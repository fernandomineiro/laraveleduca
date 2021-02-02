<?php

namespace App\Http\Controllers\API;

use App\Cidade;
use App\Estado;
use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CidadeController extends Controller {

    public function index($idEstado) {
        try {
            $cidades = Cidade::where(['fk_estado_id' => $idEstado])->get()->toArray();
            return response()->json(['items' => $cidades]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getCidadeByName(Request $request) {
        try {
            $cidades = Cidade::select('*')
                ->where('fk_estado_id', $request->get('estado_id'))
                ->where('descricao_cidade', 'like', '%' . $request->get('localidade') . '%')
                ->first();
            return response()->json(['items' => $cidades]);
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
