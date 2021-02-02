<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomCursos extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_curso',
        'fk_faculdade',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_cursos";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_curso' => 'required',
    ];
}
