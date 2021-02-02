<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoHistoricoStatus extends Model
{
    protected $table = 'pedidos_historico_status';
    protected $primaryKey = 'id';
    protected $fillable = ['data_inclusao', 'fk_pedido_status', 'fk_pedido', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status', 'cliente_notificado'];

    public $timestamps = false;

    public $rules = ['fk_pedido_status' => 'required', 'fk_pedido' => 'required'];

    public $messages = ['fk_pedido_status' => 'Status do Pedido', 'fk_pedido' => 'Pedido'];
}
