<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ConfiguracoesEstilosVariaveis extends Model {
    protected $table = 'configuracoes_estilos_variaveis';

    protected $fillable = [
        'fk_configuracoes_estilos_id',
        'fk_configuracoes_variaveis_id',
        'value'
    ];

    public $timestamps = false;

}
