<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Faculdade extends Model {

    use Notifiable, Cachable, EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';
    const ID_PERFIL = 15;

    protected $table = 'faculdades';
    protected $fillable = [
        'id',
        'wirecard_account_id',
        'razao_social',
        'fantasia',
        'cnpj',
        'status',
        'share',
        'telefone_1',
        'telefone_2',
        'telefone_3',
        'fk_usuario_id',
        'fk_endereco_id',
        'fk_conta_bancaria_id',
        'url',
        'responsavel',
        'cpf',
        'data_nascimento',
        'criacao',
        'atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'projeto_escolas',
    ];

    public $timestamps = true;

    public $rules = [
        'razao_social' => 'required',
        'fantasia' => 'required',
        'share' => 'required',
        'responsavel' => 'required',
        'cpf' => 'required',
        'data_nascimento' => 'required',
        'cep' => 'required',
        'logradouro' => 'required',
        'bairro' => 'required',
        'numero' => 'required',
        'fk_estado_id' => 'required',
        'fk_cidade_id' => 'required',
        'telefone_1' => 'required',
        'telefone_2' => 'required',
        'cnpj' => 'required',
        'url' => 'url'
    ];

    public $messages = [
        'razao_social.required' => 'Nome/Razão Social é obrigatório',
        'fantasia.required' => 'Nome Fantasia é obrigatório',
        'cep.required' => 'Cep é obrigatório!',
        'logradouro.required' => 'Logradouro é obrigatório!',
        'numero.required' => 'Número é obrigatório!',
        'fk_estado_id.required' => 'Estado é obrigatório!',
        'fk_cidade_id.required' => 'Cidade é obrigatório!',
        'responsavel.required' => 'Responsável pelo projeto é obrigatório!',
        'data_nascimento.required' => 'Data de nascimento do responsável é obrigatório!',
        'share.required' => 'Share é um campo obrigatório!',
        'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
        'bairro.required' => 'Bairro é um campo obrigatório!',
        'mini_curriculum.required' => 'Mini Currículo é um campo obrigatório!',
        'cpf.required' => 'CPF do titular da do responsável é obrigatório!',
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
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data)
    {
        return (new Faculdade)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data){

        $object = $this;

        return Validator::make($data, [
            'razao_social' => 'required',
            'fantasia' => 'required',
            'share' => 'required',
            'responsavel' => 'required',
//            'cpf' => 'required',
            'data_nascimento' => 'required',
            'cep' => 'required',
            'logradouro' => 'required',
            'bairro' => 'required',
            'numero' => 'required',
            'fk_estado_id' => 'required',
            'fk_cidade_id' => 'required',
            'url' => 'nullable|url',
            'password' => 'required|min:8|confirmed|sometimes',
            'password_confirmation' => 'required|min:8|sometimes'
        ], $this->messages);
    }

    public function getName()
    {
        return $this->razao_social;
    }

    /**
     * Retorna Faculdades
     *
     */
    public static function lista()
    {
        $faculdades = Faculdade::select(
			'faculdades.id',
            'faculdades.fantasia as nome_faculdade'
		)->where('faculdades.status', '1');

        return $faculdades->get();
    }

    public static function obter($idFaculdade)
    {
        $faculdade = Faculdade::select(
			'faculdades.id',
            'faculdades.fantasia as nome_faculdade'
        )
        ->where('faculdades.id', $idFaculdade);

        return $faculdade->first();
    }

    /**
     * Retorna a classe Endereço associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endereco()
    {
        return $this->HasOne('\App\Endereco', 'fk_endereco_id');
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

    public function path() {
        return !empty($this->url) ? $this->url.'#/perfil/certificados' : null;
    }
}
