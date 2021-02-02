<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosMxA extends Model
{
    protected $table = 'usuarios_modulos_x_acoes';
    protected $fillable = ['fk_modulo_id', 'fk_acao_id', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao','parametro'];

    public $timestamps = false;

    public $rules = [
        'fk_modulo_id' => 'required',
        'fk_acao_id' => 'required'
    ];

    public $messages = [
        'fk_modulo_id' => 'Módulo',
        'fk_acao_id' => 'Ação'
    ];
}
