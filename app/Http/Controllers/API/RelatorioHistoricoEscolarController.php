<?php

namespace App\Http\Controllers\API;

use App\Aluno;
use App\CursoModuloAluno;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetAlunosRequest;
use App\ModuloUsuario;
use App\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Curso;
use App\Helper\TaxasPagamento;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AlunosExport;
use App\Exports\RelatorioParceiroExport;
use App\Helper\CertificadoHelper;
use App\Http\Controllers\API\CertificadoController;
use App\CursosConcluidos;
use PDF;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\ViewUsuarios;

class RelatorioHistoricoEscolarController extends Controller
{
    protected $perfis_liberados = [2, 10, 13, 20, 21, 22]; # PERFIS Liberados

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');

        $user_autenticated = JWTAuth::parseToken()->authenticate();
        if(!in_array($user_autenticated->fk_perfil, $this->perfis_liberados)) {
            \Config::set('auth.providers.users.model', Usuario::class);
        } else {
            \Config::set('auth.providers.users.model', ViewUsuarios::class);
        }

        parent::__construct();
    }

    public function index($nome_cpf = false){
        $user = JWTAuth::user();

        if (empty($user->fk_faculdade_id) || !in_array($user->fk_perfil, $this->perfis_liberados)){
            return response()->json(['success' => false, 'message' => 'IES inválida.']);
        }

        $params = $this->processaRequest($nome_cpf, $user);

        if (empty($nome_cpf)){
            return response()->json(['success' => false, 'message' => 'Localize o histórico do aluno buscando por nome ou CPF.']);
        }

        $data['aluno'] = Aluno::dados_aluno($params)->first();

        $data = $this->getData($data);

        if (!isset($data['aluno'])){
            return response()->json(['success' => false, 'message' => 'Aluno não encontrado.']);
        }

        $data['link_pdf'] = url('/api/relatorios/historico-escolar/pdf/'. base64_encode($data['aluno']->id . '-' . $data['aluno']->fk_usuario_id));

        return response()->json($data);
    }

    public function getAlunos(Request $request)
    {
        $alunos = [];
        $req = $request->only('nome', 'type', 'curso', 'cpf');
        try {
            $user = JWTAuth::user();

            if (empty($user->fk_faculdade_id) || !in_array($user->fk_perfil, $this->perfis_liberados)){
                return response()->json(['error' => 'IES inválida.'], 401);
            }

            $params = [
                'orderby' => 'alunos.id',
                'sort' => 'DESC',
                'nome' => $request->nome,
                'curso' => $request->curso,
                'ies' => $request->ies,
            ];

            if(isset($req['cpf'])) {
                $params['cpf'] = preg_replace("/[^0-9]/", "", $req['cpf']);
//                $params['cpf'] = $req['cpf'];
                $params['cpf_mask'] = $req['cpf'];
            }


            $params['fk_faculdade'] = $user->fk_faculdade_id;
//            $params['fk_faculdade'] = 3; // remover isso após o teste e deixar a linha de cima

            if(!$params['curso']) {
                $alunos = Aluno::dados_aluno($params)
                    ->select('alunos.*', 'faculdades.razao_social as faculdade_instituicao')
                    ->get();
            } else {
                $curso_selecionado = Curso::where('cursos.titulo', 'like', '%' . $params['curso'] . '%')->get();
                if($curso_selecionado && count($curso_selecionado) > 0) {

                    foreach ($curso_selecionado as $curso) {
                        $query = Curso::where('cursos.id', '=', $curso->id)
                            ->join('cursos_modulos_alunos', 'cursos_modulos_alunos.fk_curso_id', 'cursos.id')
                            ->join('alunos', 'alunos.id', 'cursos_modulos_alunos.fk_aluno_id')
                            ->distinct('alunos.id')
                            ->join('faculdades', 'faculdades.id', 'alunos.fk_faculdade_id')
                            ->select('alunos.*', 'cursos.titulo as curso_titulo', 'faculdades.razao_social as faculdade_instituicao');

                        if (!empty($params['cpf'])) {
//                    $query->where('alunos.cpf', $data['cpf'])->where('alunos.fk_faculdade_id', $data['fk_faculdade'])
//                        ->orWhere('alunos.cpf', $data['cpf_mask'])->where('alunos.fk_faculdade_id', $data['fk_faculdade']);
                            $query->where('alunos.cpf', $params['cpf'])
                                ->orWhere('alunos.cpf', $params['cpf_mask']);
                        }
                        if (!empty($params['nome'])) {
                            $query->where(DB::raw('CONCAT(nome, " ", sobre_nome)'), 'like', '%' . $params['nome'] . '%');
                        }

                        $alunos_all = $query->get();
//                        return $alunos_all;

                        // Checa se existe histórico escolar do curso relacionado ao aluno
                        if (count($alunos_all) > 0) {
                            foreach ($alunos_all as $k => $aluno) {
                                $data['aluno'] = $aluno;
                                $check_rel = $this->getData($data);
                                $isCurso = false;
                                if (isset($check_rel['semestres']) && count($check_rel['semestres']) > 0) {
                                    foreach ($check_rel['semestres'] as $semestre) {
                                        if (count($semestre) > 0) {
                                            foreach ($semestre as $periodo) {
                                                if (count($periodo) > 0) {
                                                    foreach ($periodo as $curso_periodo) {
                                                        if ($curso_periodo['nome'] === $aluno->curso_titulo) {
                                                            $isCurso = true;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ($isCurso) {
                                        $alunos[] = $check_rel['aluno'];
                                    } else {
                                        $isCurso = false;
                                    }
                                }
                                $data['aluno'] = null;
                            }
                        }
                    }
                }
            }

            if(count($alunos) > 0) {
                $collection = collect($alunos);
                $alunos = $collection->unique('id');
            } else {
                $collection = null;
            }

            return response()->json($alunos, 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Erro ao buscar os dados solicitados'], 401);
        }
    }

    public function getRelatorio(Request $request)
    {
        $req = $request->only('nome', 'cpf', 'curso');
        //return $req;
        $user = JWTAuth::user();

        if (empty($user->fk_faculdade_id) || !in_array($user->fk_perfil, $this->perfis_liberados)){
            return response()->json(['success' => false, 'message' => 'IES inválida.']);
        }
        $params = [];
        $params['orderby'] = 'alunos.id';
        $params['sort'] = 'DESC';

        if(isset($req['nome'])) {
            $params['nome'] = $req['nome'];
        }

        if(isset($req['cpf'])) {
            $params['cpf'] = preg_replace("/[^0-9]/", "", $req['cpf']);
            $params['cpf_mask'] = $req['cpf'];
        }

        $params['fk_faculdade'] = $user->fk_faculdade_id;

        $data['aluno'] = Aluno::dados_aluno($params)
            ->select('alunos.*', 'faculdades.razao_social as faculdade_instituicao')
            ->first();

        // acrescentar a faculdade do aluno aqui
        $data = $this->getData($data);

        if (!isset($data['aluno'])){
            return response()->json(['success' => false, 'message' => 'Aluno não encontrado.']);
        }

        $data['link_pdf'] = url('/api/relatorios/historico-escolar/pdf/'. base64_encode($data['aluno']->id . '-' . $data['aluno']->fk_usuario_id));

        return response()->json($data);
    }

    private function getData($data, $type = false){
        if (!empty($data['aluno'])){
            $cursosModulos = new CertificadoController();

            $data['aluno']->data_nascimento = date('d/m/Y', strtotime($data['aluno']->data_nascimento));

            $cursos_concluidos = $this->getConcluidos($data['aluno']->fk_usuario_id);

            $cursos_online = Aluno::cursos_online($data['aluno']->fk_usuario_id);
            $cursos_presenciais = Curso::cursosPresenciaisAluno($data['aluno']->fk_usuario_id);
            $cursos_trilha = Curso::cursosTrilhaOnlineAluno($data['aluno']->fk_usuario_id);
            $cursos_remotos = Curso::cursosRemotosAluno($data['aluno']->fk_usuario_id);

            $data['cursos'] = array();
            foreach ($cursos_online as $key => $curso) {
                $data['cursos'][$curso->fk_curso]['fk_curso'] = $curso->fk_curso;
                $data['cursos'][$curso->fk_curso]['nome'] = $curso->titulo;
                $data['cursos'][$curso->fk_curso]['professor_nome'] = $curso->professor_nome;
                $data['cursos'][$curso->fk_curso]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->fk_curso]['tipo'] = 'Online';
            }

            foreach ($cursos_presenciais as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Presencial';
            }

            foreach ($cursos_trilha as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Trilha do conhecimento';
            }

            foreach ($cursos_remotos as $key => $curso) {
                $data['cursos'][$curso->id]['fk_curso'] = $curso->id;
                $data['cursos'][$curso->id]['nome'] = $curso->nome_curso;
                $data['cursos'][$curso->id]['professor_nome'] = $curso->nome_professor;
                $data['cursos'][$curso->id]['data_inicio'] = date('d/m/Y', strtotime($curso->criacao));
                $data['cursos'][$curso->id]['tipo'] = 'Remoto';
            }

            foreach ($data['cursos'] as $key => $curso) {
                if (isset($cursos_concluidos[$curso['fk_curso']]['data_conclusao'])){
                    $semestre = $this->getSemestre($cursos_concluidos[$curso['fk_curso']]['data_conclusao']);

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = date('d/m/Y', strtotime($cursos_concluidos[$curso['fk_curso']]['data_conclusao']));

                    $data['cursos'][$curso['fk_curso']]['media'] = $cursos_concluidos[$curso['fk_curso']]['media'];
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_quiz'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_quiz'] : '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = (!empty($cursos_concluidos[$curso['fk_curso']]['nota_trabalho'])) ? $cursos_concluidos[$curso['fk_curso']]['nota_trabalho'] : '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = (!empty($cursos_concluidos[$curso['fk_curso']]['carga_horaria'])) ? $cursos_concluidos[$curso['fk_curso']]['carga_horaria'] : '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = (!empty($cursos_concluidos[$curso['fk_curso']]['frequencia'])) ? $cursos_concluidos[$curso['fk_curso']]['frequencia'] . '%' : '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;

                } else {
                    $semestre = $this->getSemestre(date('Y-m-d'));

                    $data['cursos'][$curso['fk_curso']]['data_conclusao'] = '--';
                    $data['cursos'][$curso['fk_curso']]['media'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_quiz'] = '--';
                    $data['cursos'][$curso['fk_curso']]['nota_trabalho'] = '--';
                    $data['cursos'][$curso['fk_curso']]['carga_horaria'] = '--';
                    $data['cursos'][$curso['fk_curso']]['frequencia'] = '--';
                    $data['cursos'][$curso['fk_curso']]['semestre'] = $semestre;
                }
            }
        } else {
            $data = [];
        }


        if (!empty($data)){
            $data['carga_horaria_total'] = 0;
            $data['cursos_online_carga_horaria_total'] = 0;
            $data['cursos_remotos_carga_horaria_total'] = 0;
            $data['cursos_presenciais_carga_horaria_total'] = 0;
            $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = 0;

            if (isset($data['cursos'])){
                foreach ($data['cursos'] as $key => $curso) {
                    $cursos[$curso['semestre']][$this->getType($curso['tipo'])][$key] = $curso;

                    switch ($this->getType($curso['tipo'])) {
                        case 'online':
                            $data['cursos_online_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_online_carga_horaria_total'];
                            break;

                        case 'remoto':
                            $data['cursos_remotos_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_remotos_carga_horaria_total'];
                            break;

                        case 'presencial':
                            $data['cursos_presenciais_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_presenciais_carga_horaria_total'];
                            break;

                        case 'trilha_do_conhecimento':
                            $data['cursos_trilha_do_conhecimento_carga_horaria_total'] = (int) $curso['carga_horaria'] + (int) $data['cursos_trilha_do_conhecimento_carga_horaria_total'];
                            break;
                    }

                    if ($curso['carga_horaria'] > 0){
                        $data['carga_horaria_total'] = $curso['carga_horaria'] + $data['carga_horaria_total'];
                    }
                }

                if (!empty($cursos)){
                    unset($data['cursos']);
                    $data['semestres'] = $cursos;
                }
            }
        }

        return $data;
    }

    private function checkCursoConcluido($fk_usuario, $fk_curso){
        $curso_concluido = CursosConcluidos::select('id')->where(['fk_usuario' => $fk_usuario, 'fk_curso' => $fk_curso])->first();

        if (isset($curso_concluido->id)){
            return true;
        } else {
            return false;
        }
    }


    private function getConcluidos($fk_usuario){
        $cursos_concluidos = CursosConcluidos::where('fk_usuario', $fk_usuario)->get();

        $cursos = array();
        foreach ($cursos_concluidos as $key => $curso_concluido) {
            $cursos[$curso_concluido->fk_curso]['data_conclusao'] = $curso_concluido->criacao;

            $cursos[$curso_concluido->fk_curso]['media'] = '--';
            if ($curso_concluido->nota_quiz > 0 && $curso_concluido->nota_trabalho > 0 ){
                $cursos[$curso_concluido->fk_curso]['media'] = ($curso_concluido->nota_quiz + $curso_concluido->nota_trabalho) / 2;
                $cursos[$curso_concluido->fk_curso]['nota_quiz'] = $curso_concluido->nota_quiz;
                $cursos[$curso_concluido->fk_curso]['nota_trabalho'] = $curso_concluido->nota_trabalho;
            } elseif ($curso_concluido->nota_quiz > 0){
                $cursos[$curso_concluido->fk_curso]['nota_quiz'] = $curso_concluido->nota_quiz;
                $cursos[$curso_concluido->fk_curso]['media'] = $curso_concluido->nota_quiz;
            } elseif ($curso_concluido->nota_trabalho > 0){
                $cursos[$curso_concluido->fk_curso]['nota_trabalho'] = $curso_concluido->nota_trabalho;
                $cursos[$curso_concluido->fk_curso]['media'] = $curso_concluido->nota_trabalho;
            }

            if ($curso_concluido['carga_horaria'] > 0){
                $cursos[$curso_concluido->fk_curso]['carga_horaria'] = $curso_concluido['carga_horaria'];
            } else {
                $cursos[$curso_concluido->fk_curso]['carga_horaria'] = '--';
            }

            $cursos[$curso_concluido->fk_curso]['frequencia'] = $curso_concluido->frequencia;
        }

        return $cursos;
    }

    private function processaRequest($nome_cpf, $user){
        $params = [];
        $params['orderby'] = 'alunos.id';
        $params['sort'] = 'DESC';

        if(!empty($nome_cpf) && preg_replace("/[^0-9]/", "", $nome_cpf) > 0){
            $params['cpf'] = preg_replace("/[^0-9]/", "", $nome_cpf);
            $params['cpf_mask'] = $nome_cpf;
        } elseif (!empty($nome_cpf)){
            $params['nome'] = $nome_cpf;
        }

        $params['fk_faculdade'] = $user->fk_faculdade_id;

        return $params;
    }

    public function gerarPDF($hash){
        $decode =  base64_decode($hash);
        $data = explode("-", $decode);
        $aluno_id = $data[0];

        $data['aluno'] = Aluno::dados_aluno(['id' => $aluno_id])->first();

        if ($data['aluno']->fk_usuario_id == $data[1]){
            $historico = $this->getData($data, 'pdf');

            return PDF::loadView('relatorio.historico_escolar.template_pdf', $historico)
                ->setPaper('A4', 'landscape')->stream();
        }

    }

    private function getSemestre($data_conclusao){
        $data = explode("-", $data_conclusao);

        $primeiro_semestre = [1, 2, 3, 4, 5, 6];
        $segundo_semestre = [7, 8, 9, 10, 11, 12];

        if (!empty($data[1]) && !empty($data[0])){
            if (in_array($data[1], $primeiro_semestre)){
                return '01/' . $data[0];
            } else {
                return '02/' . $data[0];
            }
        } else {
            return $data[2];
        }
    }

    private function getType($str){
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '_', $str);

        return strtolower($str);
    }

    public function getInstituicao() {
        try {

            $faculdades = Faculdade::all();

            $data = $faculdades;
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível localizar as instituições'], 401);
        }
    }
}
