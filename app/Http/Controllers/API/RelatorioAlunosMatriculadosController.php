<?php

namespace App\Http\Controllers\API;

use App\Aluno;
use App\Assinatura;
use App\Curador;
use App\Curso;
use App\CursoTipo;
use App\Exports\RelatorioAlunosMatriculadosExport;
use App\Exports\RelatorioFinanceiroExport;
use App\Faculdade;
use App\Helper\EducazMail;
use App\Helper\RelatorioAlunosMatriculadosHelper;
use App\Http\Controllers\Admin\RelatorioFinanceiroController as AdminRelatorioFinanceiroController;
use App\Pedido;
use App\PedidoItem;
use App\PedidoStatus;
use App\Produtora;
use App\Professor;
use App\UsuariosPerfil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helper\CertificadoHelper;

class RelatorioAlunosMatriculadosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('index', new PedidoItem());

            $parametros = $request->all();
            $parametros['data_registro'] = explode(' - ', $request->data_registro);

            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);

            $alunos_matriculados = new RelatorioAlunosMatriculadosHelper();
            $data = $alunos_matriculados->lista_alunos_matriculados($parametros)->paginate($request->get('length'));

            if($data && count($data) > 0) {
                $percentual_conclusao = new CertificadoHelper();

                $data->map(function($item) use ($percentual_conclusao) {
                    $percentual = round($percentual_conclusao->percentualOnline($item->fk_usuario, $item->curso_id), 2);
                    $item->percentual_conclusao = $percentual;

                    return $item;
                }); 
            }

            return response()->json($data);
        } catch (\Exception $e) {
//            $sendMail = new EducazMail(7);
//            $sendMail->emailException($e);
            Log::error($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlunosMatriculados(Request $request)
    {
        try {
            $alunos_matriculados = new RelatorioAlunosMatriculadosHelper();

            if($request->paginate) {
                $data = $alunos_matriculados->lista_alunos_matriculados($request)->paginate($request->paginate);
            } else {
                $data = $alunos_matriculados->lista_alunos_matriculados($request)->paginate(10);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível encontrar os dados solicitados'], 400);
        }
    }

    public function export(Request $request)
    {
        try {
            $this->authorize('export', new PedidoItem());

            $parametros = $request->all();
            $parametros['data_registro'] = explode(' - ', $request->data_registro);

            $parametros['ies'] = $this->filterByUniversity($parametros);
            $parametros['cursos_ids'] = $this->filterByCourse($parametros);

            $alunos_matriculados = new RelatorioAlunosMatriculadosHelper();
            $data = $alunos_matriculados->lista_alunos_matriculados($parametros)->get();


            $currentDate = (new Carbon)->format('Ymdhis');
            return Excel::download(new RelatorioAlunosMatriculadosExport($parametros), 'alunos_matriculados_'. $currentDate . '.xlsx');
//            return (new RelatorioFinanceiroExport($parametros))->download('relatorio_financeiro_'.$currentDate.'.xlsx');
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
