<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuarioPerfilModulosAcoes extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'usuarios_perfil_x_modulos_acoes';
    protected $fillable = ['fk_modulo_acoes_id', 'fk_perfil_id', 'status', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao',];

    public $timestamps = false;

    public $rules = [
        'fk_modulo_acoes_id' => 'required',
        'fk_perfil_id' => 'required'
    ];

    public $messages = [
        'fk_modulo_acoes_id' => 'Módulo e Ação',
        'fk_perfil_id' => 'Perfil'
    ];
}
