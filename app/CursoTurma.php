<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;

class CursoTurma extends Model
{
    use Notifiable, Cachable;
    
    protected $table = "cursos_turmas";
    
    protected $guarded = ['id'];

    public $rules = [
        'nome' => 'required',
        'fk_curso' => 'required'
    ];
    public $messages = [];
    
    /**
     * Retorna a classe Curso associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function curso() {
        return $this->belongsTo('App\Curso', 'fk_curso');
    }

    /**
     * Retorna a classe CursoTurmaAgenda associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agenda() {
        return $this->hasMany('App\CursoTurmaAgenda', 'fk_turma');
    }
    
    /**
     * Retorna a classe CurstoTurmaInscricao associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inscricoes() {
        return $this->hasMany('App\CursoTurmaInscricao', 'fk_turma');
    }     

}
