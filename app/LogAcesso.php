<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAcesso extends Model
{
    protected $table = "log_acessos";
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'fk_usuario',
        'ip_acesso',
        'data_acesso',
        'user_agent_acesso',
    ];


    public $rules = [
        'fk_usuario' => 'required',
        'ip_acesso' => 'required',
        'data_acesso' => 'required',
        'user_agent_acesso' => 'required',
    ];

    public $messages = [
        'fk_usuario.required' => 'O id do usuário é obrigatório',
        'ip_acesso.required' => 'O ip é obrigatório',
        'data_acesso.required' => 'A data de acesso é obrigatória',
        'user_agent_acesso.required' => 'O agente do usuário é obrigatório',
    ];
}
