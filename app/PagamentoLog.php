<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagamentoLog extends Model
{
    protected $table    = 'pagamento_log';
    protected $fillable = [ 'fk_pedido', 'enviado', 'recebido', 'data_criacao'];
    public $timestamps  = false;
}
