<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesRedesSociais extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_redes_sociais';
    protected $fillable = ['fk_criador_id','fk_atualizador_id','criacao','atualizacao','status','titulo','rede_url','fk_faculdade_id'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'fk_faculdade_id' => 'required',
        'rede_url' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'fk_faculdade_id' => 'Faculdade',
        'rede_url' => 'URL Rede Social'
    ];
}
