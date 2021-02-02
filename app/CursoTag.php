<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoTag extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_tag';
    protected $fillable = ['fk_curso', 'tag'];

    public $timestamps = false;

    public $rules = [
        'fk_curso' => 'required',
        'tag' => 'required',
    ];

    public $messages = [
        'fk_curso' => 'Curso',
        'tag' => 'tag'
    ];
}
