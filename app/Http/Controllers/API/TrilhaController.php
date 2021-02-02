<?php

namespace App\Http\Controllers\API;

use App\CursoCategoria;
use App\Helper\EducazMail;
use App\TrilhaFavorita;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Trilha;
use App\TrilhaCurso;
use App\Curso;
use App\Faculdade;
use Illuminate\Support\Facades\DB;

class TrilhaController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idTrilha = false)
    {
        try {
            if ($idTrilha) {
                $data = Trilha::obter($idTrilha);
                return response()->json([
                    'data' => $data
                ]);
            } else {
                $data = Trilha::lista();
                return response()->json([
                    'items' => $data,
                    'count' => count($data)
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

    public function cursos($idTrilha)
    {
        try {
            $data = TrilhaCurso::cursosTrilha($idTrilha);
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }

    public function categoria($idCategoria)
    {
        try {
            $data = Trilha::lista($idCategoria);
            return response()->json([
                'items' => $data,
                'count' => count($data)
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

    public function montaCardTrilha(Request $request)
    {
        try {
            $data = Trilha::lista(null, $request->header('Faculdade', 1));

            foreach ($data as $key => $dado) {
                $dado['total_curso'] = collect(TrilhaCurso::select(DB::raw("COUNT(*) as total_curso"))
                    ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
                    ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
                    ->join('professor', 'cursos.fk_professor', '=', 'professor.id')
                    ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
                    ->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha', '=', 'trilha.id')
                    ->where('trilha_curso.status', 1)
                    ->where('cursos.status', 5)
                    ->where('trilhas_faculdades.fk_faculdade', '=', $request->header('Faculdade', 1))
                    ->where('trilha.id', $dado->id)->get())->pluck('total_curso')[0];

                $data[$key]['gratis'] = isset($dado['gratis']) ? (int) $dado['gratis'] : 0;
            }
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }

    public function montaCardTrilhaPorCategoria($idCategoria, Request $request)
    {
        try {
            $data = Trilha::lista($idCategoria, $request->header('Faculdade', 1));
            foreach ($data as $key => $dado) {
                $dado['total_curso'] = collect(TrilhaCurso::select(DB::raw("COUNT(*) as total_curso"))
                    ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
                    ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
                    ->join('professor', 'cursos.fk_professor', '=', 'professor.id')
                    ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
                    ->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha', '=', 'trilha.id')
                    ->where('trilha_curso.status', 1)
                    ->where('cursos.status', 5)
                    ->where('trilhas_faculdades.fk_faculdade', '=', $request->header('Faculdade', 1))
                    ->where('trilha.id', $dado->id)->get())->pluck('total_curso')[0];

                $data[$key]['gratis'] = isset($dado['gratis']) ? (int) $dado['gratis'] : 0;
            }
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }

    public function cardSearch(Request $request) {
        try {
            $data = Trilha::searchTrilha($request->all());
            foreach ($data as $dado) {
                $dado['total_curso'] = collect(TrilhaCurso::select(DB::raw("COUNT(*) as total_curso"))
                    ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
                    ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
                    ->join('professor', 'cursos.fk_professor', '=', 'professor.id')
                    ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
                    ->where('trilha_curso.status', 1)
                    ->where('cursos.status', 5)
                    ->where('trilha.id', $dado->id)->get())->pluck('total_curso')[0];
            }
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema' . $e->getMessage()
            ]);
        }
    }
    public function detalhes($idTrilha)
    {
        try {
            $trilha = collect(Trilha::obter($idTrilha))->first();
            $cursos = TrilhaCurso::lista($idTrilha);
            $lista_categorias_selecionadas = CursoCategoria::select('cursos_categoria.*')
                ->join('trilhas_categoria', 'trilhas_categoria.fk_categoria', 'cursos_categoria.id')
                ->where('fk_trilha', '=', $idTrilha)
                ->distinct()
                ->get();
            $cont = 0;
            $categorias = '';
            foreach ($lista_categorias_selecionadas as $categoria) {
                if ($cont == 0) $categorias = '' . $categoria->titulo;
                else $categorias = $categorias . ', ' . $categoria->titulo;
                $cont++;
            }
            return response()->json([
                'trilha' => $trilha,
                'cursos' => $cursos,
                'categorias' => $categorias
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
     * Trilhas favoritas por aluno
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function favoritar(Request $request)
    {
        try {
            $data = $request->only('fk_usuario', 'fk_trilha');
            $favoritoObjeto = new TrilhaFavorita($data);

            return response()->json([
                'success' => ($favoritoObjeto->save() ? true : false)
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
     * Trilhas favoritas por aluno
     *
     * @param $idAluno
     * @return \Illuminate\Http\JsonResponse
     */
    public function favoritas($idAluno)
    {
        try {

            $favoritos = Trilha::trilhasFavorito($idAluno);

            return response()->json([
                'items' => $favoritos,
                'count' => count($favoritos)
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function desfavoritar(Request $request) {
        try {
            $aluno = $request->get('fk_usuario');
            $trilha = $request->get('fk_trilha');

            $favorito = TrilhaFavorita::where('fk_usuario', '=', $aluno)
                ->where('fk_trilha', '=', $trilha)
                ->first();
            return response()->json([
                'success' => ($favorito->delete() ? true : false)
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
     * @param $idTrilha
     * @param $status
     */
    public function trocaStatus($idTrilha, $status) {
        try {
            $trilha = Trilha::find($idTrilha);
            $trilha->status = $status;
            $trilha->update();
            return response()->json([
                'success' => true
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

    public function deletar($id)
    {
        $obj = Trilha::findOrFail($id);

        $obj->status = 0;

        $resultado = $obj->save();

        if ($resultado) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function retornaTrilhasDash(Request $request) {
        try {
            return response()->json([
                'trilhas' => Trilha::trilhasLista($request->all())
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
     * Lista de Categorias
     * @param int $idFaculdade
     * @return \Illuminate\Http\JsonResponse
     */
    public function categorias($idFaculdade = null)
    {
        try {
            $categoriasTrilhas = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.icone'
            )
            ->where('cursos_categoria.status', '=', 1);

            if ($idFaculdade) {
                $categoriasTrilhas
                    ->join('trilhas_categoria', 'trilhas_categoria.fk_categoria', '=', 'cursos_categoria.id')
                    ->join('trilha', 'trilhas_categoria.fk_trilha', '=', 'trilha.id')
                    ->join('trilhas_faculdades', 'trilhas_faculdades.fk_trilha', '=', 'trilha.id')
                    ->where('trilha.status', '=', 5)
                    ->where('trilhas_faculdades.fk_faculdade', '=', $idFaculdade)
                    ->distinct();
            }

            $data = $categoriasTrilhas->orderBy('cursos_categoria.titulo', 'ASC')->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function getTrilhaIDBySlug($slug_trilha){
        try {
            $trilha = Trilha::select('id','titulo','slug_trilha')->where('slug_trilha', $slug_trilha)->get()->toArray();
            return response()->json([
                'data' => $trilha[0]
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
}
