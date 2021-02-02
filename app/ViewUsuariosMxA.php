<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ViewUsuariosMxA extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'vw_modulos_x_acoes';
}
