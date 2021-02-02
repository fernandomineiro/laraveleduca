<?php

namespace App;

use App\Traits\EducazSoftDelete;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoAvaliacao extends Model {

    use Notifiable, Cachable, EducazSoftDelete;

    const CREATED_AT = 'criacao';
    const UPDATED_AT = 'atualizacao';
    const SOFT_DELETE = 'status';

    public $timestamps = true;

    protected $table = 'cursos_avaliacao';

    protected $fillable = [
        'qtd_estrelas',
        'descricao',
        'status',
        'fk_curso',
        'fk_aluno'
    ];

    public $rules = [
        'descricao' => 'required',
        'fk_curso' => 'required',
        'qtd_estrelas' => 'required',
    ];

    public $messages = [
        'descricao.required' => 'O campo descrição é obrigatório.',
        'status.required' => 'O campo Status é obrigatório.',
        'fk_aluno.required' => 'Aluno',
        'fk_curso.required' => 'O campo Curso é obrigatório.',
        'qtd_estrelas.required' => 'O campo Qtd de Estrelas é obrigatório.'
    ];
}
