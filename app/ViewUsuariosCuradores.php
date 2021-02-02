<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ViewUsuariosCuradores extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'vw_usuarios_curadores';
}
