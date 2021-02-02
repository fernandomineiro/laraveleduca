<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    protected $table = 'banco';
    protected $fillable = [
        'titulo',
        'numero',
        'status',
        'fk_criador_id',
        'data_criacao',
        'fk_atualizador_id', 
        'data_atualizacao',
        'criacao',
        'atualizacao'
    ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'numero' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'numero' => 'Número'
    ];

}
