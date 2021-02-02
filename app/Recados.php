<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recados extends Model
{
    //
    public $timestamps = true;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    protected $table = 'recados';
    protected $primaryKey = 'id';
    protected $fillable = [
        'mensagem',
        'fk_professor',
        'fk_turma',
        'fk_escola',
        'fk_materia',
        'data_criacao',
        'data_atualizacao'
    ];
}
