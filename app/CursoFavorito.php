<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoFavorito extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_favorito';
    protected $fillable = [
        'fk_curso',
        'fk_aluno'
    ];

    public $timestamps = false;

    public $rules = [
        'fk_curso' => 'required',
        'fk_aluno' => 'required'
    ];
}
