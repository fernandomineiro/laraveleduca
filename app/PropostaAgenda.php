<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropostaAgenda extends Model
{
    protected $fillable = ['fk_proposta', 'data_aula', 'inicio', 'termino', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status'];
    protected $primaryKey = 'id';
    protected $table = "proposta_agenda";
    public $timestamps = false;

    public $rules = [
        'fk_proposta' => 'required',
        'data_aula' => 'required',
        'inicio' => 'required',
        'termino' => 'required'
    ];

    public $messages = [
        'fk_proposta' => 'Proposta',
        'data_aula' => 'Data da Aula',
        'inicio' => 'Início',
        'termino' => 'Término'
    ];
}
