<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuariosModulos extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'usuarios_modulos';
    protected $fillable = ['descricao', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'route_name', 'route_uri', 'view_caminho', 'controller','fk_menu_id'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição'
    ];
}
