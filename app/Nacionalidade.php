<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nacionalidade extends Model
{
    protected $table = 'nacionalidade';
    protected $fillable = ['titulo', 'status', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título'
    ];
}
