<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstruturaCurricularFaculdade extends Model {

    protected $fillable = [
        'fk_estrutura',
        'fk_faculdade',
        'status',
    ];

    protected $primaryKey = 'id';
    protected $table = "estrutura_curricular_faculdades";
    public $timestamps = false;

}
