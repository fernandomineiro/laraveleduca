<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagamentoBradesco extends Model
{
    protected $table = 'pagamento_bradesco';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status',
        'aluno_id', 'pedido_id', 'numero', 'valor', 'ip', 'user_agent', 'token_request', 'api_id',
        'json_send', 'transferencia_token', 'transferencia_url_acesso', 'status_codigo', 'status_mensagem',
        'json_response'];

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
