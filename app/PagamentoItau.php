<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PagamentoItau extends Model
{
    protected $table = 'pagamento_itau';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status',
        'aluno_id', 'pedido_id', 'numero', 'valor', 'codemp', 'chave','nome_sacado','api_id',
        'codigo_inscricao','numero_inscricao','numero_inscricao','data_vencimento',
        'url_retorno', 'obsadicional1', 'obsadicional2', 'obsadicional3','observacao','envio', 'resposta'];

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
