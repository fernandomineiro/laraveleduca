<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssinaturaRepasseParceiro extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'assinatura_repasse_parceiro';
    protected $fillable = ['fk_assinatura_repasse', 'fk_usuario', 'fk_curso', 'tipo_usuario', 'total_views', 'atualizacao', 'percentual_repasse'];
    public $timestamps  = false;
}
