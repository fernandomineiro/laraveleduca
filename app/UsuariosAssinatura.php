<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @author maiko
 * @desc Classe duplicada...deleter após validar que realmente não é usada (a tabela abaixo não existe)
 *
 */
class UsuariosAssinatura extends Model{

    protected $fillable = [
        'id', 'fk_usuario', 'fk_assinatura', 'status'
    ];

    protected $primaryKey = 'id';
    protected $table = "usuario_assinatura";

    public $timestamps = false;

}
