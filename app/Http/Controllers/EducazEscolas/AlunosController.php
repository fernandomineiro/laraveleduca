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
use App\Gestao;
use App\Http\Controllers\Controller;
use App\Imports\AlunoEscolaImport;
use App\Usuario;
use App\UsuariosPerfil;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;

class AlunosController  extends Controller {

    protected $user;

    public function __construct() {
        parent::__construct();

        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index() {

        try {

            $alunos = Escola::select(
                            'escolas.id as id_escola',
                            'escolas.razao_social as nome_escola',
                            'escolas.fk_diretoria_ensino',
                            'escolas.slug as slug_escola',
                            'estrutura_curricular.id as id_turma',
                            'estrutura_curricular.titulo as nome_turma',
                            'estrutura_curricular.slug as slug_turma',
                            'alunos.id as id_aluno',
                            DB::raw('CONCAT(alunos.nome, " ", alunos.sobre_nome) as nome_aluno'),
                            'usuarios.id as id_usuario',
                            'usuarios.nome as nome_usuario'
                        )
                        ->join('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')
                        ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', 'estrutura_curricular.id')
                        ->join('usuarios', 'estrutura_curricular_usuario.fk_usuario', 'usuarios.id')
                        ->join('alunos', 'alunos.fk_usuario_id', 'usuarios.id');

            if ($this->user->fk_perfil == UsuariosPerfil::DIRETOR) {
                $alunos->where('escolas.fk_diretor', $this->user->id);
            }

            if ($this->user->fk_perfil == UsuariosPerfil::ORIENTADOR) {
                $alunos->where('estrutura_curricular.fk_orientador', $this->user->id);
            }

            if ($this->user->fk_perfil == UsuariosPerfil::GESTOR_IES) {
                $gestao = Gestao::where('fk_usuario_id', $this->user->id)->first();
                if (!empty($gestao->fk_diretoria_ensino)) {
                    $alunos->where('escolas.fk_diretoria_ensino', $gestao->fk_diretoria_ensino);
                }
            }

            $data = $alunos->get();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function show($idAluno) {

        try {

            $alunos = Escola::select(
                'escolas.id as id_escola',
                'escolas.razao_social as nome_escola',
                'diretoria_ensino.id as id_diretoria',
                'diretoria_ensino.nome as nome_diretoria',
                'diretoria_ensino.slug as slug_diretoria',
                'escolas.slug as slug_escola',
                'estrutura_curricular.id as id_turma',
                'estrutura_curricular.titulo as nome_turma',
                'estrutura_curricular.slug as slug_turma',
                'alunos.id as id_aluno',
                'alunos.cpf',
                'alunos.telefone_1',
                'alunos.telefone_2',
                DB::raw('CONCAT(alunos.nome, " ", alunos.sobre_nome) as nome_aluno'),
                'usuarios.id as id_usuario',
                'usuarios.nome as nome_usuario',
                'usuarios.email',
                'usuarios.senha_texto'
            )
                ->join('diretoria_ensino', 'diretoria_ensino.id', 'escolas.fk_diretoria_ensino')
                ->join('estrutura_curricular', 'estrutura_curricular.fk_escola', 'escolas.id')
                ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', 'estrutura_curricular.id')
                ->join('usuarios', 'estrutura_curricular_usuario.fk_usuario', 'usuarios.id')
                ->join('alunos', 'alunos.fk_usuario_id', 'usuarios.id')
                ->where('alunos.id', $idAluno);

            $data = $alunos->first();
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
                    'nome' => $request->get('nome_aluno'),
                    'fk_perfil' => UsuariosPerfil::ALUNO,
                    'status' => 1,
                    'password' => bcrypt($senha),
                    'senha_texto' => ($senha),
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $nome = explode(' ',$request->get('nome_aluno'));
            $firstName = $nome[0];
            unset($nome[0]);
            $aluno = Aluno::create(
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

            $estrutura = EstruturaCurricular::find($request->get('id_turma'));
            EstruturaCurricularUsuario::create(
                [
                    'fk_estrutura' => $estrutura->id,
                    'fk_usuario' => $usuario->id
                ]
            );

            $aluno->senha = $senha;
            return response()->json($aluno);

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
                    'nome' => $request->get('nome_aluno'),
                    'fk_perfil' => UsuariosPerfil::ALUNO,
                    'status' => 1,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $nome = explode(' ',$request->get('nome_aluno'));
            $firstName = $nome[0];
            unset($nome[0]);
            $aluno = Aluno::updateOrCreate(
                [
                    'id' => $request->get('id_aluno')
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

            $estrutura = EstruturaCurricular::find($request->get('id_turma'));
            EstruturaCurricularUsuario::updateOrCreate(
                [
                    'fk_estrutura' => $estrutura->id,
                    'fk_usuario' => $usuario->id
                ],
                [
                    'fk_estrutura' => $estrutura->id,
                    'fk_usuario' => $usuario->id
                ]
            );

            return response()->json($aluno);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function importar(Request $request) {
        try {
            Excel::import(new AlunoEscolaImport(), $request->file('arquivo'));

            return response()->json(['Alunos importados com sucesso']);

        } catch (\Exception $error) {

            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro. O suporte já foi avisado e está cuidando do caso.',
                'exception' => $error->getMessage(),
            ]);
        }
    }
}