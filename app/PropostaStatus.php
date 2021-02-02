<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropostaStatus extends Model
{
    protected $table = 'propostas_status';
    protected $fillable = ['titulo','fk_criador_id','fk_atualizador_id','data_criacao','data_atualizacao','criacao','atualizacao','status'];

    public $timestamps = false;

    public $rules = ['titulo' => 'required'];

    public $messages = ['titulo' => 'Título'];
}
