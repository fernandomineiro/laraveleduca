<?php

namespace App\Repositories;

use App\Endereco;

class EnderecoRepository extends RepositoryAbstract {

    public function __construct(Endereco $model) {
        $this->model = $model;
    }
    
    public function save(array $data) {
        $endereco = Endereco::updateOrCreate(
            [
                'id' => !empty($data['id']) ? $data['id'] : null
            ], $data);
        return  $endereco->fresh();
    }
}
