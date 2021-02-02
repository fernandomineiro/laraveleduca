<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoTotal extends Model
{
    protected $table = 'pedidos_total';
    protected $fillable = ['fk_pedido', 'porcentagem_iss', 'porcentagem_pis_cofins', 'porcentagem_irpj_csll', 'valor_taxa_boleto', 'valor_taxa_processamento', 'valor_total', 'valor_desconto'];
    public $timestamps  = false;
}
