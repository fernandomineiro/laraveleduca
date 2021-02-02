<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaMateria extends Model
{
    //
    protected $table = 'nota_materia';
    protected $fillable = [
        'fk_materia',
        'tipo_nota',
        'nota',
        'fk_usuario'
    ];

    public $timestamps = false;
}
