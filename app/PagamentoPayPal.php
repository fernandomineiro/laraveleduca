<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagamentoPayPal extends Model
{
    protected $table = 'pagamento_paypal';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'api_id',
        'aluno_id', 'pedido_id', 'numero', 'valor','envio', 'resposta'];

    public $timestamps = false;

    public $rules = [
        'aluno_id' => 'required',
        'pedido_id' => 'required',
        'numero' => 'required',
        'valor' => 'required'

    ];

    public $messages = [
        'aluno_id' => 'Código Aluno',
        'pedido_id' => 'Número Pedido',
        'numero' => 'Número transação',
        'valor' => 'Valor'
    ];
}
