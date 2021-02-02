<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Gestao extends Model
{
    protected $table = 'gestao_ies';

    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    protected $fillable = [
        'id',
        'nome',
        'cpf',
        'status',
        'telefone_1',
        'telefone_2',
        'fk_usuario_id',
        'fk_endereco',
        'criacao',
        'atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'fk_diretoria_ensino'
    ];

    public $timestamps = true;

    public $rules = [
        'nome' => 'required',
        'email' => 'required',
        'cpf' => 'required|cpf|unique:gestao_ies,id,:id,cpf,0,status',
        'telefone_2' => 'required',
        'status' => 'required'
    ];

    public $messages = [
        'nome.required' => 'Razão Social é obrigatório!',
        'email.required' => 'E-mail é obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'cpf.required' => 'CPF inválido!',
        'status.required' => 'Informe o status!',
        'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required' => 'A senha é obrigatória!',
        'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
        'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'email.unique' => 'E-mail já cadastrado no sistema!',
        'email.required' => 'E-mail é obrigatório',
        'email.validation' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
        'email' => 'E-mail é inválido. Por favor não entre com caracteres especiais no seu email.',
    ];

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new Gestao)->_validate($data);
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
                    $query->where('fk_perfil', '<>','14');
                    $query->where('email', '=', $data['email']);
                }),
            ],
        ];

        return Validator::make($data, $rule, $this->messages);
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
     * @return mixed
     */
    public function getName() {
        return $this->nome;
    }

    /**
     * Retorna a classe Endereço associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endereco() {
        return $this->HasOne('\App\Endereco', 'id', 'fk_endereco_id');
    }
}
