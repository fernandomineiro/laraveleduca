<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvisarNovasTurmas extends Model
{
    //
    protected $table = 'avisar_novas_turmas';
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    public $timestamps = true;

    protected $fillable = [
        'fk_curso',
        'fk_faculdade',
        'nome_aluno',
        'email_aluno'
    ];

    public $rules = [
        'fk_curso' => 'required',
        'fk_faculdade' => 'required',
        'nome_aluno' => 'required',
        'email_aluno' => 'required'
    ];

    public $messages = [
        'fk_curso.required' => 'Curso é obrigatório',
        'fk_faculdade.required' => 'Projeto é obrigatório',
        'nome_aluno.required' => 'O nome do aluno é obrigatório',
        'email_aluno.required' => 'O email do aluno é obrigatório',
    ];

}
