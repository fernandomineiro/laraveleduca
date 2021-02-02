<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ContaBancaria extends Model {

    use EducazSoftDelete;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    const SOFT_DELETE = 'status';

    protected $fillable = [
        'titular',
        'fk_banco_id',
        'agencia',
        'conta_corrente',
        'operacao',
        'documento',
        'digita_conta',
        'digita_agencia',
        'tipo_conta',
    ];

    protected $table = "conta_bancaria";
    public $timestamps = true;

    public $rules = [];
    public $messages = [];

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new ContaBancaria)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data) {
        return Validator::make($data, $this->rules, $this->messages);
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function banco()
    {
        return $this->HasOne('\App\Banco', 'id', 'fk_banco_id');
    }
}
