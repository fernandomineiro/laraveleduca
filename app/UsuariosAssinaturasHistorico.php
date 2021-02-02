<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuariosAssinaturasHistorico extends Model
{
    use Notifiable, Cachable;
    
    protected $fillable = ['mes', 'ano', 'total', 'tipo'];
    protected $primaryKey = 'id';
    protected $table = "usuarios_assinaturas_historico";
    public $timestamps = false;
}
