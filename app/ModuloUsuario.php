<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuloUsuario extends Model
{
    protected $table = 'modulos_usuarios';
    protected $fillable = [
        'fk_modulo',
        'fk_usuario'
    ];

    public $timestamps = false;

    public $rules = [
        'fk_modulo' => 'required',
        'fk_usuario' => 'required'
    ];
}
