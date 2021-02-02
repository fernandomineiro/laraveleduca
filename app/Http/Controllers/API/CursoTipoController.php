<?php

namespace App\Http\Controllers\API;

use App\CursoTipo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class CursoTipoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $curso_tipo = CursoTipo::select()->where('status', '=', 1)->get();

            return response()->json($curso_tipo, 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Não foi possível encontrar os dados solicitados'], 500);
        }
    }
}
