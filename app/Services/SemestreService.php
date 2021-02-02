<?php


namespace App\Services;

use App\Repositories\SemestreRepository;

class SemestreService {
    /** @var SemestreRepository */
    protected $semestreRepository;

    /**
     * CityService constructor.
     * @param SemestreRepository $semestreRepository
     */
    public function __construct(SemestreRepository $semestreRepository) {
        $this->semestreRepository = $semestreRepository;
    }
    
    public function getSemestreForSelect()  {
        return $this->semestreRepository->getAll()
            ->prepend('Selecione', '')
            ->toArray();
    }     
}
