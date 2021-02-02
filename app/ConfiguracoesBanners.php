<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesBanners extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_banners';
    protected $fillable = [
        'imagem',
        'status',
        'titulo',
        'slug',
        'data_hora_inicio',
        'data_hora_termino',
        'tempo_transicao_seg',
        'banner_ordem',
        'banner_default',
        'banner_largura',
        'banner_altura',
        'banner_url',
        'alt_text',
        'url_link',
        'fk_faculdade_id',
        'pagina',
        'fk_criador_id',
        'fk_atualizador_id',
        'criacao',
        'atualizacao',
        'mostrar_pesquisa',
    ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'fk_faculdade_id' => 'required',
        'slug' => 'required',
        'banner_url' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'fk_faculdade_id' => 'Projeto',
        'slug' => 'Slug'
    ];

}
