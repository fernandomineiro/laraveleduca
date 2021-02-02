<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;

class ProfessorFormacaoTipo extends Model
{
    use Notifiable, Cachable;
    protected $fillable = [
        'tipo',
        'status'
    ];

    protected $primaryKey = 'id';
    protected $table = "professor_formacao_tipo";

    public $rules = [
        'tipo' => 'required'
    ];

    public $messages = [
        'tipo' => 'Tipo da Formação'    
    ];    

}
