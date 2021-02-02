<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CursosTrabalhosUsuarios extends Model {

    protected $table = 'cursos_trabalhos_usuario';
    protected $fillable = ['fk_usuario', 'fk_cursos_trabalhos', 'nota', 'downloadPath', 'filename', 'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status'];

    public $timestamps = true;
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $rules = [
        'fk_usuario' => 'required',
        'fk_cursos_trabalhos' => 'required',
        'status' => 'required',
        'downloadPath' => 'required',
    ];

    public $messages = [
        'fk_usuario' => 'Usuário',
        'fk_cursos_trabalhos' => 'Trabalho',
        'status' => 'Status',
        'downloadPath' => 'Arquivo',
    ];

    public function getNomeArquivoAttribute($value) 
    {
        if (!is_null($value)) {
            return explode('/tutoria/trabalhos/', $value)[1];
        }
    }

    public function curso() {
        return $this->belongsTo('App\CursosTrabalhos', 'fk_cursos_trabalhos', 'id');
    }

    public function aluno() {
        return $this->belongsTo('App\Usuario', 'fk_usuario', 'id');
    }

}
