<?php

namespace App\Services;

use App\Repositories\EstadoRepository;
use Illuminate\Support\Str;

class EstadoService {
    /** @var EstadoRepository */
    protected $stateRepository;

    /**
     * CityService constructor.
     * @param EstadoRepository $stateRepository
     */
    public function __construct(EstadoRepository $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }

    /**
     * @return array
     */
    public function getStatesForSelect() {
        return 
            $this->stateRepository->getAll()
                ->transform(function ($estado) {
                    return [
                        'id' => $estado->id,
                        'descricao_estado' => Str::title($estado->descricao_estado)
                    ];
                })->pluck('descricao_estado', 'id')
                ->toArray();
    }
}
