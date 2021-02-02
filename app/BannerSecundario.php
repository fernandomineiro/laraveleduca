<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class BannerSecundario extends Model
{
    use Notifiable, Cachable;


    protected $table = 'banner_secundario';
    protected $fillable = [
        'status',
        'titulo',
        'banner_url',
        'alt_text',
        'url_link',
        'tipo_banner',
        'texto',
        'fk_faculdade_id',
        'fk_criador_id',
        'fk_atualizador_id',
        'criacao',
        'atualizacao',
        'codigo_vimeo_1',
        'codigo_vimeo_2',
        'codigo_vimeo_3',

    ];

    public $timestamps = false;

    public $messages = [
        'titulo' => 'Título',
        'fk_faculdade_id' => 'Projeto',
        'slug' => 'Slug'
    ];

    public $rules = [
        'titulo' => 'required',
        'fk_faculdade_id' => 'required',
        'slug' => 'required',
        'banner_url' => 'required'
    ];

    public function faculdade() {
        return $this->hasOne('App\Faculdade', 'id', 'fk_faculdade_id');
    }
}
