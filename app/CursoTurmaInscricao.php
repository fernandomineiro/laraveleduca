<?php

namespace App;

class CursoTurmaInscricao extends Model
{
    protected $table = "cursos_turmas_inscricao";
    
    protected $guarded = ['id'];

    public $rules = [
        'fk_usuario' => 'required',
        'fk_curso' => 'required',
        'status' => 'required',
    ];

    public $messages = [
        'fk_usuario' => 'Usuário',
        'fk_curso' => 'Curso',
        'percentual_completo' => 'Percentual Completo',                
        'status' => 'Status',
    ];
    
    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function turma() {
        return $this->belongsTo('App\CursoTurma', 'fk_turma');
    }
    
    /**
     * Retorna a classe usuário
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aluno() {
        return $this->belongsTo('App\Usuario', 'fk_usuario');
    }      
}