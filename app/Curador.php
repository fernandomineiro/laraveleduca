<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Curador extends Model {

    use EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';
    const ID_PERFIL = 4;
    const PERFIL_NOME = 'CURADOR';

    protected $table = 'curadores';
    protected $fillable = [
        'id',
        'wirecard_account_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'representante_legal',
        'share',
        'mini_curriculum',
        'status',
        'telefone_1',
        'telefone_2',
        'telefone_3',
        'fk_usuario_id',
        'fk_endereco_id',
        'fk_conta_bancaria_id',
        'data_criacao',
        'data_atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'titular_curador',
        'cpf',
        'data_nascimento',
        'genero'
    ];

    public $timestamps = true;

    public $rules = [
        'razao_social' => 'required',
        'nome_fantasia' => 'required',
        'cep' => 'required',
        'logradouro' => 'required',
        'bairro' => 'required',
        'numero' => 'required',
        'fk_estado_id' => 'required',
        'fk_cidade_id' => 'required',
        'data_nascimento' => 'required',
        'cpf' => 'required',
        'share' => 'required',
        'titular_curador' => 'required',
        'email' => 'required',
        'cnpj' => 'required'
    ];

    public $messages = [
        'razao_social.required' => 'Nome/Razão Social é obrigatório',
        //'cnpj.required' => 'CPF/CNPJ é obrigatório',
        'nome_fantasia.required' => 'Nome Fantasia é obrigatório',
        'cep.required' => 'Cep é obrigatório!',
        'logradouro.required' => 'Logradouro é obrigatório!',
        'numero.required' => 'Número é obrigatório!',
        'fk_estado_id.required' => 'Estado é obrigatório!',
        'fk_cidade_id.required' => 'Cidade é obrigatório!',
        'titular_curador.required' => 'Titular é obrigatório!',
        'data_nascimento.required' => 'Data de nascimento do titular da produtora/conta é obrigatório!',
        // 'share.required' => 'Share é um campo obrigatório!',
        'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'cpf.required' => 'CPF do titular da produtora/conta é obrigatório!',
        'cnpj.required' => 'CNPJ é obrigatório!',
        'cnpj.cpf_cnpj' => 'CNPJ inválido!',
        'cnpj.unique' => 'CNPJ já cadastrado!',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'email.required' => 'E-mail é obrigatório',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'email.email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
        'titular.required' => 'Títular é obrigatório',
        'fk_banco_id.required' => 'Banco é obrigatório',
        'agencia.required' => 'Agência é obrigatório',
        'conta_corrente.required' => 'Número da Conta é obrigatório',
        'tipo_conta.required' => 'Tipo de conta Conta é obrigatório',
        'documento.required' => 'CPF/CNPJ é obrigatório'
    ];

    /**
     * Retorna a classe Endereço associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endereco()
    {
        return $this->HasOne('\App\Endereco', 'id', 'fk_endereco_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function usuario()
    {
        return $this->HasOne('\App\Usuario', 'fk_usuario_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function conta()
    {
        return $this->HasOne('\App\ContaBancaria', 'fk_conta_bancaria_id');
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->titular_curador;
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data)
    {
        return (new Curador)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data)
    {
        $object = $this;
        $rule = [
            'cep' => 'required',
            'logradouro' => 'required',
            'bairro' => 'required',
            'numero' => 'required',
            'fk_estado_id' => 'required',
            'fk_cidade_id' => 'required',
            'data_nascimento' => 'required',
            'cpf' => 'required',
            //'share' => 'required',
//            'telefone_1' => 'required',
            'telefone_2' => 'required',
            'titular_curador' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
            'email' => [
                'required',
                'email',
                Rule::unique('usuarios', 'email')->where(function ($query) use ($object, $data) {
                    if (!empty($data['fk_usuario_id'])) {
                        $query->where('id', '!=', $data['fk_usuario_id']);
                    }
                    $query->where('status', '=','1');
                    $query->where('email', '=', $data['email']);
                }),
            ]
        ];

        return Validator::make($data, $rule, $this->messages);
    }
}
