<?php

namespace App\Http\Controllers;

use App\Aluno;
use App\ContaBancaria;
use App\Endereco;
use App\Faculdade;
use App\Usuario;
use App\Helper\EducazMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class PessoasController
 *
 * Esta classe cuida de PESSOAS tudo que possui CNPJ ou CPF, realizando a validação do CNPJ/CPF, se o mesmo ja
 * se encontra cadastrado na base, salvo alunos que podem ter vários CPF repetidos, porém em Universidades distintas,
 * evitanto assim a duplicação de registros.
 *
 * Esta classe cria o usuário automaticamente para cada tipo de pessoa e envia um e-mail com o acesso, login de acesso,
 * pode ser CNPJ ou E-MAIL, com a senha gerado no ato do cdastro e automática, ficando o administrador do sistema, com a
 * possibilidade de trocar a senha enviando outra requisição para o e-mail do usuário com a senha nova*
 * -Este processo pode ser melhorado no futuro com a introdução de uma página para troca de senhas.
 *
 * Classe para controlar todos os CRUDS, VALIDAÇÕES, USUÁRIOS, dos seguintes módulos, as views destes módulos estão
 * incorporadas a usuáriosficando da seguinte forma:
 *
 * Faculdades       ->  usuario.faculdade.lista, usuario.faculdade.formulario
 * Aluno         ->  usuario.aluno.lista,  usuario.aluno.formulario
 *
 * Parceiros Educaz pelo tipo de parceiro a tela será construida
 *
 * Professores     ->  usuario.professor.lista, usuario.professor.formulario
 * Produtoras       ->  usuario.produtora.lista, usuario.produtora.formulario
 * Parceiros       ->  usuario.parceiro.lista, usuario.parceiro.formulario
 * Curadores        ->  usuario.curador.lista, usuario.curador.formulario
 *
 * @package App\Http\Controllers
 */
