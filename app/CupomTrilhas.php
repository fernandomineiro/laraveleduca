<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomTrilhas extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_trilha',
        'fk_faculdade',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_trilhas";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_trilha' => 'required',
    ];
}
