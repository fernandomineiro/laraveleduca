<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssinaturaFaculdade extends Model 
{
    use Notifiable, Cachable;
    
    protected $fillable = [
        'fk_assinatura',
        'fk_faculdade',
        'status',
        'gratis'
    ];

    protected $primaryKey = 'id';
    protected $table = "assinatura_faculdades";
    public $timestamps = false;

}