class PessoasController extends Controller
{
    public function salvarRegistro(Request $request, Model $oCreate)
    {
        $data = $this->addValuesData($request, $oCreate);

        try {
             \DB::beginTransaction();

            // cria usuário
            $idUsuario = $this->criarUsuario($request, $oCreate);

            if ($oCreate instanceof Usuario) {
                \DB::commit();
                return [
                    'code' => 1,
                    'type' => 'mensagem_sucesso',
                    'message' => 'msgInsert',
                    'validatorMessage' => null
                ];
            }

            $endereco = $this->updateCreateAddress($data);
            
            if (isset($data['fk_perfil']) && !in_array($data['fk_perfil'], [13, 11, 10, 8, 22])
                && !($endereco)) {

                throw new \Exception('Error');
            }

            // add conta bancária
            // if (!empty($data['titular']) && !empty($data['conta_corrente'])) {
                if (isset($data['fk_perfil']) && !in_array($data['fk_perfil'], [13, 11, 10, 8, 22])
                && !($contaBancaria = $this->updateCreateAccountBank($data))) {
                    throw new \Exception('Error');
                }
            // }
              
            // cria cadastro
            $data['fk_usuario_id'] = $idUsuario;
            $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;
            $data['fk_conta_bancaria_id'] = !empty($contaBancaria->id) ? $contaBancaria->id : null;
            $data['fk_faculdade_id'] = !is_null($request->get('fk_faculdade_id')) ? $request->get('fk_faculdade_id') : null;
            

            $validator = $oCreate->_validate($data);
            if ($validator->fails()) {
                $this->validatorMsg = $validator;
                throw new \Exception('Error');
            }

            $oCreate->create($data);
            \DB::commit();
            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro inserido com sucesso!',
                'validatorMessage' => null
            ];
        } catch (\Exception $exception) {
            \DB::rollBack();
            if (empty($this->validatorMsg)) {
                $this->validatorMsg = 'Não foi possível inserir o registro! ' . $exception->getMessage();
            }
            return ['code' => -1, 'type' => '', 'message' => '', 'validatorMessage' => $this->validatorMsg];
        }
    }

    /**
     * @param Request $request
     * @param Model   $oUpdate
     * @return array
     */
    public function atualizarRegistro(Request $request, Model $oUpdate)
    {
        $data = $this->addValuesData($request, $oUpdate);
        $data = array_merge($data, [
            'fk_usuario_id' => $oUpdate->fk_usuario_id,
            'fk_endereco_id' => $oUpdate->fk_endereco_id
        ]);

        try {

            \DB::beginTransaction();

            $validator = $oUpdate->_validate($data);

            if ($validator->fails()) {
                $this->validatorMsg = $validator;
                return ['code' => -1, 'type' => 'mensagem_erro', 'message' => $this->validatorMsg, 'validatorMessage' => $this->validatorMsg];
            }

            if (!($oUpdate instanceof Usuario)) {

                if (isset($oUpdate->fk_endereco_id) && !$this->updateCreateAddress($data, $oUpdate->fk_endereco_id)) {
                    return ['code' => -1, 'type' => 'mensagem_erro', 'message' => 'Conferir os campos de obrigatórios de endereço!', 'validatorMessage' => 'Conferir os campos de obrigatórios de endereço!'];
                }

                //&& !(isset($oUpdate->fk_conta_bancaria_id)
                $contaBancaria = $this->updateCreateAccountBank($data, $oUpdate->fk_conta_bancaria_id);
                if (!($oUpdate instanceof Faculdade)
                    && (!$contaBancaria || !($contaBancaria instanceof ContaBancaria))
                ) {
                    return ['code' => -1, 'type' => 'mensagem_erro', 'message' => 'Conferir os campos de obrigatórios de conta bancária!', 'validatorMessage' => 'Conferir os campos de obrigatórios de conta bancária!'];
                }

                $data['fk_conta_bancaria_id'] = isset($oUpdate->fk_conta_bancaria_id) ? $oUpdate->fk_conta_bancaria_id : (isset($contaBancaria->id) ? $contaBancaria->id : null);

                /** @var Usuario $usuario */
                $usuario = Usuario::find($oUpdate->fk_usuario_id);
                $usuario->nome = $oUpdate->getName();
                $usuario->email = $data['email'];
                $usuario->fk_faculdade_id = !is_null($request->get('fk_faculdade_id')) ? $request->get('fk_faculdade_id') : null;

                if (!empty($data['password'])) {
                    $usuario->password = bcrypt($data['password']);
                }

                if (!empty($data['foto'])) {
                    if (Storage::disk('s3')->exists('files/usuario/' . $usuario->foto)) {
                        Storage::disk('s3')->delete('files/usuario/' . $usuario->foto);
                    }
                    $usuario->foto = $data['foto'];
                }

                if (!empty($data['email'])) {

                     $rules = [
                        'email' => [
                            'required',
                            'email',
                            Rule::unique('usuarios', 'email')->where(function ($query) use ($usuario, $data) {
                                if (!empty($usuario->id)) {
                                    $query->where('id', '!=', $usuario->id);
                                }
                                $query->where('email', '=', $data['email']);

                                if (!empty($data['fk_faculdade_id'])) {
                                    $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                                }

                                if (!empty($data['fk_perfil'])) {
                                    $query->where('fk_perfil', '=', $data['fk_perfil']);
                                }

                                $query->where('status', '=','1');
                            }),
                        ],
                    ];

                    $validatorUsuario = Validator::make($data, $rules,
                        [
                            'email.required' => 'E-mail é obrigatório',
                            'email.unique' => 'E-mail já cadastrado no sistema!',
                            'email.email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
                        ]);

                    if ($validatorUsuario->fails()) {
                        $this->validatorMsg = $validatorUsuario;
                        return ['code' => -1, 'type' => 'mensagem_erro', 'message' => $this->validatorMsg, 'validatorMessage' => $this->validatorMsg];
                    }

                    $usuario->email = $data['email'];
                }

                $usuario->save();
            }

            if (isset($data['foto']) && !empty($data['foto'])) {
                Auth()->guard('admin')->user()->foto = $data['foto'];
            }

            if (!empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }

            $oUpdate->fill($data);
            $oUpdate->save();

            \DB::commit();
            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro atualizado com sucesso!',
                'validatorMessage' => null
            ];

        } catch (\Exception $exception) {

            \DB::rollBack();
            if (empty($this->validatorMsg)) {
                $this->validatorMsg = 'Não foi possível atualizar o registro! ' . $exception->getMessage();
            }
            return ['code' => -1, 'type' => 'mensagem_erro', 'message' => $this->validatorMsg, 'validatorMessage' => $this->validatorMsg];
        }

    }

    /**
     * @param array $data
     * @param null  $id
     * @return mixed
     */
    public function updateCreateAccountBank(array $data, $id = null)
    {
        if (!$id) {
            $id = null;
        }

        $validator = ContaBancaria::validade($data);
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return false;
        }

        return ContaBancaria::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param array $data
     * @param null  $id
     * @return mixed
     */
    public function updateCreateAddress(array $data, $id = null)
    {
        if (!$id) {
            $id = null;
        }
        
        $validator = Endereco::validade($data);
        if ($validator->fails()) {
           
            $this->validatorMsg = $validator;
            return false;
        }
        
        return Endereco::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param Model $model
     * @return array
     */
    public function deletaRegistro(Model $model)
    {
        try {
            DB::beginTransaction();

            if (!($model instanceof Usuario)) {

                // verifica se existe usuário
                if (isset($model->fk_usuario_id) && $model->fk_usuario_id > 0) {
                    $oUser = Usuario::findOrFail($model->fk_usuario_id);
                    $oUser->delete();
                }
            }

            $model->delete();
            DB::commit();

            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro excluído com sucesso!',
                'validatorMessage' => null
            ];
        } catch (\Exception $error) {
            DB::rollBack();
            return [
                'code' => 2,
                'type' => 'mensagem_erro',
                'message' => 'Erro ao deletar registro',
                'validatorMessage' => null,
                'exception' => $error->getMessage()
            ];
        }
    }

    /**
     * Dados comuns a INSERT e UPDATE
     * @param Request $request
     * @param Model   $oUpdate
     * @return array|mixed
     */
    private function addValuesData(Request $request, Model $oUpdate)
    {
        $data = $request->except('_token');

        for ($qtdTelefones = 1; $qtdTelefones <= 3; $qtdTelefones++) {
            if (!empty($data['telefone_' . $qtdTelefones])) {
                $data['telefone_' . $qtdTelefones] = $this->removerMascaraSemUtilizacao($data['telefone_' . $qtdTelefones]);
            }
        }

        if (!empty($data['data_nascimento'])) {
            $data['data_nascimento'] = \DateTime::createFromFormat('d/m/Y', $data['data_nascimento'])->format('Y-m-d');
        }

        if ($request->file('foto')) {
            $data['foto'] = $this->uploadUserPhoto($request->file('foto'), $oUpdate);
        }

        return $data;
    }

    /**
     * @param UploadedFile $file
     * @param Model        $oUpdate
     * @return string|null
     */
    public function uploadUserPhoto($file, Model $oUpdate)
    {
        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();

            $filePath = 'files/usuario/' . $fileName;
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            return $fileName;
        }

        return 'default.png';
    }

    //----------------------------------------------------------------------------------------------------------------------

    /**
     * @param       $request
     * @param Model $model
     * @return int|mixed
     * @throws \Exception
     */
    private function criarUsuario($request, Model $model)
    {

        $verificaUsuarioExiste = Usuario::select('*')
            ->where('usuarios.email', '=', '"' . $request->input('email') . '"')
            ->where('usuarios.status', '=', '1');

        if ($model instanceof Usuario || $model instanceof Aluno) {
            $verificaUsuarioExiste->where('usuarios.fk_faculdade_id', '=',
                '"' . $request->input('fk_faculdade_id') . '"');
        }

        $verificaUsuarioExiste = $verificaUsuarioExiste->first();


        // se usuário existe, retorna id usuário (para vincular perfil ao usuário existente)
        if ($verificaUsuarioExiste) {
            return $verificaUsuarioExiste->id;
        }

        $oUser = new Usuario();
        $data = $this->addValuesData($request, $model);

        $senha = trim($request->input('password'));
        $senha_confirmed = $request->input('password_confirmation');
        if (empty($senha)) {
            $senha = $this->gerarSenha(10, true, true, true, true);
            $senha_confirmed = $senha;
        }

        $usuario['email'] = !empty($data['email']) ? $data['email'] : null;
        $usuario['password'] = $senha;
        $usuario['password_confirmation'] = $senha_confirmed;
        $usuario['fk_perfil'] = !empty($data['fk_perfil']) ? $data['fk_perfil'] : $model::ID_PERFIL;
        $usuario['fk_faculdade_id'] = !empty($data['fk_faculdade_id']) ? $data['fk_faculdade_id'] : null;
        $usuario['nome'] = $model->getName();
        $usuario['foto'] = !empty($data['foto']) ? $data['foto'] : null;

        $validator = $oUser->_validate($usuario, $model);
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
        }

        // encripta senha
        $usuario['password'] = bcrypt($senha);

        $oUser = $oUser->create($usuario);

        // Send Welcome Reg ister Email
        $usuario['senha'] = $senha;

        $educazMail = new EducazMail(!is_null($usuario['fk_faculdade_id']) ? $usuario['fk_faculdade_id'] : false);
        $educazMail->emailBoasVindas([
            'messageData' => [
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'senha' => $usuario['senha'],
            ]
        ]);

        return $oUser->id;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function recuperarSenhaUsuario($id)
    {
        $user = Usuario::findOrFail($id);
		$senha = $this->gerarSenha(10, true, true, true, false);

        $user->update(['password' => bcrypt($senha)]);

        $educazMail = new EducazMail(7);
        $educazMail->portalRecuperarSenha(
            [
                'messageData' => [
                    'nome' => $user->nome,
                    'email' => $user->email,
                    'nova_senha' => $senha,
                ]
            ]
        );
    }

    public function _return($result, $route = null)
    {
        if (!empty($result['message'])) {
            $message = $result['message'];

            if (!empty($result['exception'])) {
                $message .= ' Exception: '.$result['exception'];
            }

            \Session::flash($result['type'], $message);
        }

        if (!empty($result['validatorMessage'])) {
            return Redirect::back()->withErrors(
                        !empty($data['errors']) ? $data['errors'] : $this->validatorMsg
                    )->withInput();
        }

        if ($route) {
            return Redirect($route);
        }

        return Redirect::back();
    }
}
