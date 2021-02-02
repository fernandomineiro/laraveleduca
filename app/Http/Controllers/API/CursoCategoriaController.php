<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CursoCategoria;

use App\Professor;

class CursoCategoriaController extends Controller {

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categorias = CursoCategoria::select(
                'cursos_categoria.id',
                'cursos_categoria.titulo',
                'icone'
            );
            
            $categorias->where('cursos_categoria.status', '=', 1)->orderBy('cursos_categoria.titulo', 'asc');

            $data = $categorias->get();
            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);
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
            $categoria = CursoCategoria::find($id)->toArray();
            
            return response()->json([
                'data' => $categoria
            ]);
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
     * @param $slug_categoria
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryIDBySlug($slug_categoria)
    {
        try {
            $categoria = CursoCategoria::select('id','titulo','slug_categoria')->where('slug_categoria', $slug_categoria)->get()->toArray();
            return response()->json([
                'data' => $categoria[0]
            ]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail();
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
    
    
    /**
     * Retorna os professores da categoria
     * 
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function professores($id = null)
    {
        try {
            $professores = Professor::getProfessoresByCategoria($id);

            return response()->json([
                'items' => $professores,
                'count' => count($professores)
            ]);
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
