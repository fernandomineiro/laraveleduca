<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoModulo extends Model
{
    use Notifiable;
    protected $table = 'cursos_modulos';
    protected $fillable = ['titulo', 'descricao', 'tipo_modulo', 'url_video', 'url_arquivo', 'carga_horaria',
        'fk_curso','fk_curso_secao' ,'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao',
        'atualizacao', 'status', 'ordem', 'horario', 'tipo_atividade', 'endereco', 'fk_quiz', 'fk_trabalho',
        'possui_nota', 'peso_media', 'criterio_nota', 'ementa', 'link_aula_ao_vivo', 'hora_aula_ao_vivo',
        'data_aula_ao_vivo', 'aula_ao_vivo', 'data_fim_aula_ao_vivo', 'hora_fim_aula_ao_vivo'
    ];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'tipo_modulo' => 'required',
        'fk_curso' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'descricao' => 'Descrição',
        'tipo_modulo' => 'Tipo',
        'url_video' => 'Url do Vídeo',
        'url_arquivo' => 'Url do Arquivo',
        'carga_horaria' => 'Carga Horária',
        'fk_curso' => 'Curso',
        'ordem' => 'ordem',
        'aula_ao_vivo' => 'Aula será ao vivo?',
        'data_aula_ao_vivo' => 'Data da Aula ao Vivo',
        'hora_aula_ao_vivo' => 'Hora da Aula ao Vivo'
    ];
}
