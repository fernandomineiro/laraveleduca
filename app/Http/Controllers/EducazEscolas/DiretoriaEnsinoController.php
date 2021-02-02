<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 06/04/2020
 * Time: 22:56
 */

namespace App\Http\Controllers\EducazEscolas;

use App\DiretoriaEnsino;
use App\EstruturaCurricular;
use App\Gestao;
use App\Http\Controllers\Controller;
use App\UsuariosPerfil;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DiretoriaEnsinoController extends Controller {

    public function __construct()  {
        parent::__construct();
    }

    public function index(Request $request) {
        try {

            $diretorias = DiretoriaEnsino::select(
                'diretoria_ensino.id',
                'diretoria_ensino.nome',
                'diretoria_ensino.fk_faculdade',
                'diretoria_ensino.slug'
            )->distinct()
                ->where('diretoria_ensino.status', 1)
                ->where('diretoria_ensino.fk_faculdade', $request->header('Faculdade', 29));

            $user = JWTAuth::parseToken()->authenticate();
            if ($user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $diretorias
                    ->join('escolas', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                    ->join('estrutura_curricular', 'escolas.id', 'estrutura_curricular.fk_escola')
                    ->where('estrutura_curricular.fk_orientador', $user->id);
            }

            if ($user->fk_perfil == UsuariosPerfil::GESTOR_IES) {

                $gestao = Gestao::where('fk_usuario_id', $user->id)->first();
                if (!empty($gestao->fk_diretoria_ensino)) {
                    $diretorias->where('diretoria_ensino.id', $gestao->fk_diretoria_ensino);
                }
            }

            return response()->json($diretorias->get());

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function show(Request $request, $idDiretoria) {
        try {

            $diretorias = DiretoriaEnsino::select(
                'id',
                'nome',
                'fk_faculdade',
                'slug'
            )
                ->where('status', 1)
                ->where('fk_faculdade', $request->header('Faculdade', 29))
                ->where('id', $idDiretoria)
                ->first();

            return response()->json($diretorias);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function turmasRegional(Request $request, $slugRegional) {
        try {

            $turmas = EstruturaCurricular::select(
                'estrutura_curricular.id',
                'estrutura_curricular.titulo',
                'estrutura_curricular.fk_escola',
                'estrutura_curricular.slug',
                'escolas.id as idEscola',
                'escolas.razao_social as nomeEscola',
                'escolas.cnpj as cnpjEscola',
                'escolas.slug as slugEscola',
                'diretoria_ensino.id as idDiretoria',
                'diretoria_ensino.nome as nomeDiretoria',
                'diretoria_ensino.slug as slugDiretoria'
            )
                ->join('escolas', 'escolas.id', 'estrutura_curricular.fk_escola')
                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                ->where('diretoria_ensino.slug', $slugRegional)
                ->where('diretoria_ensino.fk_faculdade', $request->header('Faculdade', 29))
                ->get();

            return response()->json($turmas);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function turmasEscola(Request $request, $slugRegional, $slugEscola) {
        try {

            $turmas = EstruturaCurricular::select(
                'estrutura_curricular.id',
                'estrutura_curricular.titulo',
                'estrutura_curricular.fk_escola',
                'estrutura_curricular.slug',
                'estrutura_curricular.fk_orientador',
                'escolas.id as idEscola',
                'escolas.razao_social as nomeEscola',
                'escolas.cnpj as cnpjEscola',
                'escolas.slug as slugEscola',
                'diretoria_ensino.id as idDiretoria',
                'diretoria_ensino.nome as nomeDiretoria',
                'diretoria_ensino.slug as slugDiretoria',
                'usuarios.id as id_orientador',
                'usuarios.nome as nome_orientador'
            )
                ->join('escolas', 'escolas.id', 'estrutura_curricular.fk_escola')
                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                ->leftjoin('usuarios', 'usuarios.id', 'estrutura_curricular.fk_orientador')
                ->where('diretoria_ensino.slug', $slugRegional)
                ->where('escolas.slug', $slugEscola)
                ->where('diretoria_ensino.fk_faculdade', $request->header('Faculdade', 29))
                ;

            $user = JWTAuth::parseToken()->authenticate();

            if ($user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $turmas->where('estrutura_curricular.fk_orientador', $user->id);
            }

            return response()->json($turmas->get());

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }
    
    public function turmasProfessor(Request $request, $slugRegional, $slugEscola, $idUsuario) {
        try {

            $turmas = EstruturaCurricular::select(
                'estrutura_curricular.id',
                'estrutura_curricular.titulo',
                'estrutura_curricular.fk_escola',
                'estrutura_curricular.slug',
                'escolas.id as idEscola',
                'escolas.razao_social as nomeEscola',
                'escolas.cnpj as cnpjEscola',
                'escolas.slug as slugEscola',
                'diretoria_ensino.id as idDiretoria',
                'diretoria_ensino.nome as nomeDiretoria',
                'diretoria_ensino.slug as slugDiretoria',
                'cursos_categoria.id as id_disciplina',
                'cursos_categoria.titulo as nome_disciplina',
                'cursos_categoria.slug_categoria as slug_disciplina',
                'cursos.id as id_materia',
                'cursos.titulo as nome_materia',
                'cursos.slug_curso as slug_materia',
                'usuarios.nome as nome_orientador'
            )
                ->join('escolas', 'escolas.id', 'estrutura_curricular.fk_escola')
                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular.id', 'estrutura_curricular_usuario.fk_estrutura')
                ->join('estrutura_curricular_conteudo', 'estrutura_curricular_conteudo.fk_estrutura', '=', 'estrutura_curricular.id')
                ->join('cursos_categoria', 'estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria.id')
                ->join('cursos', 'cursos.id', '=', 'estrutura_curricular_conteudo.fk_conteudo')
                ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
                ->leftjoin('usuarios', 'usuarios.id', 'estrutura_curricular.fk_orientador')
                ->where('professor.fk_usuario_id', $idUsuario)
                ->where('diretoria_ensino.slug', $slugRegional)
                ->where('escolas.slug', $slugEscola)
                ->where('diretoria_ensino.fk_faculdade', $request->header('Faculdade', 29))
                ->distinct()
                ->get();
            //

            return response()->json($turmas);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
