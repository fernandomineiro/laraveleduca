<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TrilhasCategoria extends Model
{
    use Notifiable, Cachable;
    
    public $timestamps = false;

    protected $table = 'trilhas_categoria';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fk_trilha',
        'fk_categoria',
    ];
}
