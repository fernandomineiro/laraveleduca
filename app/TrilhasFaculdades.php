<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TrilhasFaculdades extends Model
{
    use Notifiable, Cachable;
    
    public $timestamps = false;
    protected $table = 'trilhas_faculdades';
    protected $primaryKey = 'id';
    protected $fillable = [
        'fk_trilha',
        'fk_faculdade',
        'gratis',
    ];
}
