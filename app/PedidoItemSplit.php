<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoItemSplit extends Model
{
    protected $table    = 'pedidos_item_split';
    protected $fillable = ['id', 'fk_pedido', 'fk_curso', 'fk_assinatura', 'porcentagem_split_professor', 'porcentagem_split_professor_participante',
     'porcentagem_split_curador', 'porcentagem_split_parceiro', 'porcentagem_split_faculdade', 'porcentagem_split_produtora',
     'split_professor_manual', 'split_professor_participante_manual', 'split_curador_manual', 'split_parceiro_manual', 'split_faculdade_manual', 'split_produtora_manual',
     'valor_split_professor', 'valor_split_professor_participante', 'valor_split_curador', 'valor_split_faculdade', 'valor_split_produtora'];
    public $timestamps  = false;
}