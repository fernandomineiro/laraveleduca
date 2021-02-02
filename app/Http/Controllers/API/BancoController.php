<?php

namespace App\Http\Controllers\API;

use App\Banco;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;

class BancoController extends Controller {


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() {
        try {
            $alunos = Banco::where('status', 1)->get()->toArray();
            return response()->json(['items' => $alunos]);
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
