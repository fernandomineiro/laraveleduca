<?php

namespace App\Services;

use App\Faculdade;
use App\Repositories\FaculdadeRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FaculdadeService {
    /** @var FaculdadeRepository */
    protected $faculdadeRepository;

    /**
     * CityService constructor.
     * @param FaculdadeRepository $faculdadeRepository
     */
    public function __construct(FaculdadeRepository $faculdadeRepository)
    {
        $this->faculdadeRepository = $faculdadeRepository;
    }

    /**
     * @return Collection
     */
    public function getFaculdadeComFantasiaParaSelect(){
        return $this->faculdadeRepository->getAll()
                ->pluck('fantasia', 'id')
                ->prepend('Outro', 'outro')
                ->prepend('Selecione', '');
    }
}
