<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;

class CursoSugestao extends Model
{
    use Notifiable, Cachable;
    
    protected $fillable = [
        'objetivo',
        'profissao',
        'fk_categoria_id',
        'categoria',
        'tempo_dia',
        'tempo_prazo',
        'tipo_certificado'
    ];

    protected $table = "cursos_sugestao";

    public $rules = [
        'objetivo' => 'required',

    ];

    public $messages = [
        'objetivo' => 'Objetivo',

    ];

    
    /**
     * Retorna a classe CursoCategoria associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */    
    public function categoria()
    { 
        return $this->HasOne('\App\CursoCategoria', 'fk_curso_categoria_id');
    }     
    
}
