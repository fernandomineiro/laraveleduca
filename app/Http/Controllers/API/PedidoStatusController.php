<?php

namespace App\Http\Controllers\API;

use App\PedidoStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PedidoStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $pedido_status = PedidoStatus::all()->where('status', '=', 1);

            return response()->json($pedido_status, 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Não foi possível localizar os dados solicitados'], 500);
        }
    }
}
