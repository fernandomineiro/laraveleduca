<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cidade extends Model
{
    use Notifiable;
    
    protected $fillable = [
        'descricao_cidade',
        'fk_estado_id'
    ];

    protected $table = "cidades";
    public $timestamps = false;

    public $rules = [
        'descricao_cidade' => 'required',
        'fk_estado_id' => 'required'
    ];

    public $messages = [
        'descricao_cidade' => 'Cidade',
        'fk_estado_id' => 'Estado'
    ];

}
