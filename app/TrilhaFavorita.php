<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrilhaFavorita extends Model
{
    //
    protected $table = 'trilhas_favorito';
    protected $fillable = [
        'fk_usuario',
        'fk_trilha'
    ];

    public $timestamps = false;

    public $rules = [
        'fk_usuario' => 'required',
        'fk_trilha' => 'required'
    ];
}
