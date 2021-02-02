<?php

namespace App\Repositories;

use App\ContaBancaria;

class ContaBancariaRepository extends RepositoryAbstract {

    public function __construct(ContaBancaria $model) {
        $this->model = $model;
    }
    
    public function save(array $data) {
        $contaBancaria = ContaBancaria::updateOrCreate(
            [
                'id' => !empty($data['id']) ? $data['id'] : null
            ], $data);
        return  $contaBancaria->fresh();
    }
}
