<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizRespostaAluno extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'quiz_resposta_aluno';

    protected $fillable = ['fk_quiz','fk_usuario','fk_modulo','fk_questao','resposta_aluno','resposta_professor',
        'atualizacao','status', 'criacao', 'fk_atualizador_id', 'fk_criador_id'];

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

