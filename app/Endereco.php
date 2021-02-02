<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Validator;

class Endereco extends Model {

    use EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';

    protected $fillable = [
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'fk_cidade_id',
        'fk_estado_id'
    ];

    protected $table = "endereco";
    public $timestamps = true;

    public $rules = [
        'cep' => 'required',
        'logradouro' => 'required',
        'numero' => 'required',
        'bairro',
        'fk_cidade_id' => 'required',
        'fk_estado_id' => 'required'
    ];

    public $messages = [
        'cep.required' => 'Cep é obrigatório',
        'logradouro.required' => 'Logradouro é obrigatório',
        'numero.required' => 'Número é obrigatório',
        'bairro.required' => 'Bairro é obrigatório',
        'fk_cidade_id.required' => 'Cidade é obrigatória',
        'fk_estado_id.required' => 'Estado é obrigatório'
    ];

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new Endereco)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data) {
        return Validator::make($data, $this->rules, $this->messages);
    }

    /**
     * @return HasOne
     */
    public function cidade() {
        return $this->HasOne('\App\Cidade', 'id', 'fk_cidade_id');
    }

    /**
     * @return HasOne
     */
    public function estado() {
        return $this->HasOne('\App\Estado', 'id', 'fk_estado_id');
    }
}
