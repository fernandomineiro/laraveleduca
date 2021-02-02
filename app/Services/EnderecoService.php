<?php

namespace App\Services;

use App\Repositories\EnderecoRepository;

class EnderecoService {

    protected $enderecoRepository;
    
    public function __construct(EnderecoRepository $enderecoRepository) {
        $this->enderecoRepository = $enderecoRepository;
    }

    public function salvar(array $dados)  {
        return $this->enderecoRepository->save($dados);
    }
}
