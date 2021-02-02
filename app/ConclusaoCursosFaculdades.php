<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConclusaoCursosFaculdades extends Model
{
    use Notifiable, Cachable;


    protected $table = 'conclusao_cursos_faculdades';
    public $timestamps = false;

    protected $fillable = [
        'fk_curso',
        'fk_faculdade',
        'fk_certificado',
        'nota_trabalho',
        'nota_quiz',
        'freq_minima'
    ];

    public $rules = [
        'fk_curso' => 'required',
        'fk_faculdade' => 'required',
        'fk_certificado' => 'sometimes|numeric',
        'nota_trabalho' => 'sometimes|numeric',
        'nota_quiz' => 'sometimes|numeric',
        'freq_minima' => 'sometimes|numeric'
    ];
    public $messages = [
        'fk_curso.required' => 'Curso é obrigatório',
        'fk_faculdade.required' => 'Projeto é obrigatório'
    ];

}
