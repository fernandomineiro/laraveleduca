<?php

namespace App\Http\Controllers\API;

use App\Curso;
use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Faculdade;

class FaculdadeController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idFaculdade = false)
    {
        try {
            if ($idFaculdade) {
                $data = Faculdade::obter($idFaculdade);
                return response()->json(['data' => $data]);
            } else {
                $data = Faculdade::lista();
                return response()->json([
                    'items' => $data,
                    'count' => count($data)
                ]);
            }
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
    public function cursos($id) {
        try {
            $data = Curso::select('cursos.*')->where('status', '>',0)
                ->join('cursos_faculdades', 'cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where('cursos_faculdades.fk_faculdade', $id)
                ->get();

            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
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
