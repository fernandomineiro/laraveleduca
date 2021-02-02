<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $table = 'pagamento';
    protected $fillable = ['fk_pedido', 'id_pedido_gateway', 'tipo', 'taxa', 'total', 'emissor', 'parcelas', 'juros', 'data_criacao'];
    public $timestamps  = false;

    public $rules = [
        'tipo'      => 'required',
        'fk_pedido' => 'required',
        'total'     => 'required',
    ];
}
