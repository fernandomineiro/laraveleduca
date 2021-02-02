<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesPaginas extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_paginas';
    protected $fillable = [
        'status',
        'pagina_trilha_conhecimento',
        'fk_faculdade_id'
    ];

    public $timestamps = false;

    public $rules = [
        'fk_faculdade_id' => 'required'
    ];

    public $messages = [
        'status' => 'Status',
        'pagina_trilha_conhecimento' => 'Página de Trilha de Conhecimento',
        'fk_faculdade_id' => 'ID da Faculdade'
    ];
}
