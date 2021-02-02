<?php

namespace App\Http\Controllers\API;

use App\CursosMentoria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CursosMentoriaController extends Controller
{
    protected $cursos_mentoria;

    protected $cursos_mentoria_comentarios;

    public function __construct(CursosMentoria $cursos_mentoria)
    {
        $this->cursos_mentoria = $cursos_mentoria;
    }

    public function comentarios(Request $request, $curso_mentoria_id)
    {
        $this->checkFaculdade($request);

        $faculdade = $request->header('Faculdade');

        $curso_mentoria = $this->cursos_mentoria->faculdade($faculdade)->findOrFail($curso_mentoria_id);

        return response()->json([
            'items' => $curso_mentoria->comentarios
        ]);
    }

    public function createComentarios(Request $request, $curso_mentoria_id)
    {
        $this->checkFaculdade($request);

        $this->validate($request, [
            'avaliacao' => 'required|integer',
            'comentario' => 'required',
            'criador' => 'required',
//            'fk_professor' => ,
//            'fk_curso_mentoria' => $curso_mentoria_id,,
        ]);

        $faculdade = $request->header('Faculdade');

        $curso = $this->cursos_mentoria->faculdade($faculdade)->findOrFail($curso_mentoria_id);

        $comentario = $curso->comentarios()->create([
            'avaliacao' => $request->get('avaliacao'),
            'comentario' => $request->get('comentario'),
            'fk_professor' => $curso->fk_professor,
            'fk_criador_id' => $request->get('criador'),
        ]);

        return response()->json($comentario->only('avaliacao', 'comentario'));
    }

    protected function checkFaculdade(Request $request)
    {
        $faculdade = $request->header('Faculdade');

        if (!$faculdade) {
            throw new \Exception("Erro ao selecionar faculdade");
        }
    }
}
