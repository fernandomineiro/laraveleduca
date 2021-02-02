<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Curso;
use App\CursoTipo;
use App\Exports\RelatorioFinanceiroDetalhadoExport;
use App\Faculdade;
use App\Helper\EducazMail;
use App\Http\Controllers\Admin\RelatorioFinanceiroDetalhadoController as AdminRelatorioFinanceiroDetalhadoController;
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
use Tymon\JWTAuth\Facades\JWTAuth;

class RelatorioFinanceiroDetalhadoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->authorize('index', new PedidoItem());
            
            $parametros = (new AdminRelatorioFinanceiroDetalhadoController)->processaRequest($request);
            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);
            $query = (new AdminRelatorioFinanceiroDetalhadoController)->listaRelatorioFinanceiro($parametros);

            return response()->json($query->paginate($request->get('length')));
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
            
            $parametros = (new AdminRelatorioFinanceiroDetalhadoController)->processaRequest($request);
            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);
            $currentDate = (new Carbon)->format('Ymdhis');
            
            return (new RelatorioFinanceiroDetalhadoExport($parametros))->download('relatorio_financeiro_detalhado_'.$currentDate.'.xlsx');
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
