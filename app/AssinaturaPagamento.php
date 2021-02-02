<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AssinaturaPagamento extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'assinatura_pagamento';
    protected $fillable = ['codigo_assinatura_wirecard', 'pagamento_wirecard_id', 'fk_pedido', 'tipo', 'emissor', 'data_criacao', 'status'];
    public $timestamps  = false;
}
