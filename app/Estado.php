<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Estado extends Model
{
    use Notifiable;
    
    protected $fillable = [
        'descricao_estado',
        'uf_estado'
    ];

    protected $table = "estados";
    public $timestamps = false;

    public $rules = [
        'descricao_estado' => 'required',
        'uf_estado' => 'required'
    ];

    public $messages = [
        'descricao_estado' => 'Estado',
        'uf_estado' => 'UF Estado'
    ];
}
