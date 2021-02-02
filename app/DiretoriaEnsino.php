<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 01/04/2020
 * Time: 20:04
 */

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Notifications\Notifiable;

class DiretoriaEnsino extends Model {

    use Notifiable;
    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    protected $table = 'diretoria_ensino';
    protected $fillable = [
        'id',
        'nome',
        'status',
        'criacao',
        'atualizacao',
        'fk_criador_id',
        'fk_atualizador_id',
        'fk_faculdade',
    ];

    public $timestamps = true;
    public $rules = ['nome' => 'required',];
    public $messages = ['nome.required' => 'Nome é obrigatório',];

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validade(array $data) {
        return (new DiretoriaEnsino)->_validate($data);
    }

    /**
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function _validate(array $data){
        return Validator::make($data, [ 'nome' => 'required', ], $this->messages);
    }
}