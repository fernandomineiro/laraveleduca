<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CursosConcluidos extends Model
{
    protected $table    = 'cursos_concluidos';
    protected $fillable = ['fk_faculdade', 'fk_usuario', 'fk_curso', 'nota_trabalho', 'nota_quiz', 'frequencia', 'carga_horaria', 'criacao'];
    public $timestamps  = false;
}
