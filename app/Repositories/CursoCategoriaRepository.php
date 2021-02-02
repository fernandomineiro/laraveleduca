<?php

namespace App\Repositories;

use App\CursoCategoria;

class CursoCategoriaRepository extends RepositoryAbstract {
    
    public function __construct(CursoCategoria $model) {
        parent::__construct($model);
    }

    public function retornarCategoriasAtivas() {
        return $this->model->select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'cursos_categoria.slug_categoria',
                'cursos_categoria.status',
                'cursos_categoria.fk_faculdade',
                'cursos_categoria.icone',
                'cursos_categoria.ementa',
                'cursos_categoria.disciplina'
            )->where('status', 1)->get();
    }
}
