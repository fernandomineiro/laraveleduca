<?php
namespace App\Http\Controllers\EducazEscolas;

use App\Http\Controllers\Controller;
use App\NotaAtividade;
use App\NotaDisciplina;
use App\NotaMateria;
use App\QuizQuestao;
use App\QuizResposta;
use App\QuizRespostaAluno;
use App\QuizResultado;
use App\ViewUsuarios;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CorrecaoController extends Controller
{
    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notaAtividade(Request $request) {
        try {
            $dadosForm = $request->except('_token');
            if ($dadosForm['id']) {
                $notaatividade = NotaAtividade::find($dadosForm['id']);
                if ($notaatividade) {
                    $notaatividade->nota = $dadosForm['nota'];
                    if ($notaatividade->save()) {
                        return response()->json([
                            'success' => true,
                            'messages' => 'Nota atualizada com sucesso!'
                        ]);
                    }
                    return response()->json([
                        'success' => false,
                        'messages' => 'Erro ao atualizar nota!'
                    ]);
                }
            }
            $notaatividade = NotaAtividade::create([
                'fk_modulo' => $dadosForm['fk_modulo'],
                'tipo_nota' => null,
                'nota'  => $dadosForm['nota'],
                'fk_usuario' => $dadosForm['fk_usuario']
            ]);
            
            if ($notaatividade) {
                return response()->json([
                    'success' => true,
                    'messages' => 'Nota salva com sucesso!'
                ]);
            }
            return response()->json([
                'success' => false,
                'messages' => 'Erro ao salvar nota!'
            ]);
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            //$sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notaDisciplina(Request $request) {
        try {
            $dadosForm = $request->except('_token');
            if ($dadosForm['id']) {
                $notaatividade = NotaDisciplina::find($dadosForm['id']);
                if ($notaatividade) {
                    $notaatividade->nota = $dadosForm['nota'];
                    if ($notaatividade->save()) {
                        return response()->json([
                            'success' => true,
                            'messages' => 'Nota atualizada com sucesso!'
                        ]);
                    }
                    return response()->json([
                        'success' => false,
                        'messages' => 'Erro ao atualizar nota!'
                    ]);
                }
            }
            $notaatividade = NotaDisciplina::create([
                'fk_disciplina' => $dadosForm['fk_disciplina'],
                'tipo_nota' => $dadosForm['tipo_nota'],
                'nota'  => $dadosForm['nota'],
                'fk_usuario' => $dadosForm['fk_usuario']
            ]);
            
            if ($notaatividade) {
                return response()->json([
                    'success' => true,
                    'messages' => 'Nota salva com sucesso!'
                ]);
            }
            return response()->json([
                'success' => false,
                'messages' => 'Erro ao salvar nota!'
            ]);
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            //$sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notaQuestao(Request $request) {
        try {
            $dadosForm = $request->except('_token');
            $notaquestao = QuizRespostaAluno::find($dadosForm['id']);
            if ($notaquestao) {
                $notaquestao->resposta_professor = $dadosForm['nota'];
                if ($notaquestao->save()) {
                    return response()->json([
                        'success' => true,
                        'messages' => 'Nota atualizada com sucesso!'
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'messages' => 'Erro ao atualizar nota!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'messages' => 'Erro ao atualizar nota! Questão inexistente'
                ]);
            }
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            //$sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'messages' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notaMateria(Request $request) {
        try {
            $dadosForm = $request->except('_token');
            if ($dadosForm['id']) {
                $notaatividade = NotaMateria::find($dadosForm['id']);
                if ($notaatividade) {
                    $notaatividade->nota = $dadosForm['nota'];
                    if ($notaatividade->save()) {
                        return response()->json([
                            'success' => true,
                            'messages' => 'Nota atualizada com sucesso!'
                        ]);
                    }
                    return response()->json([
                        'success' => false,
                        'messages' => 'Erro ao atualizar nota!'
                    ]);
                }
            }
            $notaatividade = NotaMateria::create([
                'fk_materia' => $dadosForm['fk_materia'],
                'tipo_nota' => $dadosForm['tipo_nota'],
                'nota'  => $dadosForm['nota'],
                'fk_usuario' => $dadosForm['fk_usuario']
            ]);
            
            if ($notaatividade) {
                return response()->json([
                    'success' => true,
                    'messages' => 'Nota salva com sucesso!'
                ]);
            }
            return response()->json([
                'success' => false,
                'messages' => 'Erro ao salvar nota!'
            ]);
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            //$sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'messages' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function salvarResultadoQuiz(Request $request) {
        try {

            $resp = [];
            foreach ($request->all() as $data) {
                array_push($resp, QuizRespostaAluno::create($data));

                $erros = [];
                $acertos = [];
                $questao = QuizQuestao::find($data['fk_questao']);

                if ($questao->dissertativa === 0) {
                        $resposta = QuizResposta::find($data['resposta_aluno']);
                        if (!empty($resposta) && $resposta->label == $questao->resposta_correta) {
                                array_push($acertos, [ 'questaoId' =>  $questao->id, 'alternativaId' => $resposta->label]);
                            } else {
                                array_push($erros, [ 'questaoId' =>  $questao->id, 'alternativaId' => $data['resposta_aluno']]);
                            }

                    $quizRes = [
                            'fk_quiz' => (int) $data['fk_quiz'],         //'fk_quiz' => (int)$data['fk_quiz'],
                            'fk_usuario' => (int)$data['fk_usuario'],
                            'qtd_acertos' => count($acertos),           //'qtd_acertos' => (int)$data['qtd_acertos'],
                            'qtd_erros' => count($erros),               //'qtd_erros' => (int)$data['qtd_erros'],
                            'json_acertos' => json_encode($acertos),  //'json_acertos' => json_encode($data['json_acertos']),
                            'json_erros' => json_encode($erros),      //'json_erros' => json_encode($data['json_erros']),
                            'status' => 1
                            ];


                    QuizResultado::create($quizRes);
                }
            }

            return response()->json($resp);

        } catch (\Exception $e) {
            return response()->json([
                    'success' => false,
                    'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                    'messages' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
            ]);
         }
    }
}
