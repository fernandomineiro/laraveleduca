<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursosFaculdades extends Model
{
    use Notifiable, Cachable;


    protected $table = 'cursos_faculdades';
    public $timestamps = false;

    protected $fillable = [
        'fk_curso',
        'fk_faculdade',
        'duracao_dias',
        'disponibilidade_dias',
        'indisponivel_venda',
        'curso_gratis'
    ];

    public $rules = [
        'fk_curso' => 'required',
        'fk_faculdade' => 'required',
        'duracao_dias' => 'sometimes|numeric',
        'disponibilidade_dias' => 'sometimes|numeric'
    ];

    public $messages = [
        'fk_curso.required' => 'Curso é obrigatório',
        'fk_faculdade.required' => 'Projeto é obrigatório',
        'duracao_dias.numeric' => 'A duração em dia deve ser um número inteiro',
        'disponibilidade_dias.numeric' => 'A disponibilidade para venda em dias deve ser um número inteiro',
    ];

}
