<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosAssinaturasAtivas extends Model
{
    protected $fillable = ['id', 'mes', 'ano', 'total', 'tipo'];
    protected $primaryKey = 'id';
    protected $table = "usuarios_assinaturas_historico";
    public $timestamps = false;
}
