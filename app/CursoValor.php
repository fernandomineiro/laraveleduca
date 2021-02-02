<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoValor extends Model
{
    use Notifiable;
    
    protected $table = 'cursos_valor';
    protected $fillable = ['fk_curso', 'valor_de', 'valor', 'data_inicio', 'data_validade', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status'];

    public $timestamps = false;

    public $rules = [
        'valor_de' => 'required',
        'data_inicio' => 'required',
        'fk_curso' => 'required'
    ];

    public $messages = [
        'valor_de' => 'Valor',
        'data_inicio' => 'Data Inicial',
        'data_validade' => 'Data Vigência',
        'fk_curso' => 'Curso'
    ];
}
