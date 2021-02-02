<?php

namespace App\Repositories;

use App\Estado;

class EstadoRepository extends RepositoryAbstract {
    
    public function __construct(Estado $model) {
        parent::__construct($model);
    }

    public function getAll() {
        return $this->model->all();
    }
}
