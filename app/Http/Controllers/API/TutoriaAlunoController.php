<?php

namespace App\Http\Controllers\API;

use App\CursosTrabalhos;
use App\CursosTrabalhosUsuarios;
use App\Helper\EducazMail;
use App\Professor;
use App\ViewUsuarioCompleto;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Aluno;
use App\Curso;
use App\Pergunta;
use App\PerguntaResposta;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helper\CertificadoHelper;
use DateTime;
use Illuminate\Support\Facades\Crypt;

class TutoriaAlunoController extends Controller {

    private $_s3Url = 'https://s3.us-east-1.amazonaws.com/educaz20prod';

    public function __construct() {
        parent::__construct();

        /**
         * TODO: Pegar o ID do professor logado
         */
        $this->professorId = 1;
    }


    /**
     * Cria uma pergunta
     *
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pergunta(Request $request)
    {
       try {

            $pergunta = new Pergunta($request->all());

            $pergunta->status = 1;

            $validator = Validator::make($pergunta->toArray(), $pergunta->rules, $pergunta->messages);

            if ($validator->fails()) {
                throw new \InvalidArgumentException();
            }

            $pergunta->save();

            return response()->json([
                'success' => true,
                'data' => Pergunta::find($pergunta->id)->toArray()
            ]);

       } catch (\InvalidArgumentException $e){

           $sendMail = new EducazMail(7);
           $sendMail->emailException($e);
           return response()->json([
            'success' => false,
            'messages' => $validator->messages(),
            'data' => $request->all()
           ]);

       }  catch (\Exception $e) {
           $sendMail = new EducazMail(7);
           $sendMail->emailException($e);
           return response()->json([
               'success' => false,
               'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
           ]);
       }

    }

    /**
     * Cria uma resposta
     *
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resposta(Request $request) {
       try {

            $loggedUser = JWTAuth::user();
            $resposta = new PerguntaResposta($request->all());

            $resposta->fk_criador_id = $loggedUser->id;
            $resposta->status = Pergunta::MENSAGEM_NAO_LIDA;

            $validator = Validator::make($resposta->toArray(), $resposta->rules, $resposta->messages);

            if ($validator->fails()) {
                throw new \InvalidArgumentException();
            }

            $resposta->save();

            return response()->json([
                'success' => true,
                'data' => PerguntaResposta::where('id', $resposta->id)->with('usuario')->first()
            ]);

       } catch(\InvalidArgumentException $invalidArgument){
           $sendMail = new EducazMail(7);
           $sendMail->emailException($invalidArgument);
            return response()->json([
                'success' => false,
                'messages' => $validator->messages(),
                'data' => $request->all()
            ]);
       } catch(\Exception $error) {
           $sendMail = new EducazMail(7);
           $sendMail->emailException($error);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'data' => $request->all()
            ]);
       }
    }

    public function trabalhos() {

        $loggedUser = JWTAuth::user();
        try {
            // o usuário logado é um usuário normal, necessário buscar o professor linkado ao usuário logado para fazer a busca
            if ($loggedUser->fk_perfil == 2) {
                $trabalhos = CursosTrabalhos::select('cursos_trabalhos.*')
                    ->join('cursos', 'cursos_trabalhos.fk_cursos', '=', 'cursos.id')
                    ->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
                    ->where('cursos_faculdades.fk_faculdade', $loggedUser->fk_faculdade_id)->get();
            } else {
                $professor = ViewUsuarioCompleto::where('fk_usuario_id', '=', $loggedUser->id)->first();
                $trabalhos = CursosTrabalhos::select('cursos_trabalhos.*')
                    ->join('cursos', 'cursos_trabalhos.fk_cursos', '=', 'cursos.id')
                    ->where('cursos.fk_professor', $professor->id)->get();
            }

            $listaTrabalhos = [];

            foreach ($trabalhos as $key => $trabalho) {
                $listaTrabalhos[$key] = [
                    'id' => $trabalho->id,
                    'name' => $trabalho->titulo
                ];

                foreach ($trabalho->grade as $keyGrade => $value) {
                    $student = $value->aluno;
                    $listaTrabalhos[$key]['trabalhos'][] = [
                        'id' => $value->id,
                        'student' => $student->nome,
                        'studentId' => $student->id,
                        'grade' => $value->nota,
                        'downloadPath' => $this->_s3Url . $value->downloadPath,
                    ];
                }

            }

            return response()->json([
                'success' => true,
                'items' => $listaTrabalhos
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trabalhosSave(Request $request) {
        try {
            $loggedUser = JWTAuth::user();
            $data = $request->all();

            if (empty($data['grades'])) {
                throw new \Exception('Grade não enviada');
            }

            foreach ($data['grades'] as $student) {

                $trabalho = CursosTrabalhosUsuarios::where('fk_usuario', $student['studentId'])
                                ->where('fk_cursos_trabalhos', $data['trabalhoId'])->first();

                if (empty($trabalho)) {
                    continue;
                }

                $trabalho->fk_atualizador_id = $loggedUser->id;
                $trabalho->nota = $student['grade'];
                $trabalho->save();

                //Ao atribuir nota para o trabalho, verificar possibilidade de emissão de certificado
                $curso = Curso::select('cursos.id')->join('cursos_trabalhos', 'fk_cursos', '=', 'cursos.id')->where('cursos_trabalhos.id', $data['trabalhoId'])->first();
                if($curso){
                    $certificadoHelper = new CertificadoHelper();
                    $certificadoHelper->emiteCertificado($student['studentId'], $curso->id);
                }
            }

            return response()->json(['success' => true]);

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
     * Retorna as mensagens
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mensagens() {

        $loggedUser = JWTAuth::user();
        try {
            $perguntas = Pergunta::getPerguntasProfessor($loggedUser->id);

            return response()->json([
                'success' => true,
                'items' => $perguntas
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

    /**
     * Retorna o chat
     *
     * @param $id - id da pergunta
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function chat($idPergunta) {
        $loggedUser = JWTAuth::user();

        try {
            $chat = Pergunta::getChat($idPergunta, $loggedUser->id);

            return response()->json([
                'success' => true,
                'items' => $chat
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

    /**
     * Retorna o chat
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function naoLidas($id)  {
        $loggedUser = JWTAuth::user();
        try {
            $records = Pergunta::select()
                ->join('cursos', 'cursos.id', 'pergunta.fk_curso')
                ->join('professor', 'professor.id', 'cursos.fk_professor')
                ->join('pergunta_resposta', 'pergunta_resposta.fk_pergunta', 'pergunta.id')
                ->where('professor.fk_usuario_id', $id)
                ->where('pergunta_resposta.status', Pergunta::MENSAGEM_NAO_LIDA)
                ->where('pergunta_resposta.fk_criador_id', '!=', $id)
                ->get();

            return response()->json([
                'data' => count($records)
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

    public function uploadTcc(Request $request, $idCurso) {
        try {
            $loggedUser = JWTAuth::user();

            if (!$request->file('tcc')) {
                return;
            } 

            $path = Storage::disk('s3')->put('/tutoria/trabalhos', file_get_contents($request->file('tcc')), 'public');

            CursosTrabalhosUsuarios::create([
                'fk_cursos_trabalhos' => CursosTrabalhos::where('cursos_trabalhos.fk_cursos', '=', $idCurso)->value('id'),
                'fk_usuario' => $loggedUser->id,
                'downloadPath' => $path,
                'filename' => $request->file('tcc')->getClientOriginalName(),
                'status' => 1,
                'nota' => null,
                'fk_criador_id' => $loggedUser->id
            ]);

            //Se trabalho for enviado com sucesso, verificar possibilidade de emissão de certificado
            $certificadoHelper = new CertificadoHelper();
            $certificadoHelper->emiteCertificado($loggedUser->id, $idCurso);

            return response()->json([
                'success' => true,
                'messages' => ['Trabalho enviado com sucesso!']
            ]);

        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function retornaUsuarioTrabalhos($id) {
        try {
            $trabalhos = CursosTrabalhosUsuarios::select('cursos_trabalhos_usuario.*',
                'cursos.titulo as nome_curso',
                'cursos.imagem as imagem_curso',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor'
                )
                ->join('cursos_trabalhos', 'cursos_trabalhos.id', '=', 'cursos_trabalhos_usuario.fk_cursos_trabalhos')
                ->join('cursos', 'cursos.id', '=', 'cursos_trabalhos.fk_cursos')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_trabalhos_usuario.fk_usuario', '=', $id)
                ->where('cursos_trabalhos_usuario.status', '=', 1)
                ->get();

            foreach ($trabalhos as $trabalho) {
                $trabalho['corrigido'] = !empty($trabalho['nota']);
                $trabalho['downloadPath'] = $this->_s3Url . $trabalho['downloadPath'];
            }

            return response()->json([
                'success' => true,
                'items' => $trabalhos
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

    public function retornaUsuarioTrabalhosEnviados($id, $idCurso) {
        try {
            $trabalhos = CursosTrabalhosUsuarios::select('cursos_trabalhos_usuario.*',
                'cursos.titulo as nome_curso',
                'cursos.imagem as imagem_curso',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor'
                )
                ->join('cursos_trabalhos', 'cursos_trabalhos.id', '=', 'cursos_trabalhos_usuario.fk_cursos_trabalhos')
                ->join('cursos', 'cursos.id', '=', 'cursos_trabalhos.fk_cursos')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_trabalhos_usuario.fk_usuario', '=', $id)
                ->where('cursos_trabalhos.fk_cursos', '=', $idCurso)
                ->where('cursos_trabalhos_usuario.status', '=', 1)
                ->get();

            foreach ($trabalhos as $trabalho) {
                $trabalho['corrigido'] = !empty($trabalho['nota']);
                $trabalho['downloadPath'] = $this->_s3Url . $trabalho['downloadPath'];
            }

            return response()->json([
                'success' => true,
                'items' => $trabalhos
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

    /**
     * Dúvidas por Curso em Andamento
     *
     * @param $idCurso
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function duvidas($idCurso) {

        $loggedUser = JWTAuth::user();
        try {
            $chat = Pergunta::getChatCurso($idCurso, $loggedUser->id);

            return response()->json([
                'items' => $chat,
                'count' => count($chat),
                'success' => true
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param $id
     * @param $idCurso
     * @return \Illuminate\Http\JsonResponse
     */
    public function duvidasAlunoKroton($idCurso, $id) {
        try {
            $chat = Pergunta::getChatCurso($idCurso, $id);

            return response()->json([
                'items' => $chat,
                'count' => count($chat),
                'success' => true
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage()
            ]);
        }
    }
}
