<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 13/04/2020
 * Time: 11:01
 */

namespace App\Http\Controllers\EducazEscolas;

use App\Aluno;
use App\Endereco;
use App\Escola;
use App\EstruturaCurricular;
use App\EstruturaCurricularUsuario;
use App\Gestao;
use App\Http\Controllers\Controller;
use App\Imports\AlunoEscolaImport;
use App\Professor;
use App\Usuario;
use App\UsuariosPerfil;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController  extends Controller {

    protected $user;

    public function __construct() {
        parent::__construct();

        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function show($idUsuario) {

        try {

            $usuario = Usuario::select(
                'usuarios.id',
                'usuarios.nome',
                'usuarios.email',
                'usuarios.fk_perfil',
                'usuarios.foto'
            )->where('usuarios.id', $idUsuario);

            if (
                in_array($this->user->fk_perfil, [UsuariosPerfil::GESTOR_IES, UsuariosPerfil::DIRETOR, UsuariosPerfil::ORIENTADOR])
            ) {
                $usuario->addSelect(
                    'gestao_ies.cpf',
                    'gestao_ies.telefone_1',
                    'gestao_ies.telefone_2',
                    'gestao_ies.fk_endereco',
                    'endereco.cep',
                    'endereco.logradouro',
                    'endereco.numero',
                    'endereco.complemento',
                    'endereco.bairro'
                );
                $usuario->leftjoin('gestao_ies', 'gestao_ies.fk_usuario_id', 'usuarios.id');
                $usuario->leftjoin('endereco', 'gestao_ies.fk_endereco', 'endereco.id');

            } else if ($this->user->fk_perfil == UsuariosPerfil::PROFESSOR) {

                $usuario->addSelect(
                    'professor.cpf',
                    'professor.telefone_1',
                    'professor.telefone_2',
                    'professor.fk_endereco_id',
                    'professor.mini_curriculum',
                    'endereco.cep',
                    'endereco.logradouro',
                    'endereco.numero',
                    'endereco.complemento',
                    'endereco.bairro'
                );

                $usuario->leftjoin('professor', 'professor.fk_usuario_id', 'usuarios.id');
                $usuario->leftjoin('endereco', 'professor.fk_endereco_id', 'endereco.id');
            } else if ($this->user->fk_perfil == UsuariosPerfil::ALUNO) {
                $usuario->addSelect(
                    'alunos.cpf',
                    'alunos.telefone_1',
                    'alunos.telefone_2',
                    'alunos.fk_endereco_id',
                    'endereco.cep',
                    'endereco.logradouro',
                    'endereco.numero',
                    'endereco.complemento',
                    'endereco.bairro'
                );

                $usuario->leftjoin('alunos', 'alunos.fk_usuario_id', 'usuarios.id');
                $usuario->leftjoin('endereco', 'alunos.fk_endereco_id', 'endereco.id');
            }

            $data = $usuario->first();
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

    public function update(Request $request) {
        try {

            $usuario = Usuario::updateOrCreate(
                [
                    'id' => $request->get('id')
                ],
                [
                    'email' => $request->get('email'),
                    'nome' => $request->get('nome'),
                    'fk_perfil' => $request->get('fk_perfil'),
                    'status' => 1,
                    'fk_faculdade_id' => $request->header('Faculdade', 29)
                ]
            );

            $endereco = Endereco::updateOrCreate(
                [
                    'id' => $request->get('fk_endereco')
                ],
                [
                    'cep' => $request->get('cep'),
                    'logradouro' => $request->get('logradouro'),
                    'numero' => $request->get('numero'),
                    'complemento' => $request->get('complemento'),
                    'bairro' => $request->get('bairro')
                ]
            );

            if (
            in_array($this->user->fk_perfil, [UsuariosPerfil::GESTOR_IES, UsuariosPerfil::DIRETOR, UsuariosPerfil::ORIENTADOR])
            ) {

                Gestao::updateOrCreate(
                    [
                        'fk_usuario_id' => $usuario->id
                    ],
                    [
                        'nome' => $request->get('nome'),
                        'fk_usuario_id' => $usuario->id,
                        'cpf' => $request->get('cpf'),
                        'telefone_1' => $request->get('telefone_1'),
                        'telefone_2' => $request->get('telefone_2'),
                        'fk_endereco' => $endereco->id
                    ]
                );
            } else if ($this->user->fk_perfil == UsuariosPerfil::PROFESSOR) {
                Professor::updateOrCreate(
                    [
                        'fk_usuario_id' => $usuario->id
                    ],
                    [
                        'nome' => $request->get('nome'),
                        'fk_usuario_id' => $usuario->id,
                        'cpf' => $request->get('cpf'),
                        'telefone_1' => $request->get('telefone_1'),
                        'telefone_2' => $request->get('telefone_2'),
                        'mini_curriculum' => $request->get('mini_curriculum'),
                        'fk_endereco_id' => $endereco->id
                    ]
                );
            } else if ($this->user->fk_perfil == UsuariosPerfil::ALUNO) {

                $nome = explode(' ',$request->get('nome'));
                $firstName = $nome[0];
                unset($nome[0]);

                Aluno::updateOrCreate(
                    [
                        'fk_usuario_id' => $usuario->id
                    ],
                    [
                        'nome' => $firstName,
                        'sobre_nome' => join(' ', $nome),
                        'fk_usuario_id' => $usuario->id,
                        'cpf' => $request->get('cpf'),
                        'telefone_1' => $request->get('telefone_1'),
                        'telefone_2' => $request->get('telefone_2'),
                        'mini_curriculum' => $request->get('mini_curriculum'),
                        'fk_endereco_id' => $endereco->id
                    ]
                );
            }


            return $this->show($usuario->id);

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

    public function uploadFile(Request $request) {
        try {

            $file = $request->file('imagem');
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();

            $filePath = "files/usuario/{$fileName}";
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            if (!$file->move('files/usuario/', $fileName)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao salvar o arquivo'
                ]);
            }

            $usuario = Usuario::find($request->get('usuario'))->update(['foto' => $fileName]);

            return response()->json(['success' => true, 'imageName' => $fileName ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}