<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repasse extends Model{    
    protected $fillable = [
        'id', 'fk_usuario', 'valor', 'fk_criador_id', 'criacao'
    ];
    
    protected $primaryKey = 'id';
    protected $table = "repasses";
    
    public $timestamps = true;
    
    const CREATED_AT = 'criacao';
    const UPDATED_AT = null;
}
