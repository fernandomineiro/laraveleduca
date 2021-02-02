<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaDisciplina extends Model
{
    //
    protected $table = 'nota_disciplina';
    protected $fillable = [
        'fk_disciplina',
        'tipo_nota',
        'nota',
        'fk_usuario'
    ];

    public $timestamps = false;
}
