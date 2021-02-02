<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaAtividade extends Model
{
    //
    protected $table = 'nota_atividade';
    protected $fillable = [
        'fk_modulo',
        'tipo_nota',
        'nota',
        'fk_usuario'
    ];

    public $timestamps = false;
}
