<?php

namespace App\Http\Controllers\EducazEscolas;

use App\CategoriaProfessor;
use App\CategoriaTurma;
use App\Curso;
use App\CursoCategoria;
use App\Escola;
use App\EstruturaCurricular;
use App\Professor;
use App\Recados;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecadosController extends Controller
{
    //
    public function __construct()
    {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);

        parent::__construct();
    }

    public function index($idTurma, $idEscola, $idUsuario)
    {
        try {
            $disciplinas = Recados::select(
                'recados.*',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as professor_nome"),
                'cursos.titulo as nome_materia',
                'cursos_categoria.titulo as nome_disciplina'
            )
                ->join('cursos', 'cursos.id', '=', 'recados.fk_materia')
                ->join('professor', 'professor.id', '=', 'recados.fk_professor')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'recados.fk_materia')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
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
     * @param $idUsuario
     * @return JsonResponse
     */
    public function recadosProfessor($idUsuario, $slugMateria, $slugTurma) {
        try {

            $oUser = Professor::where('fk_usuario_id', $idUsuario)->first();

            $data = Recados::select(
                'recados.*',
                \DB::raw("concat(professor.nome, ' ', COALESCE(professor.sobrenome, '')) as professor_nome"),
                'cursos.titulo as nome_materia',
                'cursos_categoria.titulo as nome_disciplina'
            )
                ->join('cursos', 'cursos.id', '=', 'recados.fk_materia')
                ->join('professor', 'professor.id', '=', 'recados.fk_professor')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_conteudo', '=', 'recados.fk_materia')
                ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->where('cursos_categoria.status', '=', 1)
                ->where('cursos_categoria.disciplina', '=', 1)
                ->where('recados.fk_professor', $oUser->id)
                ->where('cursos.slug_curso', '=', $slugMateria)
                ->where('estrutura_curricular.slug', '=', $slugTurma)
                ->get();

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
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request) {
        try {
            $escola = Escola::where('slug',  $request->get('slugEscola'))->first();
            $turma = EstruturaCurricular::where('slug',  $request->get('slugTurma'))->first();
            $materia = Curso::where('slug_curso',  $request->get('slugMateria'))->first();
            $oUser = Professor::where('fk_usuario_id', $request->get('professor'))->first();
            
            if ($escola && $turma) {
                $new_recado = Recados::create([
                    'mensagem' => $request->get('mensagem'),
                    'fk_professor' => $oUser->id,
                    'fk_turma' => $turma->id,
                    'fk_escola' => $escola->id,
                    'fk_materia' => ($materia->id) ? $materia->id : null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'É necessário enviar a escola e a turma para as quais o aviso será direcionado'
                ]);
            }
            
            if($new_recado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recado cadastrado com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Houve um problema ao cadastrar recado',
                'erro' => $new_recado
            ]);
        } catch (\Exception $e) {
            // $sendMail = new EducazMail(7);
            //$sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id) {
        try {
            $recado = Recados::find($id);
            
            return response()->json([
                'success' => true,
                'dados' => $recado
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
    public function update($id, Request $request) {
        try {
            $recado = Recados::find($id);
            $recado->mensagem = $request->get('mensagem');

            if($recado->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recado atualizado com sucesso'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Houve um problema ao atualizar recado',
                'erro' => $recado
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
    public function destroy($id) {
        try {
            $recado = Recados::destroy($id);

            if($recado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recado excluído com sucesso'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Houve um problema ao excluir recado',
                'erro' => $recado
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
