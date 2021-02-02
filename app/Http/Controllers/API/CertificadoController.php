<?php

namespace App\Http\Controllers\API;

use App\Curso;
use App\Helper\EducazMail;
use App\Helper\CursosConcluidosHelper;
use App\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Certificado;
use App\CertificadoLayout;
use App\Helper\CertificadoHelper;
use App\QuizResultado;
use App\CursosTrabalhosUsuarios;
use App\ConclusaoCursosFaculdades;
use App\Quiz;
use App\CursoModulo;
use App\CursoTurmaAgenda;

class CertificadoController extends Controller {
    
    /**
     * @param $idFaculdade
     * @return JsonResponse
     */
    public function index($idFaculdade) {
        try {
            $items = CertificadoLayout::select(
                'certificado_layout.id',
                'certificado_layout.tipo',
                'certificado_layout.titulo',
                'certificado_layout.layout',
                'certificado_layout.fk_curso_id'
            )
                ->where('fk_faculdade', $idFaculdade)
                ->get()
                ->toArray();
            return response()->json(['items' => $items, 'count' => count($items)]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'message' => $error->getMessage()
            ]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function retornaCertificadosFaculdades(Request $request)
    {
        try {
            $items = CertificadoLayout::select(
                'certificado_layout.id',
                'certificado_layout.tipo',
                'certificado_layout.titulo',
                'certificado_layout.layout',
                'certificado_layout.fk_curso_id'
            )
                ->whereIn('fk_faculdade', $request['faculdades'])
                ->get()
                ->toArray();
            return response()->json(['items' => $items, 'count' => count($items)]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id = null)
    {
        try {
            $data = Curador::select(
                'certificado_layout.tipo',
                'certificado_layout.titulo',
                'certificado_layout.layout',
                'certificado_layout.fk_curso_id'
            )->where('id', $id)->get()->toArray();

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getCertificadosEmitidosAluno($idUsuario){
        try {
            $queryCursos = Certificado::select('certificados.id',
                'certificados.downloadPath',
                'certificados.fk_curso',
                'cursos.titulo as nome_curso',
                'cursos.imagem as imagem_curso',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor'
            )
                ->join('cursos', 'cursos.id', '=', 'certificados.fk_curso')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('certificados.fk_usuario', '=', $idUsuario);

            $queryEstrutura = Certificado::select('certificados.id',
                'certificados.downloadPath',
                'certificados.fk_curso',
                'estrutura_curricular.titulo as nome_curso',
                DB::raw('null as imagem_curso'),
                DB::raw('null as nome_professor'),
                DB::raw('null as sobrenome_professor')
            )
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'certificados.fk_estrutura')
                ->where('certificados.fk_usuario', '=', $idUsuario);

            $items = $queryCursos->union($queryEstrutura)
                ->get()
                ->toArray();

            return response()->json([
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function autentica($codigo){
        try {
            $id_decoded = explode('-', base64_decode($codigo));
            if (count($id_decoded) == 2 && $id_decoded[0] == 'educaz') {
                $certificado = Certificado::find($id_decoded[1]);
                if ($certificado) {
                    return view('certificados.autentica', ['certificado_path' => $certificado->downloadPath]);
                } else {
                    return view('certificados.autentica', ['certificado_path' => null]);
                }
            } else {
                return view('certificados.autentica', ['certificado_path' => null]);
            }
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getConclusaoQuestionario($idUsuario, $idCurso){
        $helper = new CertificadoHelper();
        $result = $helper->getConclusaoQuestionario($idUsuario, $idCurso);
        return response()->json([
            'aprovado_questionario' => $result,
        ]);
    }

    public function getConclusaoTrabalho($idUsuario, $idCurso){
        $helper = new CertificadoHelper();
        $result = $helper->getConclusaoTrabalho($idUsuario, $idCurso);
        return response()->json([
            'aprovado_trabalho' => $result,
        ]);
    }

    public function getProgressoConclusao($idUsuario, $idCurso)
    {
        if (!$idUsuario) {
            return [];
        }

        $usuario = Usuario::find($idUsuario);
        $faculdade_id = $usuario->fk_faculdade_id;
        $criterios_conclusao = ConclusaoCursosFaculdades::where([
            ['fk_curso', $idCurso],
            ['fk_faculdade', $faculdade_id]
        ])->first();

        $curso = Curso::find($idCurso);
        $helper = new CertificadoHelper();
        $aprovacao_questionario = $helper->getConclusaoQuestionario($idUsuario, $idCurso);
        $aprovacao_trabalho = $helper->getConclusaoTrabalho($idUsuario, $idCurso);

        $resposta_questionario = QuizResultado::select(DB::raw('((qtd_acertos)/qtd_acertos+qtd_erros)*100 AS nota_quiz'))
            ->join('quiz', 'quiz.id', '=', 'quiz_resultado.fk_quiz')
            ->where('quiz_resultado.fk_usuario', $idUsuario)
            ->where('quiz.fk_curso', $idCurso)
            ->orderBy('nota_quiz', 'DESC')
            ->first();
        $nota_questionario = isset($resposta_questionario) ? $resposta_questionario->nota_quiz : null;

        $resposta_trabalho = CursosTrabalhosUsuarios::select('nota')
            ->join('cursos_trabalhos', 'cursos_trabalhos.id', '=', 'cursos_trabalhos_usuario.fk_cursos_trabalhos')
            ->where('fk_usuario', $idUsuario)
            ->where('fk_cursos', $idCurso)
            ->orderBy('nota', 'DESC')
            ->first();
        $nota_trabalho = isset($resposta_trabalho) ? $resposta_trabalho->nota : null;

        $total_elementos_conclusao = 0;
        $elementos_conclusao = 0;

        if ($criterios_conclusao['nota_trabalho'] != null) {
            $total_elementos_conclusao += 1;
            if ($aprovacao_trabalho) {
                $elementos_conclusao += 1;
            }
        }
        $possui_quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $idCurso)->first();
        if ($possui_quiz) {
            $total_elementos_conclusao += 1;
            if ($aprovacao_questionario) {
                $elementos_conclusao += 1;
            }
        }

        if ($curso->fk_cursos_tipo == 1 || $curso->fk_cursos_tipo == 4) {
            $percentualOnline = $helper->percentualOnline($idUsuario, $idCurso);
            $aulas_online = CursoModulo::select(DB::raw("COUNT(1) as total_modulos"))
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->where('cursos_secao.fk_curso', $idCurso)
                ->where('cursos_secao.status', 1)
                ->where('cursos_modulos.status', 1)
                ->get()
                ->first();
            $numero_aulas_online = $aulas_online['total_modulos'];

            $total_elementos_conclusao += (int)$numero_aulas_online;
            $elementos_conclusao += (int)($numero_aulas_online * (float)$percentualOnline) / 100;
        }
        if ($curso->fk_cursos_tipo == 2 || $curso->fk_cursos_tipo == 4) {
            $percentualPresencial = $helper->percentualPresencial($idUsuario, $idCurso);
            $aulas_presencial = CursoTurmaAgenda::where('cursos_turmas_agenda.fk_curso', '=',
                $idCurso)->get()->toArray();
            $numero_aulas_presencial = count($aulas_presencial);

            $total_elementos_conclusao += (int)$numero_aulas_presencial;
            $elementos_conclusao += (int)($numero_aulas_presencial * (float)$percentualPresencial) / 100;
        }

        $percentual_conclusao = $elementos_conclusao / $total_elementos_conclusao;

        $datas_agenda_finalizadas = false;
        //verifica se todas as agendas já passaram
        if ($curso->fk_cursos_tipo == 2 || $curso->fk_cursos_tipo == 4) {
            $agendas = Curso::agendaPorCurso($idCurso);
            $count_agendas_p = 0;
            foreach ($agendas as $agenda) {
                $start = strtotime($agenda->data_inicio . ' ' . $agenda->hora_inicio);
                $end = strtotime('now');
                if ($start < $end) {
                    $count_agendas_p += 1;
                }
            }
            $datas_agenda_finalizadas = (count($agendas) == $count_agendas_p) ? true : false;
        }

        if ($percentual_conclusao == 1){
            $duracao_em_horas = 0;
            if (!empty($curso->duracao_total)){
                $duracao_total = $curso->duracao_total;
                $parts = explode(':', $duracao_total);
                $duracao_em_horas = (intval($parts[1]) > 0 || intval($parts[2]) > 0)? intval($parts[0]+1) : intval($parts[0]);
            }

            CursosConcluidosHelper::create(['fk_faculdade' => $usuario->fk_faculdade_id, 'fk_usuario' => $idUsuario, 
            'fk_curso' => $idCurso, 'nota_trabalho' => $nota_trabalho, 'carga_horaria' => $duracao_em_horas,
             'criacao' => date('Y-m-d H:i:s'), 'frequencia' => (!empty($percentualPresencial)) ? $percentualPresencial : 0]);

        } else {
            $status_conclusao = CursosConcluidosHelper::getStatusDeConclusao($usuario->fk_faculdade_id, $idUsuario, $idCurso);

            if ($status_conclusao == 1){
                $percentual_conclusao = 1;
            }
        }

        return response()->json([
            'datas_agenda_finalizadas' => $datas_agenda_finalizadas,
            'aprovacao_questionario' => $aprovacao_questionario,
            'aprovacao_trabalho' => $aprovacao_trabalho,
            'nota_questionario' => (int)$nota_questionario,
            'nota_trabalho' => $nota_trabalho,
            'percentual_conclusao' => $percentual_conclusao,
            'percentualPresencial' => isset($percentualPresencial) ? $percentualPresencial : 0,
            'percentualOnline' => isset($percentualOnline) ? $percentualOnline : 0,
            'numero_aulas_presencial' => isset($numero_aulas_presencial) ? $numero_aulas_presencial : 0,
            'numero_aulas_online' => isset($numero_aulas_online) ? $numero_aulas_online : 0,
            'elementos_conclusao' => $elementos_conclusao,
            'total_elementos_conclusao' => $total_elementos_conclusao
        ]);
    }

    public function emiteCertificado($idUsuario, $idCurso){
        try {
            $helper = new CertificadoHelper();
            $result = $helper->emiteCertificado($idUsuario, $idCurso);
            
            if ($result['success']) {
                return response()->json(['success' => true, 'result' => $result]);
            }

            return response()->json(['success' => false, 'error' => $result['error'], 'result' => $result]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'message' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
        }
    }

    public function retornaCertificadosUsuario($idUsuario) {
        try {
            $items = CertificadoLayout::select(
                'certificado_layout.*',
                'cursos.titulo as nome_curso',
                'cursos.imagem as imagem_curso',
                'cursos_turmas_inscricao.percentual_completo',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor'
            )
                ->join('cursos', 'cursos.fk_certificado', '=', 'certificado_layout.id')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('cursos_turmas', 'cursos_turmas.fk_curso', '=', 'cursos.id')
                ->join('cursos_turmas_inscricao', 'cursos_turmas_inscricao.fk_turma', '=', 'cursos_turmas.id')
                ->where('cursos_turmas_inscricao.fk_usuario', '=', $idUsuario)
                ->get()
                ->toArray();

            foreach ($items as $item) {
                $item['arquivo'] = $item['layout'];
                if ($item['percentual_completo'] === 100) $item['certificado'] = true;
                else $item['certificado'] = false;
            }

            return response()->json(['items' => $items, 'count' => count($items)]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function enviaCertificadoPorEmail(Request $request) {
        try {
            $dados = $request->all();
            $helper = new CertificadoHelper();
            $result = $helper->enviaCertificadoPorEmail($dados['idCertificado']);
            return response()->json([
                'success' => true,
                'mensagem' => 'Certificado enviado para o email cadastrado!',
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
