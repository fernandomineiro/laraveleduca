<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesHomeFooterHeader extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_home_footer_header';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'titulo',
        'categoria', 'categoria_url', 'posicao', 'altura', 'cor', 'img_background', 'css_personalizado', 'fk_faculdade_id', 'slug'
    ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'posicao' => 'required',
        'fk_faculdade_id' => 'required',
        'header' => 'required',
        'slug' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'categoria' => 'Categoria',
        'fk_faculdade_id' => 'Projeto',
        'posicao' => 'Posição',
        'slug' => 'Slug'
    ];

}
