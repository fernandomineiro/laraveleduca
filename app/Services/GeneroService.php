<?php

namespace App\Services;

use App\Repositories\GeneroRepository;

class GeneroService {
    /** @var GeneroRepository */
    protected $generoRepository;

    /**
     * CityService constructor.
     * @param GeneroRepository $generoRepository
     */
    public function __construct(GeneroRepository $generoRepository) {
        $this->generoRepository = $generoRepository;
    }
    
    public function getGeneroForSelect()  {
        return $this->generoRepository->getAll()
            ->toArray();
    }     
}
