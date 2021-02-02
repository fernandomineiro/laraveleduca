<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CursosTrabalhos extends Model {

    protected $table = 'cursos_trabalhos';
    protected $fillable = ['titulo', 'fk_cursos', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status', 'fk_cursos_modulo', 'data_entrega'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'fk_cursos' => 'required',
        'status' => 'required',
    ];

    public $messages = [
        'titulo' => 'Título',
        'fk_cursos' => 'Curso',
        'status' => 'Status',
    ];

    public function grade() {
        return $this->hasMany('App\CursosTrabalhosUsuarios', 'fk_cursos_trabalhos', 'id');
    }

}
