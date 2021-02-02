<?php

namespace App\Repositories;

use App\Cidade;

class CidadeRepository extends RepositoryAbstract {
    
    public function __construct(Cidade $model) {
        parent::__construct($model);
    }

    public function getAll() {
        return $this->model->all();
    }
}
