<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuariosModulosElementos extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'usuarios_modulos_elementos';
    protected $fillable = ['descricao', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição'
    ];
}
