<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Curador;

class CuradorController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() 
    {
        try {
            $items = Curador::select('curadores.id', 'curadores.razao_social as nome_curador')
                ->where('status', 1)
                ->get()
                ->toArray();

            return response()->json(['items' => $items]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @param $id     
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id = null)
    {
        try {
            $data = Curador::where('id', $id)->get()->toArray();

            return response()->json(['data' => $data]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
