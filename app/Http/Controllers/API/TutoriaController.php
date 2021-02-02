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
use App\Core\Listings\EnvioTrabalhoListing;
use App\Core\Listings\TrabalhoListing;
use App\Curso;
use App\Pergunta;
use App\PerguntaResposta;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helper\CertificadoHelper;

class TutoriaController extends Controller {

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);
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

    public function trabalhos(Request $request) 
    {
        $loggedUser = JWTAuth::user();

        try {
            $trabalhos = TrabalhoListing::new()
                ->setFilters(array_merge([
                    'perfil_id' => $loggedUser->fk_perfil,
                    'faculdade_id' => $loggedUser->fk_faculdade_id,
                    'professor_id' => ViewUsuarioCompleto::where('fk_usuario_id', $loggedUser->id)->value('id')
                ], $request->all()))
                ->setColumns([
                    'trabalho_id',
                    'trabalho_titulo',
                    'curso_id',
                    'curso_titulo',
                    'curso_data_criacao',
                    'tipo_titulo'
                ])
                ->setSorts([
                    'curso_titulo' => 'asc'
                ])
                ->collect()
                ->map(function ($trabalho) use ($request) {
                    $trabalho->envios = EnvioTrabalhoListing::new()
                        ->setFilters(array_merge([
                            'trabalho_id' => $trabalho->trabalho_id
                        ], $request->all()))
                        ->setColumns([
                            'id',
                            'student',
                            'studentId',
                            'grade',
                            'downloadPath',
                            'filename',
                            'data_envio',
                        ])
                        ->setSorts([
                            'data_envio' => 'desc'
                        ])
                        ->collect()
                        ->map(function ($trabalhoEnviado) {
                            $trabalhoEnviado->nome_arquivo = !is_null($trabalhoEnviado->filename) ? $trabalhoEnviado->filename : (new CursosTrabalhosUsuarios)->getNomeArquivoAttribute($trabalhoEnviado->downloadPath);
                            $trabalhoEnviado->downloadPath = env('AWS_URL', 'https://s3.us-east-1.amazonaws.com/educaz20') . $trabalhoEnviado->downloadPath;

                            return $trabalhoEnviado;
                        });

                    return $trabalho;
                });

            return response()->json([
                'success' => true,
                'items' => $trabalhos
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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

                $trabalho = CursosTrabalhosUsuarios::find($student['id']);

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
    public function mensagens(Request $request) {

        $loggedUser = JWTAuth::user();
        try {
            $perguntas = Pergunta::getPerguntasProfessor($loggedUser->id, $request->all());
            $data = [
                'perguntas' => [],
                'cursos' => []
            ];
            $cursos = [];
            foreach ($perguntas as $pergunta) {
                $pergunta = collect($pergunta);
                array_push($cursos,  ['cursos_tipo' => $pergunta['cursos_tipo'], 'courseName' => $pergunta['courseName'], 'curso_id' => $pergunta['curso_id'], 'titulo_cursotipo' => $pergunta['titulo_cursotipo']]);
                $records = Pergunta::select()
                    ->join('cursos', 'cursos.id', 'pergunta.fk_curso')
                    ->join('professor', 'professor.id', 'cursos.fk_professor')
                    ->join('pergunta_resposta', 'pergunta_resposta.fk_pergunta', 'pergunta.id')
                    ->where('professor.fk_usuario_id', $loggedUser->id)
                    ->where('pergunta_resposta.fk_pergunta', $pergunta['id'])
                    ->where('pergunta_resposta.fk_criador_id', '!=', $loggedUser->id)
                    ->latest('pergunta_resposta.id')
                    ->pluck('status')
                    ->first();
                if (!empty($records)) {
                    $pergunta['status'] = $records;
                } else {
                    $pergunta['status'] = 1;
                }
                $pergunta->put('respostas', $records);

                array_push($data['perguntas'], $pergunta);
            }

            $data['cursos'] = array_values(collect($cursos)->unique()->toArray());
            $data['perguntas'] = collect($data['perguntas'])->mapWithKeys(function ($item, $key) {
               return [$item['curso_id'] => $item];
            })->toArray();
            return response()->json([
                'success' => true,
                'items' => $data
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' .$e->getMessage()
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

            $aluno = Aluno::where('alunos.fk_usuario_id', '=', $loggedUser->id)->first();
            $curso = Curso::find($idCurso);

            $tcc = $request->file('tcc');
            $type = $tcc->getClientOriginalExtension();

            if (!$tcc) return;


            $file_name = $aluno->nome . "-" . $curso->titulo . "." . $type;

            Storage::disk('s3')->put('/tutoria/trabalhos/'.$file_name, file_get_contents($tcc), 'public');

            $trabalho = CursosTrabalhos::where('cursos_trabalhos.fk_cursos', '=', $idCurso)->firstOrFail();
            $trabalhoAluno = CursosTrabalhosUsuarios::firstOrCreate([
                'fk_cursos_trabalhos' => $trabalho->id,
                'fk_usuario' => $loggedUser->id
            ]);

            $trabalhoAluno->downloadPath = '/tutoria/trabalhos/'.$file_name;
            $trabalhoAluno->status = 1;
            $trabalhoAluno->nota = null;
            $trabalhoAluno->fk_criador_id = $loggedUser->id;

            $trabalhoAluno->save();

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
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function retornaUsuarioTrabalhos($id) {
        try {
            $trabalhos = CursosTrabalhosUsuarios::select('cursos_trabalhos_usuario.*',
                'cursos.titulo as nome_curso',
                'cursos.imagem as imagem_curso',
                'professor.nome as nome_professor',
                'professor.sobrenome as sobrenome_professor')
                ->join('cursos_trabalhos', 'cursos_trabalhos.id', '=', 'cursos_trabalhos_usuario.fk_cursos_trabalhos')
                ->join('cursos', 'cursos.id', '=', 'cursos_trabalhos.fk_cursos')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_trabalhos_usuario.fk_usuario', '=', $id)
                ->where('cursos_trabalhos_usuario.status', '=', 1)
                ->get();

            foreach ($trabalhos as $trabalho) {
                $trabalho['corrigido'] = !empty($trabalho['nota']);
                $trabalho['downloadPath'] = env('AWS_URL', 'https://s3.us-east-1.amazonaws.com/educaz20') . $trabalho['downloadPath'];
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
}
