<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuariosModulosAcoes extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'usuarios_modulos_acoes';
    protected $fillable = ['descricao', 'fk_elemento_id', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição'
    ];
}
