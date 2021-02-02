<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesSac extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_sac';
    protected $fillable = ['fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'descricao', 'slug',
        'remetente', 'telefone_1', 'telefone_2', 'telefone_3', 'email', 'url_sac', 'skype', 'hangouts', 'whatsapp', 'telegram', 'fk_faculdade_id'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'slug' => 'required',
        'fk_faculdade_id' => 'required',
        'rede_url' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição',
        'slug' => 'Slug',
        'fk_faculdade_id' => 'Projeto',
        'rede_url' => 'URL Rede Social'
    ];
}
