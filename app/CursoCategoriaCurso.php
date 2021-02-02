<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoCategoriaCurso extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_categoria_curso';
    protected $fillable = ['fk_curso', 'fk_curso_categoria'];

    public $timestamps = false;

    public $rules = [
        'fk_curso' => 'required',
        'fk_curso_categoria' => 'required',
    ];

    public $messages = [
        'fk_curso' => 'Curso',
        'fk_curso_categoria' => 'Categoria'
    ];
    
    /**
     * Retorna a classe Curso associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */    
    public function curso()
    { 
        return $this->belongsTo('\App\Curso', 'fk_curso', 'id');
    }          
    
    /**
     * Retorna a classe CursoCategoria associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */    
    public function categoria()
    { 
        return $this->belongsTo('\App\CursoCategoria', 'fk_curso_categoria');
    }        
}
