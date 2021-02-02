<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ConfiguracoesVariaveis extends Model {
    protected $table = 'configuracoes_variaveis';

    protected $fillable = [
        'nome',
        'status',
        'descricao',
        'tipo',
        'editavel',
        'default',
    ];

    public $timestamps = false;

}
