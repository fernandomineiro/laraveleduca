<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Quiz extends Model
{
    use Notifiable;
    
    protected $primaryKey = 'id';
    protected $table = 'quiz';
    protected $fillable = ['fk_curso','percentual_acerto','status','fk_atualizador_id','fk_criador_id','criacao','atualizacao'];

    public $timestamps = false;

    public $rules = [
        'fk_curso' => 'required',
        'percentual_acerto' => 'required',
    ];

    public $messages = [
        'fk_curso' => 'Curso',
        'percentual_acerto' => 'Percentual de Acertos para Aprovação'
    ];
}

