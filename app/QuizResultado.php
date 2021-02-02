<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizResultado extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'quiz_resultado';
    protected $fillable = ['fk_quiz','fk_usuario','qtd_acertos','qtd_erros','json_acertos','json_erros','data_criacao','fk_atualizador_id','fk_criador_id','criacao','atualizacao','status', 'solicitou_gabarito'];

    public $timestamps = false;

    public $rules = [
        'fk_quiz' => 'required',
        'fk_usuario' => 'required',
        'qtd_acertos' => 'required',
        'qtd_erros' => 'required',
    ];

    public $messages = [
        'fk_quiz' => 'Quiz',
        'fk_usuario' => 'Aluno',
        'qtd_acertos' => 'Quantidade de Acertos',
        'qtd_erros' => 'Quantidade de Erros',
        'json_acertos' => 'JSON com Acertos',
        'json_erros' => 'JSON com Erros',
        'solicitou_gabarito' => 'Solicitou acesso ao Gabarito'
    ];
}
