<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoAssinatura extends Model
{

    const FULL = 1;
    const Mensal = 2;
    const trilha = 3;

    protected $fillable = [
        'titulo',
        'status',
        'fk_criador_id',
        'fk_atualizador_id',
        'data_criacao',
        'data_atualizacao',
        'criacao',
        'atualizacao'
    ];

    protected $primaryKey = 'id';
    protected $table = "tipo_assinatura";
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
