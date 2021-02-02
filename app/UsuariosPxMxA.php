<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosPxMxA extends Model
{
    protected $table = 'usuarios_modulos_x_acoes';
    protected $fillable = ['fk_modulo_acoes_id','fk_perfil_id', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao',];

    public $timestamps = false;

    public $rules = [
        'fk_modulo_acoes_id' => 'required',
        'fk_perfil_id' => 'required'
    ];

    public $messages = [
        'fk_modulo_acoes_id' => 'Módulo - Ação',
        'fk_perfil_id' => 'Perfil'
    ];
}
