<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use App\ViewUsuarios;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Gestor;
use Tymon\JWTAuth\Facades\JWTAuth;

class GestorController extends Controller
{

    public function __construct()
    {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id = null)
    {
        try {
            $gestor = Gestor::where('id', $id)->with('usuario')->get()->toArray();

            return response()->json(['data' => $gestor]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail();
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function salvarGestor(Request $request)
    {
        $loggedUser = JWTAuth::user();

        try {

            $obj = Gestor::where('fk_usuario_id', $loggedUser->id)->first();
            $data = $request->all();

            $obj->fill($data);
            $obj->save();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail();
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
