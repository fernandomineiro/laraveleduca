<?php

namespace App\Services;

use App\Repositories\CidadeRepository;
use Illuminate\Support\Str;

class CidadeService
{
    /** @var CidadeRepository */
    protected $cityRepository;

    /**
     * CityService constructor.
     * @param CidadeRepository $cityRepository
     */
    public function __construct(CidadeRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function getCitiesForSelect() {
        return 
            $this->cityRepository->getAll()
                ->transform(function ($cidade) {
                    return [
                        'id' => $cidade->id,
                        'descricao_cidade' => Str::title($cidade->descricao_cidade),
                    ];
                })->pluck('descricao_cidade', 'id')
                ->toArray();
    }
}
