<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NfeLog extends Model
{
    protected $table    = 'nfe_log';
    protected $fillable = ['fk_pedido', 'fk_assinatura', 'nfse_id', 'enviado', 'recebido', 'data_criacao', 'data_atualizacao', 'error'];
    public $timestamps  = false;
}
