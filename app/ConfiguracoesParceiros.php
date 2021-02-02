<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfiguracoesParceiros extends Model
{
    protected $table = 'configuracoes_parceiros';
    protected $guarded = [];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'imagem' => 'required',
        'link' => 'required',
    ];

    public $messages = [
        'descricao' => 'Descrição',
        'link' => 'Link',
        'imagem' => 'Imagem',
        'fk_faculdade_id' => 'Faculdade',
    ];
}
