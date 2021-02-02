<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomAlunos extends Model
{
    protected $fillable = [
        'fk_cupom',
        'fk_aluno',
        'fk_faculdade',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_alunos";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_aluno' => 'required',
    ];
}
