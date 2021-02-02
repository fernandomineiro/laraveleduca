<?php

namespace App\Repositories;

use App\Professor;
use App\ViewUsuariosProfessores;

class ProfessorRepository extends RepositoryAbstract {
    
    public function __construct(Professor $model)  {
        parent::__construct($model);
    }
    
    public function listarVwProfessores() {
        return ViewUsuariosProfessores::all();
    }
}
