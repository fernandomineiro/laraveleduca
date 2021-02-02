<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizQuestao extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'quiz_questao';
    protected $fillable = ['fk_quiz','titulo','resposta_correta', 'dissertativa', 'fk_atualizador_id','fk_criador_id','criacao','atualizacao','status'];

    public $timestamps = false;

    public $rules = [
        'fk_quiz' => 'required',
        'titulo' => 'required',
        'status' => 'required'
    ];

    public $messages = [
        'fk_quiz' => 'Quiz',
        'titulo' => 'Pergunta',
        'resposta_correta' => 'Alternativa Correta'
    ];

}

