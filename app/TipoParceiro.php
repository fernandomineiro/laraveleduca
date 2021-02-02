<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoParceiro extends Model
{
    protected $table = 'tipo_parceiro';
    protected $fillable = ['descricao','status','fk_criador_id','fk_atualizador_id','data_criacao','data_atualizacao','criacao','atualizacao'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required|unique:tipo_parceiro'
    ];

    public $messages = [
        'descricao.required' => 'Descrição é obrigatória!',
        'descricao.unique' => 'Descrição já cadastrada anteriormente!'
    ];

}
