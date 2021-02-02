<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropostaQuestionario extends Model
{
    protected $fillable = ['tipo_questionario','fk_proposta','questao','ordem','status','fk_criador_id','fk_atualizador_id','data_criacao','data_atualizacao','criacao','atualizacao'];
    protected $primaryKey = 'id';
    protected $table = "propostas_questionarios";
    public $timestamps = false;

    public $rules = [
        'tipo_questionario' => 'required',
        'fk_proposta' => 'required',
        'questao' => 'required',
        // 'ordem' => 'required',
        // 'status' => 'required'
    ];

    public $messages = [
        'tipo_questionario' => 'Tipo de Questionário',
        'fk_proposta' => 'Status',
        'questao' => 'Questão',
        'local' => 'Local',
        'ordem' => 'Ordem',
        'status' => 'Status',
    ];
}
