<?php

namespace App\Http\Controllers\API;

use App\Estado;
use App\Http\Controllers\Controller;

class EstadoController extends Controller {

    public function index() {
        try {
            $estados = Estado::all()->toArray();
            return response()->json(['items' => $estados]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
