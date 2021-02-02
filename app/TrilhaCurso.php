<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TrilhaCurso extends Model
{
    use Notifiable;
    
    protected $primaryKey = 'id';
    protected $table = 'trilha_curso';
    protected $fillable = ['fk_trilha','fk_curso','fk_atualizador_id','fk_criador_id','criacao','atualizacao','status'];

    public $timestamps = false;

    public $rules = [
        'fk_trilha' => 'required',
        'fk_curso' => 'required',
    ];

    public $messages = [
        'fk_trilha' => 'Trilha',
        'fk_curso' => 'Curso'
    ];

	/**
	 * Retorna todos os cursos por tipo:
	 * (online, presencial, remoto)
	 * 
	 */
    public static function lista($idTrilha)
    {
        $trilhas = TrilhaCurso::select(
			'trilha_curso.fk_curso as id',
			'cursos.titulo as nome_curso',
			'cursos.imagem as imagem',
			//'faculdades.fantasia as nome_faculdade',
            'professor.id as id_professor',
			'professor.nome as nome_professor',
			'professor.sobrenome as sobrenome_professor',
			'usuarios.foto as foto_professor',
			'professor.mini_curriculum as detalhe_professor',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'cursos.slug_curso',
			'fk_cursos_tipo as tipo'
            //'faculdades.id as id_faculdade'
        )
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'trilha_curso.fk_curso')
        ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
        //->join('faculdades', 'faculdades.id', '=', 'trilha.fk_faculdade')
        ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
        ->join('professor', 'cursos.fk_professor', '=', 'professor.id')
        ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
        ->where('trilha_curso.status', 1)
        ->where('cursos.status', 5)
        ->where('trilha_curso.fk_trilha', $idTrilha);

        return $trilhas->get();
    }
    /**
	 * Retorna todos os cursos por tipo:
	 * (online, presencial, remoto)
	 *
	 */
    public static function cursosTrilha($idTrilha)
    {
        $trilhas = TrilhaCurso::select(
			'trilha_curso.fk_curso as id',
			'cursos.titulo',
			//'faculdades.fantasia as nome_faculdade',
			'cursos.fk_cursos_tipo as tipo',
            'cursos_tipo.titulo as curso_tipo',
            'cursos.duracao_total',
            'cursos_valor.valor',
            'cursos_valor.valor_de'
            //'faculdades.id as id_faculdade'
        )
        ->join('trilha', 'trilha.id', '=', 'trilha_curso.fk_trilha')
        //->join('faculdades', 'faculdades.id', '=', 'trilha.fk_faculdade')
        ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
        ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
        ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
        ->where('trilha_curso.status', 1)
        ->where('cursos.status', 5)
        ->where('trilha_curso.fk_trilha', $idTrilha);

        return $trilhas->get();
    }
}

