<?php

namespace App\Repositories;

use App\Faculdade;
use Illuminate\Database\Eloquent\Collection;

class FaculdadeRepository extends RepositoryAbstract {
    public function __construct(Faculdade $model) {
        parent::__construct($model);
    }
}
