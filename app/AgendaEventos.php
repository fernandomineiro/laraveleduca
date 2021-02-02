<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AgendaEventos extends Model
{
    use Notifiable, Cachable;
    protected $table = 'agenda_evento';
    protected $fillable = ['fk_evento', 'fk_professor', 'descricao', 'data_inicio', 'data_final', 'hora_inicio', 'hora_final', 'valor',
        'fk_criador_id', 'fk_atualizador_id', 'criacao', 'atualizacao', 'status'];

    public $timestamps = false;

    public $rules = [
        'fk_evento' => 'required',
        'descricao' => 'required',
        'data_inicio' => 'required|date|after_or_equal:today',
        'data_final' => 'sometimes|required|date|after_or_equal:data_inicio',
        'hora_inicio' => 'required',
        'hora_final' => 'required',
    ];

    public function evento()
    {
        return $this->belongsToMany('App\Evento');
    }

    public $messages = [
        'descricao' => 'Descrição',
        'data_inicio' => 'Data Inicial',
        'data_final' => 'Data Final',
        'hora_inicio' => 'Hora Inicial',
        'hora_final' => 'Hora Final',
        'data_inicio.after_or_equal' => 'A data de início precisa ser igual ou maior que hoje.',
        'data_final.after_or_equal' => 'A data final deve ser maior ou igual a data de início.',
        'data_inicio.date' => 'A data de início precisa ser uma data válida'
    ];

}
