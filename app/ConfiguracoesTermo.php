<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesTermo extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_termo';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'slug', 'descricao', 'texto', 'fk_logotipo_id', 'url', 'fk_faculdade_id'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'slug' => 'required',
        'fk_faculdade_id' => 'required',
    ];

    public $messages = [
        'slug' => 'Slug',
        'descricao' => 'Descrição',
        'fk_faculdade_id' => 'Faculdade'
    ];
}
