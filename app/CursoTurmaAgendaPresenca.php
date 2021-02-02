<?php

namespace App;

class CursoTurmaAgendaPresenca extends Model
{
    protected $table = "cursos_turmas_agenda_presenca";

    public $rules = [];
    public $messages = [];
    
    protected $guarded = ['id'];    
    
    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agenda() {
        return $this->belongsTo('App\CursoTurmaAgenda', 'fk_agenda');
    }    
    
    /**
     * Retorna a classe Usuário associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aluno() {
        return $this->belongsTo('App\Usuario', 'fk_usuario');
    }    
}
