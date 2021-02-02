<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropostaHistoricoStatus extends Model
{
    protected $fillable = ['fk_proposta', 'fk_usuario', 'status', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao'];
    protected $primaryKey = 'id';
    protected $table = "proposta_historico_status";
    public $timestamps = false;

    public $rules = [
        'fk_proposta' => 'required',
        'fk_usuario' => 'required',
        'status' => 'required',
    ];

    public $messages = [
        'fk_proposta' => 'Proposta',
        'fk_usuario' => 'Usuário',
        'status' => 'Status',
    ];
}
