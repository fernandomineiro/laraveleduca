<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssinaturaPagamentoLog extends Model
{
    protected $table    = 'assinatura_pagamento_log';
    protected $fillable = [ 'fk_pedido', 'enviado', 'recebido', 'data_criacao'];
    public $timestamps  = false;
}
