<?php

namespace App\Http\Controllers\EducazEscolas;

use App\CategoriaProfessor;
use App\CategoriaTurma;
use App\Curso;
use App\CursoCategoria;
use App\CursoModulo;
use App\Professor;
use App\Usuario;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DisciplinasController extends Controller
{
    //
    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    /**
     * Retorna as disciplinas por escola e turma sendo turma a estrutura_curricular
     * @param $idTurma
     * @param $idEscola
     * @return JsonResponse
     */
    public function index($idTurma, $idEscola, $idUsuario)
    {
        try {
            $disciplinas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.ementa',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.icone',
                'estrutura_curricular.titulo as turma',
                'escolas.razao_social as escola'
            )
            ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
            ->where('cursos_categoria.status', '=', 1)
            ->where('cursos_categoria.disciplina', '=', 1)
            ->where(
                [
                    ['estrutura_curricular.id', '=', $idTurma],
                    ['estrutura_curricular.fk_escola', '=', $idEscola],
                    ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                ]
            )
            ->orderBy('cursos_categoria.titulo', 'ASC');

            $data = $disciplinas->get()->unique();
            return response()->json($data);
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

    public function disciplinasProfessor($idTurma, $idEscola, $idUsuario) {
        try {

            $disciplinas = CursoCategoria::select('cursos_categoria.*')
                            ->join('categoria_professor', 'categoria_professor.fk_categoria', 'cursos_categoria.id')
                            ->join('categoria_turma', 'categoria_turma.fk_categoria', 'cursos_categoria.id')
                            ->join('estrutura_curricular', 'estrutura_curricular.id', 'categoria_turma.fk_turma')
                            ->join('estrutura_curricular_usuario', 'estrutura_curricular.id', 'estrutura_curricular_usuario.fk_estrutura')
                            ->where('fk_escola', $idEscola)
                            ->where('estrutura_curricular_usuario.fk_usuario', $idUsuario)
                            ->where('estrutura_curricular.id', $idTurma);

            $data = $disciplinas->get()->unique();
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
     * Retorna todas as disciplinas existentes (para o cadastro de matéria)
     * @return JsonResponse
     */
    public function disciplinas()
    {
        try {
            $disciplinas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.ementa',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.icone'
            )
                ->where('cursos_categoria.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->orderBy('cursos_categoria.titulo', 'ASC');

            $data = $disciplinas->get();
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
     * @param $id
     * @return JsonResponse
     */
    public function disciplinaById($id) {
        try {
            $disciplina = CursoCategoria::select(
                                'cursos_categoria.id',
                                'cursos_categoria.titulo',
                                'cursos_categoria.slug_categoria',
                                'cursos_categoria.ementa',
                                'categoria_turma.fk_turma'
                            )->join('categoria_turma', 'categoria_turma.fk_categoria', 'cursos_categoria.id')
                                ->where('cursos_categoria.id', $id)->first();

            return response()->json($disciplina);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $slug_categoria
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDisciplinaBySlug($slug_categoria)
    {
        try {
            $categoria = CursoCategoria::select('id','titulo','slug_categoria', 'ementa')
                ->where('slug_categoria', $slug_categoria)
                ->where('disciplina', 1)
                ->first();
            return response()->json([
                'data' => $categoria    
            ]);
        }  catch (\Exception $e) {
            // $sendMail = new EducazMail();
            // $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function create(Request $request) {
        try {
            DB::beginTransaction();
            $data = $request->all();

            $oUser = Usuario::where('id', $data['usuario'])->first();

            $categoria = CursoCategoria::create([
                'titulo' => $data['titulo'],
                'slug_categoria' =>  Str::slug($data['titulo'], '-') ,
                'ementa' => $data['ementa'],
                'disciplina' => 1,
            ]);

            /*CategoriaProfessor::create([
                'fk_categoria' =>   $categoria->id,
                'fk_usuario' => $oUser->id,
                'fk_criador_id' => $oUser->id
            ]);*/

            CategoriaTurma::create([
                'fk_categoria' =>   $categoria->id,
                'fk_turma' =>   $data['fk_turma'],
                'fk_criador_id' => $oUser->id
            ]);

            DB::commit();
            return $this->disciplinaById($categoria->id);
        }  catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function update(Request $request) {

        try {

            $data = $request->all();

            $categoria = CursoCategoria::findOrFail($data['id']);
            $categoria->update([
                'titulo' => $data['titulo'],
                'slug_categoria' =>  Str::slug($data['titulo'], '-') ,
                'ementa' => $data['ementa'],
            ]);

            $turma = CategoriaTurma::where('fk_categoria', $categoria->id)->first();
            $turma->update([
                'fk_categoria' =>   $categoria->id,
                'fk_turma' =>   $data['fk_turma'],
            ]);

            return $this->disciplinaById($categoria->id);
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function disciplinasEscola(Request $request, $slugRegional, $slugEscola) {
        try {

            $disciplinas = CursoCategoria::select(
                                'cursos_categoria.id',
                                'cursos_categoria.titulo',
                                'cursos_categoria.slug_categoria',
                                'cursos_categoria.fk_faculdade',
                                'cursos_categoria.icone',
                                'cursos_categoria.ementa'
                            )
                                ->join('categoria_turma', 'categoria_turma.fk_categoria', 'cursos_categoria.id')
                                ->join('estrutura_curricular', 'estrutura_curricular.id', 'categoria_turma.fk_turma')
                                ->join('escolas', 'estrutura_curricular.fk_escola', 'escolas.id')
                                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                                ->where('cursos_categoria.status', 1)
                                ->where('cursos_categoria.disciplina', 1)
                                ->where('diretoria_ensino.slug', $slugRegional)
                                ->where('escolas.slug', $slugEscola)
                                ->get();

            return response()->json($disciplinas);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function disciplinasTurma(Request $request, $slugRegional, $slugEscola, $slugTurma)
    {
        try {

            $disciplinas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.fk_faculdade',
                'cursos_categoria.icone',
                'cursos_categoria.ementa'
            )
                ->join('categoria_turma', 'categoria_turma.fk_categoria', 'cursos_categoria.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', 'categoria_turma.fk_turma')
                ->join('escolas', 'estrutura_curricular.fk_escola', 'escolas.id')
                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                ->where('cursos_categoria.status', 1)
                ->where('cursos_categoria.disciplina', 1)
                ->where('diretoria_ensino.slug', $slugRegional)
                ->where('escolas.slug', $slugEscola)
                ->where('estrutura_curricular.slug', $slugTurma)
                ->get();

            return response()->json($disciplinas);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param $idUsuario
     * @param $idTurma
     * @param $idDisciplina
     * @return JsonResponse
     */
    public function boletimDisciplina($idUsuario, $idTurma, $idDisciplina) {
        try {
            $boletim = Curso::select(
                'cursos.id',
                'cursos.titulo as column1',
                'cursos.slug_curso as slug_materia',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'nota_materia.nota as notaAtividade'
                // adicionar aqui nota por matéria
            )
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
                ->leftjoin('nota_materia', function ($join) use ($idUsuario) {
                    $join->on('nota_materia.fk_materia', '=', 'cursos.id');
                    $join->where('nota_materia.fk_usuario', '=', $idUsuario);
                })
                ->where('estrutura_curricular_conteudo.fk_categoria', '=', $idDisciplina)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ]
                )
                ->orderBy('cursos.titulo', 'ASC')
                ->get();

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
    
    public function boletimGlobal($idUsuario, $idTurma, $idEscola) {
        try {
            $disciplinas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo as column1',
                'cursos_categoria.slug_categoria',
                'nota_disciplina.nota as notaAtividade'
                // adicionar aqui nota por disciplina
            )
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('escolas', 'escolas.id', '=', 'estrutura_curricular.fk_escola')
                ->join('nota_disciplina', function ($join) use ($idUsuario) {
                    $join->on('nota_disciplina.fk_disciplina', '=', 'cursos_categoria.id');
                    $join->where('nota_disciplina.fk_usuario', '=', $idUsuario);
                })
                ->where('cursos_categoria.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where(
                    [
                        ['estrutura_curricular.id', '=', $idTurma],
                        ['estrutura_curricular.fk_escola', '=', $idEscola],
                        ['estrutura_curricular_usuario.fk_usuario', '=', $idUsuario],
                    ]
                )
                ->orderBy('cursos_categoria.titulo', 'ASC');

            $data = $disciplinas->get()->unique();
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
}
