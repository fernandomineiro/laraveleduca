<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizResposta extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'quiz_resposta';
    protected $fillable = ['label','descricao','fk_quiz_questao','fk_atualizador_id','fk_criador_id','criacao','atualizacao','status'];

    public $timestamps = false;

    public $rules = [
        'descricao' => 'required',
        'fk_quiz_questao' => 'required'
    ];

    public $messages = [
        'label' => 'Label (Numero/Letra)',
        'descricao' => 'Alternativa',
        'fk_quiz_questao' => 'Questão'
    ];

}

