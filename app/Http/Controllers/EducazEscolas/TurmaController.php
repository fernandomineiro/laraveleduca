<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 07/04/2020
 * Time: 18:07
 */

namespace App\Http\Controllers\EducazEscolas;

use App\Http\Controllers\Controller;
use App\EstruturaCurricular;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TurmaController extends Controller {

    public function __construct()  {
        parent::__construct();
    }

    public function show($idTurma, Request $request) {
        try {

            $turma = EstruturaCurricular::select(
                                'estrutura_curricular.id',
                                'estrutura_curricular.titulo',
                                'estrutura_curricular.fk_escola',
                                'estrutura_curricular.slug',
                                'estrutura_curricular.fk_orientador',
                                'usuarios.id as id_orientador',
                                'usuarios.nome as nome_orientador'
                            )
                                ->leftjoin('usuarios', 'usuarios.id', 'estrutura_curricular.fk_orientador')
                                ->where('estrutura_curricular.id', $idTurma)
                                ->first();

            return response()->json($turma);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param $idEscola
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($idTurma, Request $request) {

        try {

            /** @var EstruturaCurricular $turma */
            $turma = EstruturaCurricular::findOrFail($idTurma);
            $turma->update(
                array_merge($request->all(), ['slug' => Str::slug($request->get('titulo'), '-')])
            );

            return $this->show($turma->id, $request);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {

        try {

            /** @var EstruturaCurricular $turma */
            $turma = EstruturaCurricular::create(
                array_merge($request->all(), [
                    'slug' => Str::slug($request->get('titulo'), '-'),
                    'status' => 1
                ])
            );

            return $this->show($turma->id, $request);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ]);
        }

    }

}