<?php


namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ViewUsuariosModulosAcoes extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'vm_usuarios_modulos_x_acoes';
}
