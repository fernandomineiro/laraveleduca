<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 13/04/2020
 * Time: 11:01
 */

namespace App\Http\Controllers\EducazEscolas;

use App\Aluno;
use App\Escola;
use App\EstruturaCurricular;
use App\EstruturaCurricularUsuario;
use App\Http\Controllers\Controller;
use App\Imports\AlunoEscolaImport;
use App\Usuario;
use App\UsuariosPerfil;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OrientadorController  extends Controller {

    public function index() {

        try {

            $orientadores = Usuario::select(
                            'usuarios.id',
                            'usuarios.nome',
                            'usuarios.email',
                            'usuarios.fk_perfil',
                            'usuarios.senha_texto'
                           /* 'escolas.id as id_escola',
                            'escolas.razao_social as nome_escola',
                            'estrutura_curricular.id as id_turma',
                            'estrutura_curricular.titulo as nome_turma'*/
                        )
                            /*->leftjoin('estrutura_curricular', 'estrutura_curricular.fk_orientador', 'usuarios.id')
                            ->leftjoin('escolas', 'escolas.fk_diretor', 'estrutura_curricular.fk_escola')*/
                            ->where('fk_perfil', UsuariosPerfil::ORIENTADOR);

            $data = $orientadores->get();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function show($idDiretor) {

        try {

            $diretor = Usuario::select(
                            'usuarios.id',
                            'usuarios.nome',
                            'usuarios.email',
                            'usuarios.fk_perfil',
                            'usuarios.senha_texto'
                        )->where('fk_perfil', UsuariosPerfil::ORIENTADOR)
                        ->where('usuarios.id', $idDiretor);

            $data = $diretor->first();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
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
                    'fk_perfil' => UsuariosPerfil::ORIENTADOR,
                    'status' => 1,
                    'password' => bcrypt($senha),
                    'senha_texto' => $senha,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $usuario->senha = $senha;
            return response()->json($usuario);

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
                    'id' => $request->get('id')
                ],
                [
                    'email' => $request->get('email'),
                    'nome' => $request->get('nome'),
                    'fk_perfil' => UsuariosPerfil::ORIENTADOR,
                    'status' => 1,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            return response()->json($usuario);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }
}