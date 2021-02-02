<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoPagamento extends Model
{
    protected $fillable = ['titulo','status','fk_criador_id', 'fk_atualizador_id', 'ambiente', 'key_teste', 'token_teste', 'key_producao', 'token_producao', 'app_teste', 'app_producao', 'url_retorno', 'data_criacao','data_atualizacao','criacao','atualizacao'];
    protected $primaryKey = 'id';
    protected $table = "tipos_pagamento";
    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'status' => 'required',
    ];

    public $messages = [
        'titulo' => 'Título',
        'status' => 'Status',
    ];
}
