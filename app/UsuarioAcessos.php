<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuarioAcessos extends Model 
{
    use Notifiable, Cachable;
    
    protected $table = "usuarios_acessos";
    
    public $timestamps = false;
    
    protected $fillable = [
        'usuario_id',
        'ip',
        'origem',
        'data'
    ];
    
}
