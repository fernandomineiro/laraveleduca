<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CursoSecao extends Model
{
    use Notifiable;


    protected $table = 'cursos_secao';
    protected $fillable = ['titulo', 'ordem', 'fk_curso', 'status', 'data_disponibilidade', 'ementa', 'roteiro', 'habilidades'];

    public $timestamps = false;

    public $rules = [
        'titulo' => 'required',
        'fk_curso' => 'required'
    ];

    public $messages = [
        'titulo' => 'Título',
        'ordem' => 'Ordem',
        'fk_curso' => 'Curso',
        'status' => 'Status',
    ];

    public static function modulosCurso($idCurso) {
        $query = CursoSecao::select(
            'cursos_secao.id',
            'cursos_secao.titulo as nome_secao',
            'cursos_modulos.id as modulo_id',
            'cursos_modulos.titulo as titulo',
            'cursos_modulos.descricao as descricao',
            'cursos_modulos.tipo_modulo',
            'cursos_modulos.url_video',
            'cursos_modulos.url_arquivo',
            'cursos_modulos.aula_ao_vivo',
            'cursos_modulos.data_aula_ao_vivo',
            'cursos_modulos.hora_aula_ao_vivo',
            'cursos_modulos.link_aula_ao_vivo',
            'cursos_modulos.data_fim_aula_ao_vivo',
            'cursos_modulos.hora_fim_aula_ao_vivo',
            'cursos_modulos.carga_horaria'
        )
            ->join('cursos_modulos', 'cursos_modulos.fk_curso_secao', '=', 'cursos_secao.id')
            ->where('cursos_secao.status', '=', 1)
            ->where('cursos_modulos.status', '=', 1)
            ->where('cursos_secao.fk_curso', '=', $idCurso)
            ->orderBy('cursos_secao.ordem', 'ASC')
            ->orderBy('cursos_modulos.ordem', 'ASC');
        return $query->get();

    }
}
