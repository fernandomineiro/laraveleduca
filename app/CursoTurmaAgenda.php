<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class CursoTurmaAgenda extends Model
{
    use Notifiable;
    
    protected $table = "cursos_turmas_agenda";

    public $rules = [
        'nome' => 'required',
        'hora_inicio' => 'required',
        'hora_final' => 'required',
        'data_inicio' => 'required|date|after_or_equal:today',
        'data_final' => 'sometimes|required|date|after_or_equal:data_inicio'
    ];
    public $messages = [
        'data_inicio.after_or_equal' => 'A data de início precisa ser igual ou maior que hoje.',
        'data_final.after_or_equal' => 'A data final precisa ser igual ou maior que a data de início.',
        'data_inicio.date' => 'A data de início precisa ser uma data válida'
    ];
    
    protected $guarded = ['id'];    
    
    /**
     * Retorna a classe CursoTurma associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function turma() {
        return $this->belongsTo('App\CursoTurma', 'fk_turma');
    }
    
    /**
     * Retorna a classe CursoTurmaAgendaPresenca associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presencas() {
        return $this->hasMany('App\CursoTurmaAgendaPresenca', 'fk_agenda');
    }
    
}
