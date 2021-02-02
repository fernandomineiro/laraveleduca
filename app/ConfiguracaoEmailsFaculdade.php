<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracaoEmailsFaculdade extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_emails_faculdade';
    protected $fillable = [
        'fk_criador_id',
        'fk_atualizador_id',
        'criacao',
        'atualizacao',
        'status',
        'fk_faculdade_id',
        'slug',
        'conta',
        'senha',
        'smtp_server',
        'smtp_port',
        'smtp_ssl_tls',
        'nome',
        'email',
        'assinatura'
    ];

    public $timestamps = false;

    public $rules = [
        'slug' => 'required',
        'email' => 'required',
        'senha' => 'required',
        'smtp_server' => 'required',
        'fk_faculdade_id' => 'required'
    ];

    public $messages = [
        'slug' => 'Slug da conta',
        'email' => 'E-Mail de envio',
        'senha' => 'Senha da conta',
        'smtp_server' => 'Servidor SMTP',
        'fk_faculdade_id' => 'Faculdade'
    ];
}
