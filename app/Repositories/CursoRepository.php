<?php

namespace App\Repositories;

use App\Curso;
use Illuminate\Support\Facades\DB;

class CursoRepository extends RepositoryAbstract {

    private $idTipo;
    private $status = 5;
    private $idFaculdade = 7;
    private $idCategoria;
    private $idCurso;

    public function __construct(Curso $model) {
        parent::__construct($model);
    }

    /**
     * @param mixed $idCurso
     */
    public function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
        return $this;
    }

    /**
     * @param mixed $idTipo
     */
    public function setIdTipo($idTipo) {
        $this->idTipo = $idTipo;
        return $this;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status) {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $idFaculdade
     */
    public function setIdFaculdade(int $idFaculdade) {
        $this->idFaculdade = $idFaculdade;
        return $this;
    }

    /**
     * @param mixed $idCategoria
     */
    public function setIdCategoria($idCategoria) {
        $this->idCategoria = $idCategoria;
        return $this;
    }

    /**
     * @param int $idUsuario
     * @param null $idEstrutura
     * @return array
     */
    public function listaITV(int $idUsuario, $idEstrutura = null) {
        $cursos = $this->model->distinct()->select(
            'cursos.id',
            'cursos.titulo as nome_curso',
            'cursos.idioma',
            'cursos.slug_curso',
            'cursos.imagem',
            'cursos_valor.valor',
            'cursos_valor.valor_de',
            'professor.nome as nome_professor',
            'professor.sobrenome as sobrenome_professor',
            'fk_cursos_tipo as tipo',
            'estrutura_curricular_conteudo.data_inicio',
            'estrutura_curricular_conteudo.ordem',
            'estrutura_curricular.tipo_liberacao',
            DB::raw('IFNULL(cursos_concluidos.fk_curso, 0) as curso_concluido')
        )
            ->leftJoin('cursos_valor', 'cursos_valor.fk_curso', '=', 'cursos.id')
            ->join('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
            ->join('cursos_categoria_curso', 'cursos_categoria_curso.fk_curso', '=', 'cursos.id')
            ->join('estrutura_curricular_conteudo', function ($join) {

                $join->on('estrutura_curricular_conteudo.fk_conteudo', '=', 'cursos.id');
                $join->on('estrutura_curricular_conteudo.fk_categoria', '=', 'cursos_categoria_curso.fk_curso_categoria');

            })
            ->join('estrutura_curricular', 'estrutura_curricular.id', '=', 'estrutura_curricular_conteudo.fk_estrutura')
            ->join('estrutura_curricular_usuario', 'estrutura_curricular_usuario.fk_estrutura', '=', 'estrutura_curricular.id')
            ->leftjoin('cursos_concluidos', function ($join) {
                $join->on('cursos_concluidos.fk_faculdade', '=', 'cursos_faculdades.fk_faculdade');
                $join->on('cursos_concluidos.fk_usuario', '=', 'estrutura_curricular_usuario.fk_usuario');
                $join->on('cursos_concluidos.fk_curso', '=', 'cursos.id');
            })
            ->where('cursos.status', $this->status)
            ->where('cursos_faculdades.fk_faculdade', $this->idFaculdade)
            ->where('estrutura_curricular_usuario.fk_usuario', $idUsuario);

        if (!empty($this->idCategoria)) {
            $cursos->where('cursos_categoria_curso.fk_curso_categoria', $this->idCategoria);
        }

        if (!empty($this->idTipo)) {
            $cursos->where('cursos.fk_cursos_tipo', $this->idTipo);
        }

        if (!empty($idEstrutura)) {
            $cursos->where('estrutura_curricular.id', $idEstrutura);
        }
        
        if (!empty($this->idCurso)) {
            $cursos->where('cursos.id', $this->idCurso);
        }

        $cursos->orderBy(DB::raw('TIMEDIFF(`estrutura_curricular_conteudo`.`data_inicio`, now()) >= 0'));
        $cursos->orderBy('estrutura_curricular_conteudo.ordem', 'asc');

        $cursos = $cursos->get()->toArray();

        return collect($cursos)->unique()->toArray();
    }
}
