<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Configuracao extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes';
    protected $fillable = ['dominio', 'logo', 'banner_home', 'cor_principal', 'cor_secundaria', 'fk_faculdade', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status'];

    public $timestamps = false;

    public $rules = [
        'dominio' => 'required',
        'fk_faculdade' => 'required|numeric|min:0|not_in:0'
    ];

    public $messages = [
        'dominio' => 'Título'
    ];

}
