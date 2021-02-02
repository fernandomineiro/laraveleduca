<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoStatus extends Model
{

    const AGUARDANDO_PAGAMENTO = 1;
    const PAGO = 2;
    const CANCELADO = 3;
    const PAGAMENTO_NAO_APROVADO = 4;
    const PAGAMENTO_EM_ANALISE = 5;

    protected $table = 'pedidos_status';
    protected $primaryKey = 'id';
    protected $fillable = ['titulo', 'cor', 'status', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = ['titulo' => 'required', 'cor' => 'required', 'status' => 'required'];

    public $messages = ['titulo' => 'Título', 'cor' => 'Cor', 'status' => 'Status'];

}
