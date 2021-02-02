<?php


namespace App;


use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AgendaCursos extends Model {
    use Notifiable, Cachable;

    protected $table = 'agenda_cursos';

    protected $fillable = [
        'fk_curso',
        'titulo',
        'descricao',
        'local',
        'data',
        'fk_criador_id',
        'fk_atualizador_id',
        'criacao',
        'atualizacao',
         'status'
        ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'local' => 'required',
        'data' => 'required',
    ];

    public $messages = [
        'titulo' => 'Título',
        'local' => 'Local',
        'Data' => 'Data',
    ];


}
