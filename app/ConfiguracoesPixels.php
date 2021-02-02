<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesPixels extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_pixel';
    protected $guarded = [];

    public $timestamps = false;

    public $rules = [
        'pixel' => 'required',
    ];

    public $messages = [
        'description' => 'Descrição',
        'tipo_pixel' => 'Type',
        'pixel' => 'Pixel',
        'status' => 'Status'
    ];
}
