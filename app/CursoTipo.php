<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoTipo extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_tipo';
    protected $fillable = ['titulo', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título'
    ];
}
