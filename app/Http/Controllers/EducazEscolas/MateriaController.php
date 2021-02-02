<?php

namespace App\Http\Controllers\EducazEscolas;

use App\Aluno;
use App\ConclusaoCursosFaculdades;
use App\Curso;
use App\CursoCategoria;
use App\CursoCategoriaCurso;
use App\CursoModulo;
use App\CursoSecao;
use App\CursosFaculdades;
use App\CursosTrabalhos;
use App\CursosTrabalhosUsuarios;
use App\CursoTag;
use App\CursoTurmaAgenda;
use App\CursoValor;
use App\EstruturaCurricular;
use App\EstruturaCurricularConteudo;
use App\EstruturaCurricularUsuario;
use App\Helper\CertificadoHelper;
use App\ModuloUsuario;
use App\Professor;
use App\Quiz;
use App\QuizQuestao;
use App\QuizResposta;
use App\Usuario;
use App\UsuariosPerfil;
use App\UsuariosModulos;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use DateTime;

class MateriaController extends Controller
{
    //
    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    /**
     * Retorna as matérias do aluno de acordo com disciplina, turma, escola e aluno
     * @param $idDisciplina
     * @param $idTurma
     * @param $idEscola
     * @param $idUsuario
     * @return JsonResponse
     */
    public function index($idDisciplina, $idTurma, $idEscola, $idUsuario)
    {
        try {
            $materias = Curso::select(
                'cursos.id',
                'cursos.titulo as nome_materia',
                'cursos.slug_curso as slug_materia',
                'cursos.imagem',
                'professor.fk_usuario_id',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as nome_professor"),
                'estrutura_curricular.titulo as turma',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.ementa',
                'cursos_categoria.icone',
                'escolas.razao_social as escola'
            )
            ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
            ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->where('cursos_categoria.status', '=', 1)
            ->where('cursos_categoria.disciplina', '=', 1)
            ->where(
                [
                    ['estrutura_curricular.id', '=', $idTurma],
                    ['estrutura_curricular.fk_escola', '=', $idEscola],
                    ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ['estrutura_curricular_conteudo.fk_categoria', '=', $idDisciplina],
                ]
            )
            ->orderBy('nome_materia', 'ASC');

            $data = $materias->get();
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
     * Retorna as matérias do aluno de acordo com disciplina, turma, escola e aluno (mandar slug de cada um dos parâmetros, menos idUsuario)
     * @param $idDisciplina
     * @param $idTurma
     * @param $idEscola
     * @param $idUsuario
     * @return JsonResponse
     */
    public function professor($idDisciplina, $idTurma, $idEscola, $idUsuario)
    {
        try {
            $materias = Curso::select(
                'cursos.id',
                'cursos.titulo as nome_materia',
                'cursos.slug_curso as slug_materia',
                'cursos.imagem',
                'professor.fk_usuario_id',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as nome_professor"),
                'estrutura_curricular.titulo as turma',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.ementa',
                'cursos_categoria.icone',
                'escolas.razao_social as escola'
            )
            ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
            ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->where('cursos_categoria.status', '=', 1)
            ->where('cursos_categoria.disciplina', '=', 1)
            ->where('estrutura_curricular.slug', '=', $idTurma)
            ->where('cursos_categoria.slug_categoria', '=', $idDisciplina)
            ->where('escolas.slug', '=', $idEscola)

            ->orderBy('nome_materia', 'ASC');

            $user = JWTAuth::parseToken()->authenticate();
            if (!empty($user) && $user->fk_perfil == UsuariosPerfil::PROFESSOR) {
                $materias->where('professor.fk_usuario_id', '=', $idUsuario);
            }
            
            $materias = $materias->get()->unique();
            $data = [];
            foreach ($materias as $materia) {
                $atividades = CursoModulo::select(DB::raw("COUNT(cursos_modulos.id) as atividades"))
                    ->where('cursos_modulos.fk_curso', $materia->id)
                    ->get()
                    ->pluck('atividades');
                $aulas = CursoSecao::select(DB::raw("COUNT(cursos_secao.id) as aulas"))
                    ->where('cursos_secao.fk_curso', $materia->id)
                    ->get()
                    ->pluck('aulas');
                $materia = collect($materia);
                if ($atividades) $materia->put('atividades', $atividades[0]);
                if ($aulas) $materia->put('aulas', $aulas[0]);
                array_push($data, $materia);
            }
            
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

    /**
     * Retorna as matérias do aluno de acordo com disciplina, turma, escola e aluno
     * @param $idUsuario
     * @return JsonResponse
     */
    public function materiasProfessor($idUsuario)
    {
        try {
            $materias = Curso::select(
                'cursos.id',
                'cursos.titulo as nome_materia',
                'cursos.imagem',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as nome_professor"),
                'cursos_categoria.id',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.ementa',
                'cursos_categoria.icone'
                // adicionar aqui nome da escola
            )
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('cursos.fk_professor', $idUsuario)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->orderBy('nome_materia', 'ASC');

            $data = $materias->get();
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

    public function getAulasMateria() {
        try {

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
     * Show
     * @param $id
     * @return JsonResponse
     */
    public function show($id, $idUsuario)
    {
        try {
            $retorno['curso'] = Curso::findOrFail($id);

            $retorno['atividades'] = CursoModulo::select(
                'cursos_modulos.*',
                DB::raw('COUNT(modulos_usuarios.id) as assistido')
            )
                ->where('cursos_modulos.fk_curso', '=', $id)
                ->leftJoin('modulos_usuarios', function ($join) use ($idUsuario) {
                    $join->on('modulos_usuarios.fk_modulo', '=', 'cursos_modulos.id');
                    $join->where('modulos_usuarios.fk_usuario', '=', $idUsuario);
                })
                ->groupBy('cursos_modulos.id')
                ->get();
            

            /*$lista_faculdades = CursosFaculdades::select('cursos_faculdades.*')
                ->join('faculdades', 'faculdades.id', '=', 'cursos_faculdades.fk_faculdade')
                ->where('cursos_faculdades.fk_curso', $id)
                ->get()->toArray();*/

            $retorno['aulas']  = CursoSecao::select(
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo',
                'cursos_secao.ordem as secao_ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa'
            )
                ->where('cursos_secao.fk_curso', '=', $id)
                ->orderBy('cursos_secao.id')
                ->get();
            
            $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->first();

            if($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = QuizQuestao::select('*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if(count($quiz_questao)) {

                    foreach($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=', $questao->id)->get();
                        if(count($quiz_resposta)) {
                            foreach($quiz_resposta as $k_resposta => $resposta) {
                                $lista_resposta[$questao->id][] = $resposta;
                            }
                        }
                    }
                }

                $retorno['quiz'] = $quiz;
                $retorno['quiz_questao'] = $quiz_questao;
                $retorno['quiz_resposta'] = $lista_resposta;
            }
            $retorno['disciplina'] = CursoCategoria::where('cursos_categoria.status', '=', 1)
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('estrutura_curricular_conteudo.fk_conteudo', '=', $id)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->first();

            return response()->json([
                'data' => $retorno
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
     * Show
     * @param $id
     * @return JsonResponse
     */
    public function showAula($idAula, $idUsuario)
    {
        try {
            $retorno['curso'] = Curso::findOrFail($id);

            $retorno['atividades'] = CursoModulo::select('cursos_modulos.*', 'modulos_usuarios.id as assistido')
                ->where('cursos_modulos.fk_curso', '=', $id)
                ->leftJoin('modulos_usuarios', function ($join) use ($idUsuario) {
                    $join->on('modulos_usuarios.fk_modulo', '=', 'cursos_modulos.id');
                    $join->where('modulos_usuarios.fk_usuario', '=', $idUsuario);
                })
                ->get();

            /*$lista_faculdades = CursosFaculdades::select('cursos_faculdades.*')
                ->join('faculdades', 'faculdades.id', '=', 'cursos_faculdades.fk_faculdade')
                ->where('cursos_faculdades.fk_curso', $id)
                ->get()->toArray();*/

            $retorno['aulas']  = CursoSecao::select(
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo',
                'cursos_secao.ordem as secao_ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa'
            )
                ->where('cursos_secao.fk_curso', '=', $id)
                ->orderBy('cursos_secao.id')
                ->get();
            
            $quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->first();

            if($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = QuizQuestao::select('*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if(count($quiz_questao)) {

                    foreach($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=', $questao->id)->get();
                        if(count($quiz_resposta)) {
                            foreach($quiz_resposta as $k_resposta => $resposta) {
                                $lista_resposta[$questao->id][] = $resposta;
                            }
                        }
                    }
                }

                $retorno['quiz'] = $quiz;
                $retorno['quiz_questao'] = $quiz_questao;
                $retorno['quiz_resposta'] = $lista_resposta;
            }
            $retorno['disciplina'] = CursoCategoria::where('cursos_categoria.status', '=', 1)
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('estrutura_curricular_conteudo.fk_conteudo', '=', $id)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->first();

            return response()->json([
                'data' => $retorno
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

    public function getMateriaBySlug($slug_curso){
        try {
            $curso = Curso::select('cursos.id','cursos.titulo','cursos.slug_curso', 'cursos.publico_alvo', 'cursos.ementa')
                ->where('cursos.slug_curso', $slug_curso)
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->get()
                ->first();
            return response()->json([
                'data' => $curso
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail();
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function aulasProfessorSlug(Request $request, $slugMateria, $slugTurma) {
        try {

            $total_atividades = 0;
            $retorno = Curso::select(
                'cursos.id',
                'cursos.titulo',
                'cursos.slug_curso',
                'cursos.descricao',
                'cursos.objetivo_descricao',
                'cursos.publico_alvo',
                'cursos.imagem',
                'cursos.ementa',
                'cursos.fk_professor',
                'estrutura_curricular.titulo as turma'
            )
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->where('cursos.slug_curso', $slugMateria)
                ->where('estrutura_curricular.slug', $slugTurma)
                ->first();

            $retorno['aulas']  = CursoSecao::select(
                'cursos_secao.id',
                'cursos_secao.titulo',
                'cursos_secao.ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa',
                'cursos_secao.roteiro',
                'cursos_secao.habilidades'
            )
                ->where('cursos_secao.fk_curso', '=', $retorno->id)
                ->orderBy('cursos_secao.id')
                ->distinct()
                ->get();

            foreach ($retorno['aulas'] as &$aula) {
                //
                $aula['atividades'] = CursoModulo::select(
                    'cursos_modulos.id',
                    'cursos_modulos.titulo',
                    'cursos_modulos.descricao',
                    'cursos_modulos.url_video',
                    'cursos_modulos.url_arquivo',
                    'cursos_modulos.carga_horaria',
                    'cursos_modulos.fk_curso',
                    'cursos_modulos.fk_curso_secao',
                    'cursos_modulos.ordem',
                    'cursos_modulos.aula_ao_vivo',
                    'cursos_modulos.data_aula_ao_vivo',
                    'cursos_modulos.hora_aula_ao_vivo',
                    'cursos_modulos.link_aula_ao_vivo',
                    'cursos_modulos.data_fim_aula_ao_vivo',
                    'cursos_modulos.hora_fim_aula_ao_vivo',
                    'cursos_modulos.horario',
                    'cursos_modulos.tipo_atividade',
                    'cursos_modulos.endereco',
                    'cursos_modulos.fk_quiz',
                    'cursos_modulos.fk_trabalho',
                    'cursos_modulos.possui_nota',
                    'cursos_modulos.peso_media',
                    'cursos_modulos.fk_trabalho',
                    'cursos_modulos.criterio_nota',
                    'cursos_trabalhos.data_entrega'
                )
                    ->leftJoin('cursos_trabalhos', function ($join) {
                        $join->on('cursos_trabalhos.fk_cursos_modulo', '=', 'cursos_modulos.id');
                        $join->on('cursos_trabalhos.fk_cursos', '=', 'cursos_modulos.fk_curso');
                    })
                    ->where('cursos_modulos.fk_curso', '=', $retorno->id)
                    ->where('cursos_modulos.fk_curso_secao', $aula->id)
                    ->orderBy('ordem', 'asc')
                    ->get();

                $total_atividades = $total_atividades + count($aula['atividades']);
                foreach ($aula['atividades'] as &$atividade) {
                    $atividade['quiz'] = [];
                    if (!empty($atividade['fk_quiz'])) {
                        $atividade['quiz'] = ['test'];

                        /*quiz [
                            id,
                            fk_curso,
                            questoes [
                            id
                                fk_quiz
                                titulo
                                resposta_correta,
                                dissertativa,
                                alternativas [
                                id
                                    label
                                    descricao
                                    fk_quiz_questao
                                ]
                            ]
                        ]*/
                    }
                }
            }

            /*$quiz = Quiz::select('quiz.*')->where('quiz.fk_curso', '=', $id)->first();

            if($quiz) {
                $lista_questao = array();
                $lista_resposta = array();

                $quiz_questao = QuizQuestao::select('*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if(count($quiz_questao)) {

                    foreach($quiz_questao as $key => $questao) {
                        $lista_questao[$questao->id] = $questao;

                        $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=', $questao->id)->get();
                        if(count($quiz_resposta)) {
                            foreach($quiz_resposta as $k_resposta => $resposta) {
                                $lista_resposta[$questao->id][] = $resposta;
                            }
                        }
                    }
                }

                $retorno['quiz'] = $quiz;
                $retorno['quiz_questao'] = $quiz_questao;
                $retorno['quiz_resposta'] = $lista_resposta;
            }
            $retorno['disciplina'] = CursoCategoriaCurso::where('fk_curso', '=', $id)->first();*/
            $alunos = Aluno::select(
                'alunos.id as aluno_id',
                \DB::raw("concat(alunos.nome, ' ', COALESCE(alunos.sobre_nome, '')) as nome_aluno"),
                'alunos.fk_usuario_id',
                'estrutura_curricular.titulo as turma',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.slug_categoria',
                'cursos.id as id_materia',
                'cursos.titulo as nome_materia',
                'cursos.slug_curso as slug_materia'
            )
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_usuario', '=', 'alunos.fk_usuario_id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_usuario.fk_estrutura')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('estrutura_curricular.slug', '=', $slugTurma)
                ->where('cursos.slug_curso', '=', $slugMateria)
                // ->where('professor.fk_usuario_id', '=', $idUsuario) filtrar pelo professor
                ->orderBy('alunos.nome')
                ->get();

            $data = [];
            foreach ($alunos as $aluno) {
                $atividades = CursoModulo::select(
                    'cursos_modulos.id as id_atividade', 
                    'cursos_modulos.titulo as titulo_atividade', 
                    'cursos_modulos.criterio_nota',
                    DB::raw('COUNT(modulos_usuarios.id) as assistido'),
                    'cursos_secao.id as secao_id',
                    'cursos_secao.titulo as secao_titulo',
                    'cursos_secao.ordem as secao_ordem',
                    'cursos_secao.data_disponibilidade as data_atividade',
                    'cursos_secao.ementa',
                    'nota_atividade.nota as nota_atividade',
                    'nota_atividade.id as id_nota'
                    )
                    ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                    ->join('cursos', 'cursos.id', '=', 'cursos_modulos.fk_curso')
                    ->leftJoin('modulos_usuarios', function ($join) use ($aluno) {
                        $join->on('modulos_usuarios.fk_modulo', '=', 'cursos_modulos.id');
                        $join->where('modulos_usuarios.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->leftJoin('nota_atividade', function ($join) use ($aluno) {
                        $join->on('nota_atividade.fk_modulo', '=', 'cursos_modulos.id');
                        $join->where('nota_atividade.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->where('cursos.slug_curso', '=', $slugMateria)
                    ->groupBy('cursos_modulos.id')
                    ->get();
                
                $aulas_feitas = CursoModulo::select(
                    'cursos_modulos.*',
                    DB::raw('COUNT(modulos_usuarios.id) as assistido')
                )
                    ->join('modulos_usuarios', function ($join) use ($aluno) {
                        $join->on('modulos_usuarios.fk_modulo', '=', 'cursos_modulos.id');
                        $join->where('modulos_usuarios.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->join('cursos', 'cursos.id', '=', 'cursos_modulos.fk_curso')
                    ->where('cursos.slug_curso', '=', $slugMateria)
                    ->groupBy('cursos_modulos.id')
                    ->get();
                
                $boletim = CursoModulo::select('cursos_modulos.*')
                    ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                    ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                    ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                    ->join('nota_atividade', function ($join) use ($aluno) {
                        $join->on('nota_atividade.fk_modulo', '=', 'cursos_modulos.id');
                        $join->where('nota_atividade.fk_usuario', '=', $aluno['fk_usuario_id']);
                    })
                    ->join('cursos', 'cursos.id', '=', 'cursos_modulos.fk_curso')
                    ->where('cursos.slug_curso', '=', $slugMateria)
                    ->where('cursos_modulos.possui_nota', '=', 1)
                    ->where('cursos_categoria.disciplina', '=', 1)
                    ->where('estrutura_curricular_usuario.fk_usuario', $aluno['fk_usuario_id'])
                    ->where('cursos_modulos.tipo_atividade', '=', 'quiz')
                    ->get()
                    ->unique();
                
                $numero_aulas_feitas = count($aulas_feitas) + count($boletim);
                $getProgressoCurso = 0 .'%';
                if ($numero_aulas_feitas > 0) {
                    $getProgressoCurso = ($numero_aulas_feitas * 100) / $total_atividades;
                    $getProgressoCurso = number_format($getProgressoCurso, 2, ',', '.') .'%';
                }

                $aluno = collect($aluno);
                if ($atividades) $aluno->put('atividades', $atividades);
                $aluno->put('progresso_conclusao', $getProgressoCurso);
                $aluno->put('total_atividades', $total_atividades);
                $aluno->put('total_feitos', $numero_aulas_feitas);
                array_push($data, $aluno->toArray());
            }
            $retorno['alunos'] = $data;
            return response()->json($retorno);
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function aulasProfessor(Request $request, $id) {

        try {
            $retorno = Curso::select(
                'id',
                'titulo',
                'slug_curso',
                'descricao',
                'objetivo_descricao',
                'publico_alvo',
                'imagem',
                'ementa',
                'cursos.fk_professor'
            )->where('id', $id)->first();

            $retorno['aulas']  = CursoSecao::select(
                'cursos_secao.id',
                'cursos_secao.titulo',
                'cursos_secao.ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa',
                'cursos_secao.roteiro',
                'cursos_secao.habilidades'
            )
                ->where('cursos_secao.fk_curso', '=', $id)
                ->orderBy('cursos_secao.id')
                ->distinct()
                ->get();

            foreach ($retorno['aulas'] as &$aula) {
                //
                $aula['atividades'] = CursoModulo::select(
                                            'cursos_modulos.id',
                                            'cursos_modulos.titulo',
                                            'cursos_modulos.descricao',
                                            'cursos_modulos.url_video',
                                            'cursos_modulos.url_arquivo',
                                            'cursos_modulos.carga_horaria',
                                            'cursos_modulos.fk_curso',
                                            'cursos_modulos.fk_curso_secao',
                                            'cursos_modulos.ordem',
                                            'cursos_modulos.aula_ao_vivo',
                                            'cursos_modulos.data_aula_ao_vivo',
                                            'cursos_modulos.hora_aula_ao_vivo',
                                            'cursos_modulos.link_aula_ao_vivo',
                                            'cursos_modulos.data_fim_aula_ao_vivo',
                                            'cursos_modulos.hora_fim_aula_ao_vivo',
                                            'cursos_modulos.horario',
                                            'cursos_modulos.tipo_atividade',
                                            'cursos_modulos.endereco',
                                            'cursos_modulos.fk_quiz',
                                            'cursos_modulos.fk_trabalho',
                                            'cursos_modulos.possui_nota',
                                            'cursos_modulos.peso_media',
                                            'cursos_modulos.fk_trabalho',
                                            'cursos_modulos.criterio_nota',
                                            'cursos_trabalhos.data_entrega'
                                        )
                                        ->leftJoin('cursos_trabalhos', function ($join) {
                                            $join->on('cursos_trabalhos.fk_cursos_modulo', '=', 'cursos_modulos.id');
                                            $join->on('cursos_trabalhos.fk_cursos', '=', 'cursos_modulos.fk_curso');
                                        })
                                        ->where('cursos_modulos.fk_curso', '=', $id)
                                        ->where('cursos_modulos.fk_curso_secao', $aula->id)
                                        ->orderBy('ordem', 'asc')
                                        ->get();

                foreach ($aula['atividades'] as &$atividade) {
                    $atividade['quiz'] = [];
                    if (!empty($atividade['fk_quiz'])) {
                        $atividade['quiz'] = Quiz::select('id', 'fk_curso', 'percentual_acerto')->where('id', $atividade['fk_quiz'])->first();
                        if (!empty($atividade['quiz'])) {
                            $atividade['quiz']['questoes'] = QuizQuestao::select(
                                'id',
                                'fk_quiz',
                                'titulo',
                                'resposta_correta',
                                'dissertativa'
                            )->where('fk_quiz', $atividade['quiz']->id)->get();

                            foreach ($atividade['quiz']['questoes'] as &$questao ) {
                                $questao['alternativas'] = QuizResposta::select(
                                    'id',
                                    'descricao',
                                    'fk_quiz_questao'
                                )->where('fk_quiz_questao', $questao['id'])->get();
                            }
                        }
                    }
                }
            }

            return response()->json($retorno);
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }

    }
    
    public function criar(Request $request) {
        $dadosForm = $request->except('_token', 'id');

        $professor = Professor::where('fk_usuario_id', $dadosForm['usuario'])->first();
        if (!empty($dadosForm['fk_professor'])) {
            $professor = Professor::where('id', $dadosForm['fk_professor'])->first();
        }
        
        $dadosForm['slug_curso'] = Str::slug($dadosForm['titulo'], '-');

        try {
            DB::beginTransaction();

            $curso = new Curso();

            $dadosForm['titulo'] = ucwords(mb_strtolower(str_replace('_', ' ', $dadosForm['titulo']), 'UTF-8'));
            $dadosForm['ementa'] = ucwords(mb_strtolower(str_replace('_', ' ', $dadosForm['ementa']), 'UTF-8'));
            $dadosForm['descricao'] = ucwords(mb_strtolower(str_replace('_', ' ', $dadosForm['descricao']), 'UTF-8'));
            $dadosForm['objetivo_descricao'] = ($dadosForm['objetivo_descricao']) ? ucwords(mb_strtolower(str_replace('_', ' ', $dadosForm['objetivo_descricao']), 'UTF-8')) : '';
            $dadosForm['publico_alvo'] = ($dadosForm['publico_alvo']) ? ucwords(mb_strtolower(str_replace('_', ' ', $dadosForm['publico_alvo']), 'UTF-8')) : '';
            $dadosForm['fk_professor'] = $professor->id;

            $curso = $curso->create($dadosForm);

            $categoria = CursoCategoria::where('slug_categoria', $dadosForm['slug_disciplina'])->first();
            CursoCategoriaCurso::create([
                'fk_curso_categoria' => $categoria->id,
                'fk_curso' => $curso->id,
            ]);

            $turma = EstruturaCurricular::where('slug', $dadosForm['slug_turma'])->first();
            EstruturaCurricularConteudo::create([
                'fk_conteudo' => $curso->id,
                'fk_estrutura' => $turma->id,
                'fk_categoria' => $categoria->id
            ]);

            DB::commit();
            return $this->aulasProfessor($request, $curso->id);

        }  catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function tratarString($string) {
        return ucwords(mb_strtolower(str_replace('_', ' ', $string), 'UTF-8'));
    }
    
    public function atualizar(Request $request, $id) {
        try {
            \DB::beginTransaction();

            $dadosForm = $request->all();

            $curso = Curso::findOrFail($dadosForm['id']);

            $dadosForm['titulo'] = $this->tratarString($dadosForm['titulo']);
            $dadosForm['slug_curso'] = Str::slug($dadosForm['titulo'], '-');
            $dadosForm['descricao'] = $this->tratarString($dadosForm['descricao']);
            $dadosForm['objetivo_descricao'] = !empty($dadosForm['objetivo_descricao']) ? $this->tratarString($dadosForm['objetivo_descricao']) : '';
            $dadosForm['publico_alvo'] = !empty($dadosForm['publico_alvo']) ? $this->tratarString($dadosForm['publico_alvo']) : '';
            $dadosForm['ementa'] = $this->tratarString($dadosForm['ementa']);


            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $dadosForm['imagem'] = $this->uploadFile('imagem', $file);
            }

            $resultado = $curso->update($dadosForm);
            $this->aulas($dadosForm['aulas'], $curso);

            if (!$resultado) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível atualizar o registro!',
                ]);
            }

            \DB::commit();
            return $this->aulasProfessor($request, $curso->id);

        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function aulas($aulas, $materia) {

        foreach ($aulas as $key => $aula) {

            if (empty($aula['titulo'])) {
                continue;
            }

            $data = [
                'titulo' => $aula['titulo'],
                'ordem' => $key + 1,
                'data_disponibilidade' => $aula['data_disponibilidade'],
                'ementa' => $aula['ementa'],
                'roteiro' => $aula['roteiro'],
                'habilidades' => $aula['habilidades'],
                'objetivo' => $aula['objetivo'],
                'fk_curso' => $materia->id,
            ];

            $secao = CursoSecao::updateOrCreate(['id' => $aula['id']], $data);

            if (!empty($aula['atividades'])) {

                foreach ($aula['atividades'] as $keyAtiv => $atividade) {
                    if (!empty($atividade['titulo']) && $atividade['titulo'] != '') {

                        $modulo = CursoModulo::updateOrCreate(
                            ['id' => $atividade['id']],
                            [
                                'titulo' => $atividade['titulo'],
                                'descricao' => $atividade['descricao'],
                                'tipo_modulo' => 2,
                                'possui_nota' => $atividade['possui_nota'] == 'numerico' ? 1 : 0,
                                'fk_trabalho' => null,
                                'fk_quiz' => null,
                                'endereco' => $atividade['endereco'],
                                'tipo_atividade' => $atividade['tipo_atividade'],
                                'horario' => $atividade['horario'],
                                'link_aula_ao_vivo' => $atividade['link_aula_ao_vivo'],
                                'hora_aula_ao_vivo' => $atividade['hora_aula_ao_vivo'],
                                'data_aula_ao_vivo' => $atividade['data_aula_ao_vivo'],
                                'hora_fim_aula_ao_vivo' => isset($atividade['hora_fim_aula_ao_vivo']) ? $atividade['hora_fim_aula_ao_vivo'] : null,
                                'data_fim_aula_ao_vivo' => isset($atividade['data_fim_aula_ao_vivo']) ? $atividade['data_fim_aula_ao_vivo'] : null,
                                'aula_ao_vivo' => !empty($atividade['link_aula_ao_vivo']) ? 1: 0,
                                'ordem' => $keyAtiv + 1,
                                'fk_curso_secao' => $secao->id,
                                'status' => 1,
                                'fk_curso' => $materia->id,
                                'url_arquivo' => $atividade['url_arquivo'],
                                'url_video' => $atividade['url_video'],
                                'criterio_nota' => $atividade['criterio_nota'],
                                'peso_media' => $atividade['peso_media']
                            ]
                        );

                        if (!empty($atividade['tipo_atividade']) && $atividade['tipo_atividade'] == 'trabalho') {
                            $trabalho = CursosTrabalhos::updateOrCreate(
                                [
                                    'id' => $atividade['fk_trabalho']
                                ],
                                [
                                    'status' => 1,
                                    'titulo' => $atividade['titulo'],
                                    'fk_cursos' => $materia->id,
                                    'fk_cursos_modulo' => $modulo->id,
                                    'data_entrega' => $atividade['data_entrega']
                                ]
                            );

                            $modulo->update(['fk_trabalho' => $trabalho->id]);
                        }

                        if (!empty($atividade['quiz']['questoes']) && !empty($atividade['quiz']['questoes'][0]['titulo'])) {

                            $quiz = Quiz::updateOrCreate(
                                [
                                    'fk_curso' => !empty($atividade['quiz']['fk_curso']) ? $atividade['quiz']['fk_curso'] : null
                                ],
                                [
                                    'fk_curso' => $materia->id
                                ]
                            );

                            $modulo->update(['fk_quiz' => $quiz->id]);
                            if (!empty($atividade['quiz']['questoes'])) {
                                foreach ($atividade['quiz']['questoes'] as $questao) {

                                    if (!empty($questao['titulo'])) {
                                        $quizQuestao = QuizQuestao::updateOrCreate(
                                            [
                                                'id' => !empty($questao['id']) ? $questao['id'] : null
                                            ],
                                            [
                                                'fk_quiz' => $quiz->id,
                                                'titulo' => $questao['titulo'],
                                                'resposta_correta' => $questao['resposta_correta'],
                                                'dissertativa' => $questao['dissertativa'],

                                            ]
                                        );

                                        if (!empty($questao['alternativas'])) {
                                            foreach ($questao['alternativas'] as $alternativa) {
                                                if (!empty($alternativa['descricao'])) {
                                                    QuizResposta::updateOrCreate(
                                                        [
                                                            'id' => !empty($alternativa['id']) ? $alternativa['id'] : null
                                                        ],
                                                        [
                                                            'label' => $alternativa['label'],
                                                            'descricao' => $alternativa['descricao'],
                                                            'fk_quiz_questao' => $quizQuestao->id,
                                                        ]
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function testAt()
    {
        if (!empty($dadosForm['trabalho']) && $dadosForm['trabalho']) {
            $dataTrabalho = [
                'status' => 1,
                'titulo' => 'TCC',
                'fk_cursos' => $id
            ];

            CursosTrabalhos::updateOrCreate(
                [
                    'fk_cursos' => $id
                ],
                $dataTrabalho
            );
        }

        if ($dadosForm['questoes']) {
            if (isset($dadosForm['quiz']) && count($dadosForm['quiz'])) {
                //verificar se existe fk_quiz
                //se existir, continue normalmente
                //se não, crie novo Quiz, atualize variavel fk_quiz
                $quiz_id = null;
                if (!isset($dadosForm['quiz']['id'])) {
                    $quiz = new Quiz();
                    $array_insert_quiz = [
                        'fk_curso' => $id,
                        'percentual_acerto' => 0
                    ];
                    $resultado_quiz = $quiz->create($array_insert_quiz);
                    if ($resultado_quiz) {
                        $quiz_id = $resultado_quiz->id;
                    }
                } else {
                    $quiz_id = $dadosForm['quiz']['id'];
                    $quiz_to_update = Quiz::find($quiz_id);
                    if ($quiz_to_update) {
                        $quiz_to_update->percentual_acerto = 0;
                        $quiz_to_update->update();
                    }
                }

                $questoesids = collect($dadosForm['quiz']['questao'])->map(function ($item, $key) {
                    return $item['id'];
                });
                $questoesdelete = QuizQuestao::where('fk_quiz', $quiz_id)->whereNotIn('id', $questoesids)->get();

                foreach ($questoesdelete as $qtd) {
                    $qtd->delete();
                }

                foreach ($dadosForm['quiz']['questao'] as $key => $item) {
                    if (!empty($item['titulo'])) {
                        if (!empty($item['id'])) {
                            $quiz_questao = QuizQuestao::find($item['id']);
                            if ($quiz_questao) $quiz_questao->update($item);
                            $respostasids = collect($item['opcao'][$item['id']])->map(function ($opcao, $key) use ($item) {
                                return $opcao['id'];
                            });
                            $respostasdelete = QuizResposta::where('fk_quiz_questao', $item['id'])->whereNotIn('id', $respostasids)->get();

                            foreach ($respostasdelete as $rd) {
                                $rd->delete();
                            }
                            if (isset($item['opcao'])) {
                                foreach ($item['opcao'][$item['id']] as $op) {
                                    $quiz_resposta = QuizResposta::findOrFail($op['id']);
                                    if ($quiz_resposta) {
                                        $quiz_resposta->descricao = $op['descricao'];
                                        $quiz_resposta->update();
                                    }
                                }
                            } else {
                                for ($i = 1; $i <= 5; $i++) {

                                    if ($item['op']['descricao' . $i] != '') {
                                        $array_insert_quiz_resposta = [
                                            'label' => $i,
                                            'descricao' => $item['op']['descricao' . $i],
                                            'fk_quiz_questao' => $item['id']
                                        ];

                                        $quiz_resposta = new QuizResposta();
                                        $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                    }
                                }
                            }
                        } else {
                            if (!empty($item['titulo'])) {
                                $array_insert_quiz_questao = [
                                    'fk_quiz' => $quiz_id,
                                    'titulo' => $item['titulo'],
                                    'resposta_correta' => $item['resposta_correta'],
                                    'status' => '1',
                                ];

                                $quiz_questao = new QuizQuestao();
                                $resultado_quiz_questao = $quiz_questao->create($array_insert_quiz_questao);

                                for ($i = 1; $i <= 5; $i++) {
                                    if ($item['op']['descricao' . $i] != '') {
                                        $array_insert_quiz_resposta = [
                                            'label' => $i,
                                            'descricao' => $item['op']['descricao' . $i],
                                            'fk_quiz_questao' => $resultado_quiz_questao->id
                                        ];

                                        $quiz_resposta = new QuizResposta();
                                        $resultado_quiz_resposta = $quiz_resposta->create($array_insert_quiz_resposta);
                                        if (!$resultado_quiz_resposta) {
                                            \DB::rollBack();
                                            return response()->json([
                                                'success' => false,
                                                'error' => 'Não foi possível atualizar o registro!',
                                                'errors' => $resultado_quiz_resposta
                                            ]);
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }
        }
        ### fim QUIZ ###
    }

    /**
     * Retorna as aulas do usuário para o calendário
     * @param $idUsuario
     * @param $idTurma
     * @return JsonResponse
     */
    public function getCalendario($idUsuario, $idTurma) {
        try {
            $materias = CursoSecao::select(
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo',
                'cursos_secao.ordem as secao_ordem',
                'cursos_secao.data_disponibilidade',
                'cursos_secao.ementa'
            )
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_secao.fk_curso')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
                ->where('cursos_categoria.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('cursos_secao.data_disponibilidade', '!=', '')
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ]
                )->get();
            
            $data = collect($materias)->map(function ($materia) {
                if (!empty($materia['data_disponibilidade'])) {
                    $dados['id'] = $materia['secao_id'];
                    $dados['Subject'] = $materia['secao_titulo'];
                    $dados['StartTime'] = date('D M d Y H:i:s T', strtotime($materia['data_disponibilidade']));
                    $dados['EndTime'] = date('D M d Y H:i:s T', strtotime($materia['data_disponibilidade']));
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

    public function quizAtividade($id) {
        try {
            $quiz = Quiz::find($id);
            $retorno['questao'] = [];
            if ($quiz) {
                $quiz_questao = QuizQuestao::select('quiz_questao.*')->where('quiz_questao.fk_quiz', '=', $quiz->id)->get();
                if (count($quiz_questao)) {
                    foreach ($quiz_questao as $key => $questao) {
                        if (!empty($questao)) {
                            if (!$questao->dissertativa) {
                                $quiz_resposta = QuizResposta::select('*')->where('quiz_resposta.fk_quiz_questao', '=', $questao->id)->get();
                                $questao = collect($questao);
                                $questao->put('alternativas', $quiz_resposta);
                            }
                            array_push($retorno['questao'], $questao);
                        }
                    }
                }

                $retorno['quiz'] = $quiz;
            }

            return response()->json($retorno);

        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $id
     * @param $idUsuario
     * @return JsonResponse
     */
    public function quizAtividadeUsuario($id, $idUsuario) {
        try {
            $quiz = Quiz::find($id);
            $questoes = [];
            if ($quiz) {
                $questoes = QuizQuestao::select(
                    'quiz_questao.*',
                    'quiz_resposta_aluno.id as id_resposta_aluno',
                    'quiz_resposta_aluno.resposta_aluno',
                    'quiz_resposta_aluno.resposta_professor'
                )
                    ->where('quiz_questao.fk_quiz', $quiz->id)
                    ->join('quiz_resposta_aluno', 'quiz_resposta_aluno.fk_questao', '=', 'quiz_questao.id')
                    ->where('quiz_resposta_aluno.fk_usuario', $idUsuario)
                    ->get()
                    ->unique();

                foreach ($questoes as &$questao) {
                    $questao['alternativas'] = [];

                    if ($questao['dissertativa'] === 0) {
                        $questao['alternativas'] = QuizResposta::where('fk_quiz_questao', $questao['id'])->get();
                    }
                }
            }

            return response()->json([
                'data' => $questoes
            ]);
            
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function uploadFile(Request $request) {
        try {

            $input = $request->get('input');
            $tipo = $request->get('tipo');
            $file = $request->file('imagem');
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();

            $filePath = "files/escolas/{$tipo}/{$fileName}";
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            if (!$file->move('files/' . $tipo . '/' . $input, $fileName)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao salvar o arquivo'
                ]);
            }

            return response()->json(['success' => true, 'imageName' => $fileName ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @param $idMateria
     * @param $idAtividade
     * @return JsonResponse
     */
    public function trabalhoAtividade($idMateria, $idAtividade) {
        try {
            $trabalho = CursosTrabalhos::where('fk_curso', $idMateria)->where('fk_cursos_modulos', $idAtividade)->first();
            return response()->json([
                'data' => $trabalho
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
     * @param Request $request
     * @param $idCurso
     * @param $idModulo
     * @return JsonResponse|void
     */
    public function uploadTcc(Request $request, $idCurso, $idModulo, $idUsuario) {
        try {

            $aluno = Aluno::where('alunos.fk_usuario_id', '=', $idUsuario)->first();
            $curso = Curso::find($idCurso);

            $tcc = $request->file('tcc');
            $type = $tcc->getClientOriginalExtension();

            if (!$tcc) return;

            $date = new DateTime();
            $date = $date->format( 'd-m-Y' );

            $file_name = $aluno->nome . "-" . $curso->titulo . "_" . $date . "." . $type;
            $file_name = str_replace(' ', '_', $this->tirarAcentos($file_name));
            if ($tcc->move('files/escola/trabalhos', $file_name)) {


                 Storage::disk('s3')->put('/escola/trabalhos/'.$file_name, file_get_contents($tcc), 'public');

                $trabalho = CursosTrabalhos::where('cursos_trabalhos.fk_cursos', '=', $idCurso)
                    ->where('cursos_trabalhos.fk_cursos_modulo', '=', $idModulo)
                    ->firstOrFail();

                $trabalhoAluno = CursosTrabalhosUsuarios::create([
                    'fk_cursos_trabalhos' => $trabalho->id,
                    'fk_usuario' => $idUsuario,
                    'downloadPath' => '/files/escola/trabalhos' . $file_name,
                    'status' => 1,
                    'nota' => null,
                    'fk_criador_id' => $idUsuario,
                    'fk_modulo' => $idModulo
                ]);

                if ($trabalhoAluno) {
                    if ($idModulo) {
                        $modulo_concluido = ModuloUsuario::create([
                            'fk_modulo' => $idModulo,
                            'fk_usuario' => $idUsuario
                        ]);
                    }
                    return response()->json([
                        'success' => true,
                        'messages' => 'Trabalho enviado com sucesso!'
                    ]);
                }
            }
            return response()->json([
                'success' => false,
                'messages' => 'Erro ao enviar trabalho!'
            ]);

        }  catch (\Exception $e) {
           //  $sendMail = new EducazMail(7);
            // $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage()
            ]);
        }
    }
    
    public function resultadoQuizAtividade() {
        try {
            /* $trabalho = CursosTrabalhos::where('fk_curso', $idMateria)->where('fk_cursos_modulos', $idAtividade)->first();
            return response()->json([
                'data' => $trabalho
            ]); */
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
     * @param $idUsuario
     * @param $idTurma
     * @param $idMateria
     * @return JsonResponse
     */
    public function boletimMateria($idUsuario, $idTurma, $idMateria) {
        try {
            $boletim = CursoModulo::select('cursos_modulos.*',
                'cursos_secao.id as secao_id',
                'cursos_secao.titulo as secao_titulo as aulaAtividade',
                'cursos_secao.ordem as secao_ordem',
                'cursos_secao.data_disponibilidade as dataAtividade',
                'cursos_secao.ementa',
                'nota_atividade.nota as notaAtividade'
            // adicionar aqui o campo de nota
            )
                ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos_modulos.fk_curso')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('nota_atividade', function ($join) use ($idUsuario) {
                    $join->on('nota_atividade.fk_modulo', '=', 'cursos_modulos.id');
                    $join->where('nota_atividade.fk_usuario', '=', $idUsuario);
                })
                ->where('cursos_modulos.fk_curso', '=', $idMateria)
                ->where('cursos_modulos.possui_nota', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ]
                )
                ->get()
                ->unique();

            return response()->json([
                'items' => $boletim
            ]);
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            // $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
