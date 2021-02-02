<?php

namespace App;

class PerguntaResposta extends Model {

    protected $table = 'pergunta_resposta';

    protected $fillable = [
        'resposta',
        'fk_pergunta',
        'fk_atualizador_id',
        'fk_criador_id',
        'criacao',
        'atualizacao',
        'status'
    ];

    public $rules = [
        'fk_pergunta' => 'required',
        'resposta' => 'required'
    ];

    public $messages = [
        'resposta' => 'Resposta',
        'fk_pergunta' => 'Pergunta',
    ];

    public function usuario() {
        return $this->hasOne('\App\Usuario', 'id', 'fk_criador_id');
    }
}
