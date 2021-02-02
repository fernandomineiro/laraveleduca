<?php

namespace App\Helper;

use App\CursosConcluidos;
use App\QuizResultado;

class CursosConcluidosHelper {
    static function create($data){

        $quiz = QuizResultado::select('quiz_resultado.qtd_acertos', 'quiz_resultado.qtd_erros')
            ->join('quiz', 'quiz.id', '=', 'quiz_resultado.fk_quiz')
            ->where('quiz_resultado.fk_usuario', $data['fk_usuario'])
            ->where('quiz.fk_curso', $data['fk_curso'])
            ->first();

        $data['nota_quiz'] = 0;
        if (isset($quiz->qtd_acertos)){
            $total_questoes = $quiz->qtd_acertos + $quiz->qtd_erros;
            $pontuacao_por_questao = 100 / ($quiz->qtd_acertos + $quiz->qtd_erros) ;

            $data['nota_quiz'] = number_format(($pontuacao_por_questao * $quiz->qtd_acertos) / 10, 2);
        }

        $cursos_concluidos = CursosConcluidos::where(['fk_faculdade' => $data['fk_faculdade'], 'fk_usuario' => $data['fk_usuario'], 'fk_curso' => $data['fk_curso']])->first();

        if (empty($cursos_concluidos->id)){
            CursosConcluidos::create($data);
        }
    }

    static function getStatusDeConclusao($fk_faculdade, $fk_usuario, $fk_curso){
        $cursos_concluidos = CursosConcluidos::where(['fk_faculdade' => $fk_faculdade, 'fk_usuario' => $fk_usuario, 'fk_curso' => $fk_curso])->first();

        if (isset($cursos_concluidos->id)){
            return 1;
        } else {
            return 0;
        }
    }
}
