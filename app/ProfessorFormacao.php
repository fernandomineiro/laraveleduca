<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;

class ProfessorFormacao extends Model
{
    use Notifiable, Cachable;
    
    protected $fillable = [
        'fk_professor_formacao_tipo_id',
        'instituicao',
        'curso',
        'ano_inicio',
        'ano_conclusao',
    ];

    protected $primaryKey = 'id';
    protected $table = "professor_formacao";

    public $rules = [
        'fk_professor_formacao_tipo_id' => 'required',
        'instituicao' => 'required'
    ];

    public $messages = [
        'fk_professor_formacao_tipo' => 'Tipo da Formação',
        'tipo' => 'Tipo',
        'instituicao' => 'Instituição',
        'curso' => 'Curso'        
    ];
    
    /**
     * Retorna a classe Professor associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professor()
    {
        return $this->belongsTo('App\Professor', 'fk_professor_id');
    }
    
    /**
     * Retorna a classe ProfessorFormacaoTipo associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipo()
    {
        return $this->belongsTo('App\ProfessorFormacaoTipo', 'fk_professor_formacao_tipo_id');
    }    
    

}
