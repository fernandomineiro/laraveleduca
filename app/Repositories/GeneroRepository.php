<?php

namespace App\Repositories;

class GeneroRepository {
    public function getAll() {
        return collect([
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ]);
    }
}
