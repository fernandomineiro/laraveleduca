<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ConfiguracoesTiposCursosAtivos extends Model
{
    use Notifiable, Cachable;


    protected $table = 'configuracoes_tipos_cursos_ativos';
    protected $fillable = [
        'fk_criador_id',
        'fk_atualizador_id',
        'fk_faculdade_id',
        'status',
        'criacao',
        'atualizacao',
        'ativar_cursos_online',
        'ativar_cursos_presenciais',
        'ativar_cursos_hibridos',
        'ativar_cursos_mentoria',
        'ativar_eventos',
        'ativar_trilha_conhecimento',
        'ativar_membership',
        'ativar_biblioteca',
        'ativar_vantagens_assinantes',
        'ativar_descubra_trilhas',
        'ativar_seja_professor',
        'ativar_autenticidade_certificado',
        'ativar_faz_curso_superior',
        'ativar_faz_especializacao',
        'ativar_banner_secundario',
        'tipo_layout',
        'teaser',
        'banner_secundario',
        'banner_lateral',
        'url_quem_somos',
        'header_primario',
        'header_secundario',
        'descricao',
        'cor_banner_login',
        'banner_central',
        'texto_banner_central',
        'primeiro_texto_login',
        'segundo_texto_login',

    ];

    public $timestamps = false;

    public $rules = [
        'fk_faculdade_id' => 'required',
        'ativar_cursos_online' => 'required',
        'ativar_cursos_presenciais' => 'required',
        'ativar_cursos_hibridos' => 'required',
//        'ativar_cursos_mentoria' => 'required',
        'ativar_eventos' => 'required'
    ];

    public $messages = [
        'fk_faculdade_id' => 'Projeto',
        'ativar_cursos_online' => 'Curso Online',
        'ativar_cursos_presenciais' => 'Curso Presencial',
        'ativar_cursos_hibridos' => 'Curso Remoto',
//        'ativar_cursos_mentoria' => 'Curso Mentoria',
        'ativar_eventos' => 'Eventos'
    ];
}
