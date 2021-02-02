<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoTagAluno extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'cursos_tag_aluno';
    protected $fillable = ['fk_cursos_tag', 'fk_aluno'];

    public $timestamps = false;

    public $rules = [
        'fk_cursos_tag' => 'required',
        'fk_aluno' => 'required',
    ];

    public $messages = [
        'fk_cursos_tag' => 'Curso',
        'fk_aluno' => 'Aluno'
    ];

    public function tagsAluno() {
        return $this->HasOne('\App\CursoTag', 'id', 'fk_cursos_tag');
    }
}
