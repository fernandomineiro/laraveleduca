<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesEstilos extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_estilos';
    protected $fillable = ['fk_criador_id','fk_atualizador_id','criacao','atualizacao','status','descricao',
        'categoria','css_personalizado','slug','url','fk_faculdade_id'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'fk_faculdade_id' => 'required'
    ];

    public $messages = [
        'descricao' => 'Descrição',
        'fk_faculdade_id' => 'Faculdade'
    ];


}
