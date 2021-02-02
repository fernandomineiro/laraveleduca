<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Curso;

class CursoProfessorController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function online($idProfessor)
    {
        try {
            $data = Curso::cursosPorProfessor($idProfessor, 1);

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

    /**
     * @return \Illuminate\Http\JsonResponse
     */    
    public function remotos($idProfessor)
    {
        try {
            $data = Curso::cursosPorProfessor($idProfessor, 4);

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
    
    /**
     * @return \Illuminate\Http\JsonResponse
     */    
    public function presenciais($idProfessor)
    {
        try {
            $data = Curso::cursosPorProfessor($idProfessor, 2);

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
