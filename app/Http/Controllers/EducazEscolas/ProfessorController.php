<?php

namespace App\Http\Controllers\EducazEscolas;

use App\Aluno;
use App\Curso;
use App\CursoCategoria;
use App\CursoModulo;
use App\CursoSecao;
use App\Pergunta;
use App\Professor;
use App\ProfessorEscola;
use App\Quiz;
use App\QuizQuestao;
use App\QuizResposta;
use App\QuizRespostaAluno;
use App\Usuario;
use App\UsuariosPerfil;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfessorController extends Controller
{
    //
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retorna os professores com os quais o aluno tem aula
     * @param $idUsuario
     * @param $idEscola
     * @param $idTurma
     * @return JsonResponse
     */
    public function index($idUsuario, $idEscola, $idTurma) {
        try {
            $professores = Professor::select(
                'professor.id',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as professor_nome"),
                'professor.mini_curriculum as professor_curriculo',
                'usuarios.foto as professor_foto',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as professor_disciplina'
            // adicionar aqui nome da escola
            )
                ->join('cursos', 'cursos.fk_professor', '=', 'professor.id')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular.fk_escola', '=', $idEscola],
                        ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ]
                )
                ->orderBy('cursos_categoria.titulo', 'ASC');

            $professores = $professores->get()->unique();
            $data = [];
            foreach ($professores as $professor) {
                $materias = $materias = Curso::where('cursos.fk_professor', $professor->id)
                    ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                    ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                    ->where('cursos_categoria.disciplina', '=', 1)
                    ->pluck('cursos.titulo')
                    ->toArray();
                $professor['professor_materias'] = $materias;
                array_push($data, $professor);
            }
            
            return response()->json([
                'items' => $data,
                'count' => count($data)
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
     * Retorna o professor pela matéria que ele leciona
     * @param $idMateria
     * @param $idEscola
     * @param $idTurma
     * @return JsonResponse
     */
    public function professorPorMateria($idMateria, $idEscola, $idTurma){
        try {
            $professores = Professor::select(
                'professor.id',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as professor_nome"),
                'professor.mini_curriculum as professor_curriculo',
                'usuarios.foto as professor_foto',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as professor_disciplina'
            // adicionar aqui nome da escola
            )
                ->join('cursos', 'cursos.fk_professor', '=', 'professor.id')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
                ->where('professor.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular.fk_escola', '=', $idEscola],
                        ['estrutura_curricular_conteudo.fk_conteudo', '=', $idMateria],
                    ]
                )
                ->orderBy('cursos_categoria.titulo', 'ASC');
            
            $professores = $professores->get();
            $data = [];

            if ($professores) {
                foreach ($professores as $professor) {
                    $materias = Curso::where('cursos.fk_professor', $professor->id)
                        ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                        ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                        ->where('cursos_categoria.disciplina', '=', 1)
                        ->pluck('cursos.titulo')
                        ->toArray();
                    $professor['professor_materias'] = $materias;
                    array_push($data, $professor);
                }
            }

            return response()->json([
                'items' => $data
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
     * Retorna o professor pela disciplina com matérias que ele leciona
     * @param $idDisciplina
     * @param $idEscola
     * @param $idTurma
     * @return JsonResponse
     */
    public function professorPorDisciplina($idDisciplina, $idEscola, $idTurma){
        try {
            $professores = Professor::select(
                'professor.id',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as professor_nome"),
                'professor.mini_curriculum as professor_curriculo',
                'usuarios.foto as professor_foto',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as professor_disciplina'
            // adicionar aqui nome da escola
            )
                ->join('cursos', 'cursos.fk_professor', '=', 'professor.id')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('usuarios', 'usuarios.id', '=', 'professor.fk_usuario_id')
                ->where('professor.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular.fk_escola', '=', $idEscola],
                        ['estrutura_curricular_conteudo.fk_categoria', '=', $idDisciplina],
                    ]
                )
                ->orderBy('cursos_categoria.titulo', 'ASC');

            $professores = $professores->get()->unique();
            $data = [];
            
            if ($professores) {
                foreach ($professores as $professor) {
                    $materias = Curso::where('cursos.fk_professor', $professor->id)
                            ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                            ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                            ->where('cursos_categoria.disciplina', '=', 1)
                            ->pluck('cursos.titulo')
                        ->toArray();
                    $professor['professor_materias'] = $materias;
                    array_push($data, $professor);
                }
            }
            
            return response()->json([
                'items' => $data,
                'count' => count($data)
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
     * Retorna as mensagens
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mensagens(Request $request) {

        try {
            $perguntas = $this->getPerguntas($request->get('professor'), $request->all());
            $data = [];
            foreach ($perguntas as $pergunta) {
                $pergunta = collect($pergunta);
                $records = Pergunta::select('pergunta_resposta.*', 
                    'pergunta_resposta.status', 
                    'alunos.id as aluno_id',
                    \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as aluno_nome"))
                    ->join('cursos', 'cursos.id', 'pergunta.fk_curso')
                    ->join('professor', 'professor.id', 'cursos.fk_professor')
                    ->join('pergunta_resposta', 'pergunta_resposta.fk_pergunta', 'pergunta.id')
                    ->join('alunos', 'alunos.fk_usuario_id', '=', 'pergunta_resposta.fk_criador_id')
                    ->where('professor.fk_usuario_id', $request->get('professor'))
                    ->where('pergunta_resposta.fk_pergunta', $pergunta['id'])
                    ->where('pergunta_resposta.fk_criador_id', '!=', $request->get('professor'))
                    ->latest('pergunta_resposta.id')
                    ->first();
                if (!empty($records)) {
                    $pergunta['status'] = $records->status;
                } else {
                    $pergunta['status'] = 1;
                }
                $pergunta->put('ultima_resposta', $records);

                array_push($data, $pergunta);
            }

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
     * @param $id
     * @param $dados
     * @return mixed
     */
    private function getPerguntas($id, $dados) {
        $oUser = Professor::where('fk_usuario_id', $id)->first();
        
        $mensagens = Pergunta::select('pergunta.*' )
            ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'pergunta.fk_curso')
            ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
            ->join('cursos', 'cursos.id', 'pergunta.fk_curso')
            ->where('cursos.fk_professor', $oUser->id)
            ->where('cursos_categoria.disciplina', '=', 1);
        
        if (isset($dados['turma'])) {
            $mensagens ->where('estrutura_curricular.slug', '=', $dados['turma']);
        }
        
        if (isset($dados['escola'])) {
            $mensagens->where('escolas.slug', '=', $dados['escola']);
        }
        
        if (isset($dados['disciplina'])) {
            $mensagens->where('cursos_categoria.slug_categoria', '=', $dados['disciplina']);
        }
        
        if (isset($dados['materia'])) {
            $mensagens->where('cursos.slug_curso', '=', $dados['materia']);
        }
        return $mensagens->get();
    }

    /**
     * @param $idPergunta
     * @param $idUsuario
     * @return JsonResponse
     */
    public function chat($idPergunta, $idUsuario) {

        try {
            $chat = Pergunta::getChat($idPergunta, $idUsuario);

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
     * Retorna as aulas do usuário para o calendário
     * @param $idUsuario
     * @param $idTurma
     * @return JsonResponse
     */
    public function getCalendario($idUsuario) {
        try {
            $oUser = Professor::where('fk_usuario_id', $idUsuario)->first();
            $aulas = CursoSecao::select(
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo',
                'cursos_secao.ordem as secao_ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa'
            )
                ->join('cursos', 'cursos.id', '=', 'cursos_secao.fk_curso')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_secao.fk_curso')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('cursos.fk_professor', '=', $oUser->id)
                ->where('cursos_categoria.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('cursos_secao.data_disponibilidade', '!=', '')
                ->get();

            $data = collect($aulas)->map(function ($aula) {
                if (!empty($aula['data_disponibilidade'])) {
                    $dados['id'] = $aula['secao_id'];
                    $dados['Subject'] = $aula['secao_titulo'];
                    $dados['StartTime'] = date('D M d Y H:i:s T', strtotime($aula['data_disponibilidade']));
                    $dados['EndTime'] = date('D M d Y H:i:s T', strtotime($aula['data_disponibilidade']));
                    return $dados;
                }
            });

            return response()->json([
                'items' => $data->toArray(),
                'count' => count($data)
            ]);
        }  catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            // $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $turma
     * @param $disciplina
     * @param $escola
     * @param $idUsuario
     * @return JsonResponse
     */
    public function atividadesCorrecaoDisciplina($turma, $disciplina, $materia, $idUsuario) {
        try {
            $alunosTurma = Aluno::select('alunos.id as aluno_id',
                \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome_aluno"),
                'alunos.fk_usuario_id', 
                'estrutura_curricular.titulo as turma',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.slug_categoria',
                'cursos.id as id_materia',
                'cursos.titulo as nome_materia',
                'cursos.slug_curso as slug_materia',
                'nota_disciplina.id as id_disciplinanota',
                'nota_disciplina.nota as notaDisciplina',
                'nota_disciplina.tipo_nota as criterioNotaDisciplina',
                'nota_materia.id as id_materianota',
                'nota_materia.nota as notaMateria',
                'nota_materia.tipo_nota as criterioNotaMateria'
            )
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_usuario', '=', 'alunos.fk_usuario_id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_usuario.fk_estrutura')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
                ->leftJoin('nota_disciplina', function ($join) {
                    $join->on('nota_disciplina.fk_disciplina', '=', 'cursos_categoria.id');
                    $join->on('nota_disciplina.fk_usuario', '=', 'alunos.fk_usuario_id');
                })
                ->leftJoin('nota_materia', function ($join) {
                    $join->on('nota_materia.fk_materia', '=', 'cursos.id');
                    $join->on('nota_materia.fk_usuario', '=', 'alunos.fk_usuario_id');
                })
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('estrutura_curricular.slug', '=', $turma)
                ->where('cursos_categoria.slug_categoria', '=', $disciplina)
                ->where('cursos.slug_curso', '=', $materia)
                ->where('professor.fk_usuario_id', '=', $idUsuario)
                ->orderBy('alunos.nome')
                ->get();


            // $alunosTurma = $alunosTurma->get()->unique();
            $data = [];
            /*foreach ($alunosTurma as $aluno) {
                
                
                $trabalhos = CursoModulo::select('cursos_modulos.*',
                    'cursos_trabalhos.titulo as trabalho_titulo',
                    'cursos_trabalhos_usuario.downloadPath',
                    'nota_atividade.nota as notaAtividade',
                    'nota_atividade.id as id_nota'
                )
                    ->join('cursos_trabalhos', 'cursos_trabalhos.fk_cursos', '=', 'cursos_modulos.fk_curso')
                    ->join('cursos_trabalhos_usuario', 'cursos_trabalhos_usuario.fk_cursos_trabalhos', '=', 'cursos_trabalhos.id')
                    ->leftJoin('nota_atividade', function ($join) use ($aluno) {
                        $join->on('nota_atividade.fk_modulo', '=', 'cursos_modulos.id');
                        $join->where('nota_atividade.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                    ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                    ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                    ->where('cursos_categoria.disciplina', '=', 1)
                    ->where('cursos_categoria.slug_categoria', '=', $disciplina)
                    ->where('cursos_modulos.fk_curso', $aluno['id_materia'])
                    ->where('cursos_modulos.tipo_atividade', '=', 'trabalho')
                    ->where('cursos_trabalhos_usuario.fk_usuario', '=', $aluno['fk_usuario_id'])
                    ->get();

                $exercicios = Quiz::select('cursos_modulos.*', 'quiz.id as id_quiz')->distinct()
                    ->join('quiz_resposta_aluno', 'quiz_resposta_aluno.fk_quiz', '=', 'quiz.id')
                    ->leftJoin('nota_atividade', function ($join) use ($aluno) {
                        $join->on('nota_atividade.fk_modulo', '=', 'quiz_resposta_aluno.fk_modulo');
                        $join->where('nota_atividade.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->join('cursos_modulos', 'cursos_modulos.id', 'quiz_resposta_aluno.fk_modulo')
                    ->where('cursos_modulos.fk_curso', $aluno['id_materia'])
                    ->where('cursos_modulos.tipo_atividade', '=', 'quiz')
                    ->where('quiz_resposta_aluno.fk_usuario', $aluno['fk_usuario_id'])
                    ->get();

                foreach ($exercicios as &$exercicio) {
                    $exercicio['questoes'] = QuizQuestao::select(
                        'quiz_questao.*',
                        'quiz_resposta_aluno.id as id_resposta_aluno',
                        'quiz_resposta_aluno.resposta_aluno',
                        'quiz_resposta_aluno.resposta_professor'
                    ) 
                        ->join('quiz_resposta_aluno', 'quiz_resposta_aluno.fk_questao', '=', 'quiz_questao.id')
                        ->where('quiz_questao.fk_quiz', $exercicio['id_quiz'])
                        ->where('quiz_resposta_aluno.fk_usuario', $aluno['fk_usuario_id'])
                        ->get();

                    foreach ($exercicio['questoes'] as &$questao) {
                        $questao['alternativas'] = [];

                        if ($questao['dissertativa'] === 0) {
                            $questao['alternativas'] = QuizResposta::where('fk_quiz_questao', $questao['id'])->get();
                        }
                    }
                }
                
                
                $aluno = collect($aluno);
                if ($trabalhos)  {
                    $aluno->put('trabalhos', $trabalhos);
                }
                if ($exercicios) $aluno->put('exercicios', $exercicios);
                array_push($data, $aluno);
            }*/
            return response()->json([
                'items' => $alunosTurma,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $turma
     * @param $disciplina
     * @param $materia
     * @param $idUsuario
     * @return JsonResponse
     */
    public function trabalhosCorrecao($turma, $disciplina, $materia, $idUsuario) {
        try {
            $trabalhos = CursoModulo::select('cursos_modulos.*',
                'cursos_secao.id as id_aula',
                'cursos_secao.titulo as aulaAtividade',
                'cursos_secao.ordem as aula_ordem',
                'cursos_secao.data_disponibilidade as data_aula',
                'cursos_trabalhos.titulo as trabalho_titulo',
                'cursos_trabalhos.id as id_trabalho'
            )
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->join('cursos_trabalhos', 'cursos_trabalhos.fk_cursos', '=', 'cursos_modulos.fk_curso')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('estrutura_curricular.slug', '=', $turma)
                ->where('cursos_categoria.slug_categoria', '=', $disciplina)
                ->where('cursos.slug_curso', '=', $materia)
                ->where('professor.fk_usuario_id', '=', $idUsuario)
                ->where('cursos_modulos.tipo_atividade', '=', 'trabalho')
                ->get();

            $data = [];
            foreach ($trabalhos as $trabalho) {
                $alunos = Aluno::select('alunos.id as aluno_id',
                    \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome_aluno"),
                    'alunos.fk_usuario_id',
                    'nota_atividade.nota as notaAtividade',
                    'nota_atividade.id as id_nota',
                    'cursos_trabalhos_usuario.downloadPath',
                    'cursos_trabalhos_usuario.fk_usuario as usuario_id'
                )
                    ->leftJoin('nota_atividade', function ($join) use ($trabalho) {
                        $join->on('nota_atividade.fk_usuario', '=', 'alunos.fk_usuario_id');
                        $join->where('nota_atividade.fk_modulo', '=', $trabalho['id']);
                    })
                    ->join('cursos_trabalhos_usuario', 'cursos_trabalhos_usuario.fk_usuario', '=', 'alunos.fk_usuario_id')
                    ->where('cursos_trabalhos_usuario.fk_cursos_trabalhos', $trabalho['id_trabalho'])
                    ->orderBy('alunos.nome')
                    ->get()
                    ->unique();
                $trabalho = collect($trabalho);
                if ($alunos) {
                    $trabalho->put('alunos', $alunos);
                    array_push($data, $trabalho);
                }
            }

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $turma
     * @param $disciplina
     * @param $materia
     * @param $idUsuario
     * @return JsonResponse
     */
    public function exericiosCorrecao($turma, $disciplina, $materia, $idUsuario) {
        try {
            $exercicios = Quiz::select('cursos_modulos.*',
                'quiz.id as id_quiz',
                'cursos_modulos.*',
                'cursos_secao.id as id_aula',
                'cursos_secao.titulo as aulaAtividade',
                'cursos_secao.ordem as aula_ordem',
                'cursos_secao.data_disponibilidade as data_aula'
            )
                ->join('cursos_modulos', 'cursos_modulos.fk_quiz', 'quiz.id')
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('estrutura_curricular.slug', '=', $turma)
                ->where('cursos_categoria.slug_categoria', '=', $disciplina)
                ->where('cursos.slug_curso', '=', $materia)
                ->where('professor.fk_usuario_id', '=', $idUsuario)
                ->where('cursos_modulos.tipo_atividade', '=', 'quiz')
                ->get();

            $data = [];
            foreach ($exercicios as $exercicio) {
                $alunos = Aluno::select('alunos.id as aluno_id',
                    \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome_aluno"),
                    'alunos.fk_usuario_id',
                    'nota_atividade.nota as notaAtividade',
                    'nota_atividade.id as id_nota'
                )
                    ->leftJoin('nota_atividade', function ($join) use ($exercicio) {
                        $join->on('nota_atividade.fk_usuario', '=', 'alunos.fk_usuario_id');
                        $join->where('nota_atividade.fk_modulo', '=', $exercicio['id']);
                    })
                    ->join('quiz_resposta_aluno', 'quiz_resposta_aluno.fk_usuario', '=', 'alunos.fk_usuario_id')
                    ->where('quiz_resposta_aluno.fk_quiz', $exercicio['id_quiz'])
                    ->distinct()
                    ->orderBy('alunos.nome')
                    ->get();
                $exercicio = collect($exercicio);
                if ($alunos) {
                    $exercicio->put('alunos', $alunos);
                    array_push($data, $exercicio);
                }
            }

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $turma
     * @param $disciplina
     * @param $materia
     * @param $idUsuario
     * @return JsonResponse
     */
    public function presencialCorrecao($turma, $disciplina, $materia, $idUsuario) {
        try {
            $exercicios = CursoModulo::select('cursos_modulos.*',
                'cursos_secao.id as id_aula',
                'cursos_secao.titulo as aulaAtividade',
                'cursos_secao.ordem as aula_ordem',
                'cursos_secao.data_disponibilidade as data_aula'
            )
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('estrutura_curricular.slug', '=', $turma)
                ->where('cursos_categoria.slug_categoria', '=', $disciplina)
                ->where('cursos.slug_curso', '=', $materia)
                ->where('professor.fk_usuario_id', '=', $idUsuario)
                ->where('cursos_modulos.tipo_atividade', '=', 'presencial')
                ->get();

            $data = [];
            foreach ($exercicios as $exercicio) {
                $alunos = Aluno::select('alunos.id as aluno_id',
                    \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome_aluno"),
                    'alunos.fk_usuario_id',
                    'nota_atividade.nota as notaAtividade',
                    'nota_atividade.id as id_nota'
                )
                    ->leftJoin('nota_atividade', function ($join) use ($exercicio) {
                        $join->on('nota_atividade.fk_usuario', '=', 'alunos.fk_usuario_id');
                        $join->where('nota_atividade.fk_modulo', '=', $exercicio['id']);
                    })
                    ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_usuario', '=', 'alunos.fk_usuario_id')
                    ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_usuario.fk_estrutura')
                    ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_estrutura', '=', 'estrutura_curricular.id')
                    ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                    ->where('cursos.id', $exercicio['fk_curso'])
                    ->orderBy('alunos.nome')
                    ->get();
                $exercicio = collect($exercicio);
                if ($alunos) {
                    $exercicio->put('alunos', $alunos);
                    array_push($data, $exercicio);
                }
            }

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function professoresEscola($slugEscola = null) {
        try {

            $professores = Usuario::select(
                'professor.id',
                'usuarios.nome',
                'usuarios.email',
                'usuarios.fk_perfil',
                'escolas.razao_social as nome_escola'
            )->distinct()
                ->join('professor', 'professor.fk_usuario_id', 'usuarios.id')
                ->join('professor_escola', 'professor_escola.fk_professor', 'professor.id')
                ->join('escolas', 'escolas.id', 'professor_escola.fk_escola');

            if (!empty($slugEscola) &&$slugEscola != 'undefined') {
                $professores->where('escolas.slug', $slugEscola);
            }

            $user = JWTAuth::parseToken()->authenticate();
            if ($user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $professores->join('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')
                    ->where('estrutura_curricular.fk_orientador', '=', $user->id);
            }

            $data = $professores->get();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function show($idProfessor) {

        try {

            $professor = Professor::select(
                'professor.id',
                'usuarios.fk_perfil',
                'usuarios.email',
                'usuarios.senha_texto',
                'professor.cpf',
                'professor.telefone_1',
                'professor.telefone_2',
                'usuarios.nome',
                'usuarios.id as id_usuario',
                'professor_escola.fk_escola'
            )
                ->join('professor_escola', 'professor_escola.fk_professor', 'professor.id')
                ->join('usuarios', 'professor.fk_usuario_id', 'usuarios.id')
                ->where('professor.id', $idProfessor);

            $data = $professor->first();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function create(Request $request) {
        try {

            $senha = $this->gerarSenha(8, true, true, true, true);
            $usuario = Usuario::create(
                [
                    'email' => $request->get('email'),
                    'nome' => $request->get('nome'),
                    'fk_perfil' => UsuariosPerfil::PROFESSOR,
                    'status' => 1,
                    'password' => bcrypt($senha),
                    'senha_texto' => $senha,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $nome = explode(' ',$request->get('nome'));
            $firstName = $nome[0];
            unset($nome[0]);
            $professor = Professor::create(
                [
                    'nome' => $firstName,
                    'sobre_nome' => join(' ', $nome),
                    'cpf' => $request->get('cpf'),
                    'telefone_1' => $request->get('telefone_1'),
                    'telefone_2' => $request->get('telefone_2'),
                    'fk_usuario_id' => $usuario->id,
                ]
            );


            ProfessorEscola::create(
                [
                    'fk_professor' => $professor->id,
                    'fk_escola' => $request->get('fk_escola')
                ]
            );

            $professor->senha = $senha;
            return response()->json($professor);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request) {
        try {

            $usuario = Usuario::updateOrCreate(
                [
                    'id' => $request->get('id_usuario')
                ],
                [
                    'email' => $request->get('email'),
                    'nome' => $request->get('nome'),
                    'fk_perfil' => UsuariosPerfil::PROFESSOR,
                    'status' => 1,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $nome = explode(' ',$request->get('nome'));
            $firstName = $nome[0];
            unset($nome[0]);
            $professor = Professor::updateOrCreate(
                [
                    'id' => $request->get('id')
                ],
                [
                    'nome' => $firstName,
                    'sobre_nome' => join(' ', $nome),
                    'cpf' => $request->get('cpf'),
                    'telefone_1' => $request->get('telefone_1'),
                    'telefone_2' => $request->get('telefone_2'),
                    'fk_usuario_id' => $usuario->id,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );


            ProfessorEscola::updateOrCreate(
                [
                    'fk_professor' => $professor->id,
                    'fk_escola' => $request->get('fk_escola')
                ],
                [
                    'fk_professor' => $professor->id,
                    'fk_escola' => $request->get('fk_escola')
                ]
            );

            return response()->json($professor);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
