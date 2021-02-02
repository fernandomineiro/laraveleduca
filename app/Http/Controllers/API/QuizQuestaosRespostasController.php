<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Quiz;

use App\QuizQuestao;
use App\QuizResposta;
use App\QuizResultado;
use PHPUnit\Util\Json;
use App\Helper\CertificadoHelper;
use App\ConclusaoCursosFaculdades;
use App\Usuario;

class QuizQuestaosRespostasController extends Controller
{
    public function index($idCurso, $idQuiz, $idUsuario)
    {
        try {
            $usuario = Usuario::find($idUsuario);
            $faculdade_id = $usuario->fk_faculdade_id;

            $criterios_conclusao = ConclusaoCursosFaculdades::select('nota_quiz as percentual_acerto')
            ->where([
                ['fk_curso', $idCurso], 
                ['fk_faculdade', $faculdade_id]
            ])->get()
            ->toArray();
            if (!$criterios_conclusao)
                return response()->json([
                    'erro' => 'Não foi possível obter o questionário.'
                ]);
            $nota_corte_quiz = $criterios_conclusao;

            $quizQuestaos = QuizQuestao::select(
                'quiz_questao.id',
                'quiz_questao.fk_quiz',
                'quiz_questao.titulo',
                'quiz_questao.fk_atualizador_id',
                'quiz_questao.fk_criador_id',
                'quiz_questao.criacao',
                'quiz_questao.atualizacao'
            )
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            $quizRespostas = QuizResposta::select(
                'quiz_resposta.id',
                'quiz_resposta.label',
                'quiz_resposta.descricao',
                'quiz_resposta.fk_quiz_questao'
            )
                ->join('quiz_questao', 'quiz_resposta.fk_quiz_questao', '=', 'quiz_questao.id')
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            if (count($quizQuestaos) == 0 || count($quizRespostas) == 0)
                return response()->json([
                    'erro' => 'Não foi possível obter o questionário.'
                ]);

            else {
                //return response()->json([
                //    $quizQuestaos,
                //    $quizRespostas
                //]);
                $quizRes = [
                    'fk_quiz' => (int)$idQuiz,                  //'fk_quiz' => (int)$data['fk_quiz'],
                    'fk_usuario' => (int)$idUsuario,
                    'qtd_acertos' => 0,                         //'qtd_acertos' => (int)$data['qtd_acertos'],
                    'qtd_erros' => count($quizQuestaos),        //'qtd_erros' => (int)$data['qtd_erros'],
                    'status' => 1
                ];
                $quiz_resultado = QuizResultado::create($quizRes);

                return response()->json([
                    'percentualAcerto' => $nota_corte_quiz,
                    'questaos' => $quizQuestaos,
                    'respostas' => $quizRespostas,
                    'count_questaos' => count($quizQuestaos),
                    'quiz_resultado_id' => $quiz_resultado,
                    //'quiz_resultado_obj' => $quizRes,
                ]);
            }
        } catch (\InvalidArgumentException $e){
            DB::rollBack();

            return response()->json([
                'success' => false,
                'messages' => $e->getMessage()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getSituacao($idCurso, $idQuiz, $idUsuario){
        try {
            //situacoes possiveis -> aberto, desistencia, aprovado
            //aprovado -> enviou pelo menos um questionario contendo a quantidade de acertos suficiente
            //se não, verificar se desistiu ou se está em aberto
            //se aprovado ou desistiu, enviar gabarito e questoes
            //se aprovado, enviar também respostas do usuario
            //se em aberto, enviar apenas numero de tentativas
            $aprovado = false;
            $quiz = Quiz::find($idQuiz);
            $quiz_resultados = QuizResultado::where('fk_quiz', $idQuiz)
                ->where('fk_usuario', $idUsuario)->get();

            $usuario = Usuario::find($idUsuario);
            $faculdade_id = $usuario->fk_faculdade_id;
            $criterios_conclusao = ConclusaoCursosFaculdades::where([
                ['fk_curso', $idCurso], 
                ['fk_faculdade', $faculdade_id]
            ])->first();
            if(!$criterios_conclusao){
                $resposta['error'] = "Critérios para conclusão não encontrados";
                $resposta['success'] = false;
                return $resposta;
            }
            $nota_corte_quiz = $criterios_conclusao['nota_quiz'];

            $quizQuestaos = QuizQuestao::select(
                'quiz_questao.id',
                'quiz_questao.fk_quiz',
                'quiz_questao.titulo',
                'quiz_questao.fk_atualizador_id',
                'quiz_questao.fk_criador_id',
                'quiz_questao.criacao',
                'quiz_questao.atualizacao'
            )
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            $quizRespostas = QuizResposta::select(
                'quiz_resposta.id',
                'quiz_resposta.label',
                'quiz_resposta.descricao',
                'quiz_resposta.fk_quiz_questao'
            )
                ->join('quiz_questao', 'quiz_resposta.fk_quiz_questao', '=', 'quiz_questao.id')
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            if (count($quizQuestaos) == 0 || count($quizRespostas) == 0)
                return response()->json([
                    'erro' => 'Não foi possível obter o questionário.'
                ]);

            $quiz_respostas_corretas = QuizQuestao::select('quiz_questao.id as questao_id',
                'quiz_resposta.id as alternativa_id')
                ->join('quiz_resposta', function ($join) {
                    $join->on('quiz_questao.id', '=', 'quiz_resposta.fk_quiz_questao');
                    $join->on('quiz_questao.resposta_correta', '=', 'quiz_resposta.label');
                })
                ->where('quiz_questao.fk_quiz', $idQuiz)->get()->toArray();

            if ($quiz && $quiz_resultados) {
                foreach ($quiz_resultados as $resultado) {
                    $acertos = ($resultado->qtd_acertos / ($resultado->qtd_acertos + $resultado->qtd_erros)) * 100;
                    //if ($acertos >= $quiz->percentual_acerto) $aprovado = true;
                    if ($acertos >= $nota_corte_quiz) $aprovado = true;                    
                }
            }
            if ($aprovado) {
                $retorno['situacao'] = 'aprovado';
                $quiz_resultado = QuizResultado::where('fk_quiz', $idQuiz)
                    ->where('fk_usuario', $idUsuario)->orderBy('data_criacao', 'DESC')->first();
                if ($quiz_resultado) {
                    $retorno['acertos'] = json_decode($quiz_resultado->json_acertos);
                    $retorno['erros'] = json_decode($quiz_resultado->json_erros);
                    $retorno['respostas_corretas'] = $quiz_respostas_corretas;
                    $retorno['quizQuestaos'] = $quizQuestaos;
                    $retorno['quizRespostas'] = $quizRespostas;
                }
            } else {
                $quiz_desistencia = $quiz_resultados->pluck('solicitou_gabarito');
                if ($quiz_desistencia) {
                    $desistencia = $quiz_desistencia->contains(1);
                    if ($desistencia) {
                        $retorno['situacao'] = 'desistiu';
                        $retorno['respostas_corretas'] = $quiz_respostas_corretas;
                        $retorno['quizQuestaos'] = $quizQuestaos;
                        $retorno['quizRespostas'] = $quizRespostas;
                    } else {
                        $retorno['situacao'] = 'aberto';
                    }
                }
            }
            $retorno['tentativas'] = count($quiz_resultados);
            //$retorno['quizPercentualAcerto'] = $quiz->percentual_acerto;
            $retorno['quizPercentualAcerto'] = $nota_corte_quiz;

            return response()->json([
                $retorno,
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

    public function enviar(Request $request)
    {
        try {
            $data = $request->all();

            $idQuiz = $data['fk_quiz'];
            $quiz = Quiz::find($idQuiz);
            if(!$quiz){
                return response()->json(['success' => false]);
            }
            $quiz_questoes = QuizQuestao::select('quiz_questao.id',
                'quiz_questao.resposta_correta',
                'quiz_resposta.id as alternativa_id',
                'quiz_resposta.label')
                ->join('quiz_resposta', function($join){
                    $join->on('quiz_questao.id', '=', 'quiz_resposta.fk_quiz_questao');
                    $join->on('quiz_questao.resposta_correta', '=', 'quiz_resposta.label');
                })
                ->where('quiz_questao.fk_quiz', $idQuiz)->get()->toArray();

            //$questoes_ids_alternativas = array_column($quiz_questoes, 'resposta_correta', 'id');
            $questoes_ids_alternativas = array_column($quiz_questoes, 'alternativa_id', 'id');
            $respostas_usuario = $data['respostas'];
            $acertos = array();
            $acertos_o = array();
            $erros = array();
            $erros_o = array();
            foreach($respostas_usuario as $resposta_usuario){
                $idQuestao = $resposta_usuario['questaoId'];
                if($questoes_ids_alternativas[$idQuestao] == $resposta_usuario['alternativaId']){
                    array_push($acertos, $idQuestao);
                    array_push($acertos_o, $resposta_usuario);
                } else {
                    array_push($erros, $idQuestao);
                    array_push($erros_o, $resposta_usuario);
                }
            }

            //$percentual_acerto_quiz = $quiz->percentual_acerto;

            if(count($erros) == 0 && count($acertos) == 0)
                $percentual_acerto_usuario = 0;
            else
                $percentual_acerto_usuario = (count($acertos)/(count($acertos)+count($erros)))*100;

            //$aprovado = $percentual_acerto_usuario >= $percentual_acerto_quiz;

            $quizRes = [
                'fk_quiz' => (int)$idQuiz,                  //'fk_quiz' => (int)$data['fk_quiz'],
                'fk_usuario' => (int)$data['fk_usuario'],
                'qtd_acertos' => count($acertos),           //'qtd_acertos' => (int)$data['qtd_acertos'],
                'qtd_erros' => count($erros),               //'qtd_erros' => (int)$data['qtd_erros'],
                'json_acertos' => json_encode($acertos_o),  //'json_acertos' => json_encode($data['json_acertos']),
                'json_erros' => json_encode($erros_o),      //'json_erros' => json_encode($data['json_erros']),
                'status' => 1
            ];


            //codigo para insercao
            ///$quiz_resultado = QuizResultado::create($quizRes);
            //mudando para atualizacao
            $quiz_resultado = QuizResultado::where('fk_quiz', $idQuiz)        
            ->where('fk_usuario', (int)$data['fk_usuario'])->orderBy('data_criacao', 'DESC')->first()->update($quizRes);

            //$quiz = Quiz::find((int)$data['fk_quiz'])->first();
            $curso_id = (int)$data['id'];
            if($curso_id){
                //$curso_id = $quiz->fk_curso;
                $certificadoHelper = new CertificadoHelper();
                $retorno = $certificadoHelper->emiteCertificado((int)$data['fk_usuario'], $curso_id);
            }            

            return response()->json([
            'success' => true,
            'data' => $quizRes,
            'objeto_criaco' => $quiz_resultado,
            'retornoCertificado' =>$retorno
        ]);
        } catch (\InvalidArgumentException $e){
            DB::rollBack();
        
            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',

                'data' => $request->all()
            ]);
        }
    } 
    
    public function gabarito(Request $request){
        try {
            $data = $request->all();
            $idQuiz = $data['fk_quiz'];
            $idUsuario = $data['fk_usuario'];
            $idCurso = $data['id'];

            $quiz_resultado = QuizResultado::where('fk_quiz', $idQuiz)
                ->where('fk_usuario', $idUsuario)->orderBy('data_criacao', 'DESC')->first();

            //return response()->json([
            //    'quiz_resultado' => $quiz_resultado,
            //]);

            if ($quiz_resultado) {
                $quiz_resultado->solicitou_gabarito = 1; // true ou 1
                $quiz_resultado->save();
                
                $certificadoHelper = new CertificadoHelper();
                $retorno = $certificadoHelper->emiteCertificado($idUsuario, $idCurso);                

                return response()->json([
                    'retorno' => 'sucesso',
                    'resultado' => $quiz_resultado
                ]);
            }
        } catch (\InvalidArgumentException $e){
            return response()->json([
                'erro' => 'Não foi possível inserir o registro.'
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

    public function getRespostasCorretas($idCurso, $idQuiz, $idUsuario){
        try {
            $quizPercentualAcerto = Quiz::select(
                'quiz.percentual_acerto'
            )
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->get()
                ->toArray();

            $quizQuestaos = QuizQuestao::select(
                'quiz_questao.id',
                'quiz_questao.fk_quiz',
                'quiz_questao.titulo',
                'quiz_questao.resposta_correta',
                'quiz_questao.fk_atualizador_id',
                'quiz_questao.fk_criador_id',
                'quiz_questao.criacao',
                'quiz_questao.atualizacao'
            )
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            $quizRespostas = QuizResposta::select(
                'quiz_resposta.id',
                'quiz_resposta.label',
                'quiz_resposta.descricao',
                'quiz_resposta.fk_quiz_questao'
            )
                ->join('quiz_questao', 'quiz_resposta.fk_quiz_questao', '=', 'quiz_questao.id')
                ->join('quiz', 'quiz_questao.fk_quiz', '=', 'quiz.id')
                ->where('quiz.id', $idQuiz)
                ->where('quiz.fk_curso', $idCurso)
                ->where('quiz.status', 1)
                ->get()
                ->toArray();

            if (count($quizQuestaos) == 0 || count($quizRespostas) == 0 ||
                count($quizPercentualAcerto) == 0)
                return response()->json([
                    'erro' => 'Não foi possível obter o questionário.'
                ]);

            else {
                return response()->json([
                    'percentualAcerto' => $quizPercentualAcerto,
                    'questaos' => $quizQuestaos,
                    'respostas' => $quizRespostas,
                    'count_questaos' => count($quizQuestaos),
                    //'count_resultados' => count($quizResultados)
                ]);
            }
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getRespostasUsuario($idCurso, $idQuiz, $idUsuario){
        try {
            $quiz_resultados = QuizResultado::where('fk_quiz', $idQuiz)
                ->where('fk_usuario', $idUsuario)->get();

            return response()->json([
                'respostas' => $quiz_resultados
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
