<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropostaQuestionarioOpcao extends Model
{
    protected $fillable = ['fk_proposta_questionario','descricao','ordem','status','fk_criador_id','fk_atualizador_id','data_criacao','data_atualizacao','criacao','atualizacao'];
    protected $primaryKey = 'id';
    protected $table = "propostas_questionarios_opcoes";
    public $timestamps = false;

    public $rules = [
        'fk_proposta_questionario' => 'required',
        'descricao' => 'required',
        // 'ordem' => 'required',
        // 'status' => 'required'
    ];

    public $messages = [
        'fk_proposta_questionario' => 'Status',
        'descricao' => 'Questão'
    ];
}
