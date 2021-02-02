<?php

namespace App\Services;

use App\Repositories\ContaBancariaRepository;

class ContaBancariaService {
    /** @var ContaBancariaRepository  */
    protected $contaBancariaRepository;
    
    public function __construct(ContaBancariaRepository $contaBancariaRepository) {
        $this->contaBancariaRepository = $contaBancariaRepository;
    }

    public function salvar(array $dados)  {
        return $this->contaBancariaRepository->save($dados);
    }
}
