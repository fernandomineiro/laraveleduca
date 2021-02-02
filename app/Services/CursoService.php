<?php

namespace App\Services;

use App\Repositories\CursoRepository;
use Illuminate\Support\Str;

class CursoService
{
    /** @var CursoRepository */
    protected $cursoRepository;

    /**
     * CityService constructor.
     * @param CursoRepository $cursoRepository
     */
    public function __construct(CursoRepository $cursoRepository)
    {
        $this->cursoRepository = $cursoRepository;
    }

    /**
     * @return mixed
     */
    public function getCursosForSelect() {
        return 
            $this->cursoRepository->getAll()
                ->transform(function($curso) {
                    return [
                        'id' => $curso->id,
                        'titulo' => $curso->titulo
                    ];
                })
                ->pluck('titulo', 'id')
                ->toArray();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCurso($id) {
        return $this->cursoRepository->load($id);
    }
}
