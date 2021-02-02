<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CupomAssinaturas extends Model
{
    //
    protected $fillable = [
        'fk_cupom',
        'fk_assinatura',
        'fk_faculdade',
    ];

    protected $primaryKey = 'id';
    protected $table = "cupom_assinaturas";
    public $timestamps = false;

    public $rules = [
        'fk_cupom' => 'required',
        'fk_assinatura' => 'required',
    ];
}
