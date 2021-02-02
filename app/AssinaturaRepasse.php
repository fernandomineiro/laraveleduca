<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssinaturaRepasse extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'assinatura_repasse';
    protected $fillable = ['fk_faculdade', 'fk_assinatura', 'total_arrecadado', 'valor_view', 'total_views',
     'total_parceiros', 'total_assinantes', 'mes', 'ano', 'atualizacao'];
    public $timestamps  = false;
}
