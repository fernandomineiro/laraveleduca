<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Parceiro extends Model
{
    protected $table = 'parceiro';

    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';
    const PERFIL_NOME = 'PARCEIRO';
    const ID_PERFIL = 19;

    protected $fillable = [
        'id',
        'wirecard_account_id',
        'razao_social',
        'fantasia',
        'cnpj',
        'mini_curriculum',
        'representante_legal',
        'status',
        'share',
        'telefone_1',
        'telefone_2',
        'telefone_3',
        'fk_usuario_id',
        'fk_endereco_id',
        'fk_conta_bancaria_id',
        'criacao',
        'atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'responsavel',
        'data_nascimento',
        'cpf',
        'titular'
    ];

    public $timestamps = true;

    public $rules = [
        'razao_social' => 'required',
        'data_nascimento' => 'required',
        'cpf' => 'required',
        'email' => 'required',
        'share' => 'required',
        'cnpj' => 'required|cnpj|unique:parceiro,id,:id,cnpj,0,status',
        'telefone_2' => 'required',
        'status' => 'required'
    ];

    public $messages = [
        'razao_social.required' => 'Razão Social é obrigatório!',
        'logradouro.required' => 'Logradouro é obrigatório!',
        'fk_estado_id.required' => 'Estado é obrigatório!',
        'fk_cidade_id.required' => 'Cidade é obrigatório!',
        'responsavel.required' => 'Titular é obrigatório!',
        'data_nascimento.required' => 'Data de nascimento do titular da parceiro/conta é obrigatório!',
        'share.required' => 'Share é um campo obrigatório!',
        'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'cpf.required' => 'CPF do titular da parceiro/conta é obrigatório!',
        'status.required' => 'Informe o status!',
        'cnpj.required' => 'CNPJ é obrigatório!',
        'cnpj.cnpj' => 'CNPJ inválido!',
        'cnpj.unique' => 'CNPJ já cadastrado!',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'titular.required' => 'Títular é obrigatório',
        'fk_banco_id.required' => 'Banco é obrigatório',
        'agencia.required' => 'Agência é obrigatório',
        'conta_corrente.required' => 'Número da Conta é obrigatório',
        'tipo_conta.required' => 'Tipo de conta Conta é obrigatório',
        'documento.required' => 'CPF/CNPJ é obrigatório',
        'email.required' => 'E-mail é obrigatório',
        'email.validation' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
        'email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
    ];

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new Parceiro)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data) {
        $object = $this;

        $rule = [
            'telefone_2' => 'required',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes',
            'email' => [
                'email',
                'required',
                Rule::unique('usuarios', 'email')->where(function ($query) use ($object, $data) {
                    if (!empty($data['fk_usuario_id'])) {
                        $query->where('id', '!=', $data['fk_usuario_id']);
                    }
                    $query->where('status', '=','1');
                    $query->where('email', '=', $data['email']);
                }),
            ],
        ];
        if (!empty($data['cnpj'])){
            array_push($rule, ['cnpj' => [
                //'cpf_cnpj',
                Rule::unique('curadores', 'cnpj')->where(function ($query) use ($object, $data) {
                    if (!empty($data['id']) && !empty($data['cnpj'])) {
                        $query->where('id', '!=', $data['id']);
                    }
                    $query->where('status', '!=', 0);
                    $query->where('cnpj', '=', $data['cnpj']);
                }),
            ]]);
        }
        return Validator::make($data, $rule, $this->messages);
    }

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
    public function getName() {
        return $this->responsavel;
    }
}
