<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Curso;
use App\CursoTipo;
use App\TrilhaCurso;
use App\Exports\RelatorioFinanceiroExport;
use App\Faculdade;
use App\Helper\EducazMail;
use App\Http\Controllers\Admin\RelatorioFinanceiroController as AdminRelatorioFinanceiroController;
use App\Pedido;
use App\Produtora;
use App\Professor;
use App\Http\Controllers\Controller;
use App\PedidoItem;
use App\PedidoStatus;
use App\UsuariosPerfil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class RelatorioFinanceiroController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->authorize('index', new PedidoItem());
            
            $parametros = (new AdminRelatorioFinanceiroController)->processaRequest($request);
            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);

            $query = (new AdminRelatorioFinanceiroController)->listaRelatorioFinanceiro($parametros);
            $pedidos = $query->paginate($request->get('length'));

            // nas linha abaixo estou buscando o nome do professor.
                if($pedidos) {
                    $model_trilha = new TrilhaCurso();
                    $model_pedido_item = new PedidoItem();
                    
                    $pedidos->map(function($item) use($model_trilha, $model_pedido_item) {
                        
                        if($item->fk_trilha != null) {
                            $trilha_dados = $model_trilha->lista($item->fk_trilha);
                            
                            if($trilha_dados) {
                                foreach ($trilha_dados as $professor) {
                                    if($professor) {
                                        if(isset($item->professor_nome)) {
                                            $item->professor_nome .= ' -- ' . $professor->nome_professor . ' ' . $professor->sobrenome_professor;    
                                        } else {
                                            $item->professor_nome = $professor->nome_professor . ' ' . $professor->sobrenome_professor;
                                        }
                                    }
                                }
                            }
                        } 
                        elseif ($item->fk_trilha == null) {
                            $professor = $model_pedido_item->where('fk_pedido', '=', $item->pedido_id)
                                ->join('cursos', 'cursos.id', 'pedidos_item.fk_curso')
                                ->join('professor', 'professor.id', 'cursos.fk_professor')
                                ->select('professor.*')
                                ->first();
                            if($professor) {
                                if(isset($item->professor_nome)) {
                                    $item->professor_nome .= ' -- ' . $professor->nome . ' ' . $professor->sobrenome;
                                } else {
                                    $item->professor_nome = $professor->nome . ' ' . $professor->sobrenome_professor;
                                }
                            } else {
                                $item->professor_nome = '--';
                            }
                        } else {
                            $item->professor_nome = '--';
                        }
                        
                        $item->professor_sobrenome = '';
                        return $item;
                    });
                }
            // fim: nas linha acima estou buscando o nome do professor.

            return response()->json($pedidos);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function export(Request $request)
    {
        try {
            $this->authorize('export', new PedidoItem());
            
            $parametros = (new AdminRelatorioFinanceiroController)->processaRequest($request);
            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);
            $currentDate = (new Carbon)->format('Ymdhis');
            
            return (new RelatorioFinanceiroExport($parametros))->download('relatorio_financeiro_'.$currentDate.'.xlsx');
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function loadFilters()
    {
        try {
            $this->authorize('index', new PedidoItem());

            $json = [];

            $json['status'] = PedidoStatus::where('status', 1)->get()->pluck('titulo', 'id');
            $json['modalidades'] = CursoTipo::where('status', 1)->get()->pluck('titulo', 'id');
            $json['modalidades']->prepend('Assinatura', 'ASSINATURA');
            $json['modalidades']->prepend('Trilha', 'TRILHA');
            $json['modalidades']->prepend('Evento', 'EVENTO');

            if (JWTAuth::user()->fk_perfil === UsuariosPerfil::PROFESSOR && JWTAuth::user()->professor) {
                $json['faculdades'] = Faculdade::query()
                    ->where('status', 1)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('cursos')
                            ->where('cursos.fk_professor', JWTAuth::user()->professor->id)
                            ->whereColumn('cursos.fk_faculdade', 'faculdades.id');
                    })
                    ->get()
                    ->pluck('razao_social', 'id');
            }

            return response()->json($json);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    private function filterByUniversity($parametros) 
    {
        if (JWTAuth::user()->fk_perfil === UsuariosPerfil::PROFESSOR) {
            $query = Faculdade::query()
                ->where('status', 1)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('cursos')
                        ->where('cursos.fk_professor', JWTAuth::user()->professor->id)
                        ->whereColumn('cursos.fk_faculdade', 'faculdades.id');
                })
                ->select('id')
                ;

            if (isset($parametros['ies']) && !is_null($parametros['ies'])) {
                $query->where('id', $parametros['ies']);
            }

            return $query->get()
                ->pluck('id');
        }

        return JWTAuth::user()->fk_faculdade_id;
    }

    private function filterByCourse($parametros) 
    {
        if (JWTAuth::user()->fk_perfil === UsuariosPerfil::PROFESSOR) {
            $query = Curso::query()
                ->where('cursos.fk_professor', JWTAuth::user()->professor->id)
                ->select('id');

            if (isset($parametros['ies']) && !is_null($parametros['ies'])) {
                $query->where('cursos.fk_faculdade', $parametros['ies']);
            }

            return $query->get()
                ->pluck('id');
        }

        return null;
    }
}
