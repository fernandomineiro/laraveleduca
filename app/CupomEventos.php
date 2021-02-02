<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomEventos extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_evento',
        'fk_faculdade',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_eventos";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_evento' => 'required',
    ];
}
