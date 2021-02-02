<?php

namespace App;

use App\Traits\EducazSoftDelete;
use Illuminate\Database\Eloquent\Model;

class Email extends Model {

    use EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    public $timestamps = true;

    protected $primaryKey = 'id';
    protected $table = 'email';

    protected $fillable = [
        'assunto',
        'emailfrom',
        'fk_faculdade_id',
        'fk_tipo_email',
        'status',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao'
    ];

    public $rules = [
        'assunto' => 'required',
        'emailfrom' => 'required|email',
        'fk_faculdade_id' => 'required',
        'fk_tipo_email' => 'required',
    ];

    public $messages = [
        'assunto.required' => 'Assunto é obrigatório',
        'fk_faculdade_id.required' => 'Projeto é obrigatório',
        'fk_tipo_email.required' => 'Tipo do email é obrigatório',
        'emailfrom.required' => 'E-mail é obrigatório',
        'emailfrom.email' => 'E-mail incorreto',
    ];

    /**
     * Retorna a classe Faculdade associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function faculdade() {
        return $this->HasOne('\App\Faculdade', 'id', 'fk_faculdade_id');
    }

    /**
     * Retorna a classe Faculdade associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tipo() {
        return $this->HasOne('\App\TipoEmail', 'id', 'fk_tipo_email');
    }
}
