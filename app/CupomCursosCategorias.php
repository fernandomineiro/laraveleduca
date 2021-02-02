<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomCursosCategorias extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_categoria',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_cursos_categorias";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_categoria' => 'required',
    ];
}
