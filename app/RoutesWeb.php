<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RoutesWeb extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'vm_routes_laravel';
    
}
