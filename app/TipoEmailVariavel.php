<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoEmailVariavel extends Model {

    protected $primaryKey = 'id';
    protected $table = 'tipo_email_variavel';

    protected $fillable = [
        'titulo',
        'descricao',
        'fk_tipo_email',
        'status',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao'
    ];

    public $timestamps = false;

}
