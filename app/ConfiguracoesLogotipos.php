<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesLogotipos extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_logotipos';
    protected $fillable = ['fk_criador_id','fk_atualizador_id','criacao','atualizacao','status','descricao','url_logtipo','slug','fk_faculdade_id'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'url_logo' => 'required',
        'slug' =>  'required',
        'fk_faculdade_id' =>  'required'
    ];

    public $messages = [
        'descricao' => 'Descrição Logo',
        'url_logo' => 'URL imagem',
        'fk_faculdade_id' => 'Projeto',
        'slug' =>  'Apelido'
    ];



}
