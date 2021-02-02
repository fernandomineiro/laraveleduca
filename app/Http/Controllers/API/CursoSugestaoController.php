<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\CursoSugestao;


class CursoSugestaoController extends Controller
{
  
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() 
    {
        try {
            $sugestoes = CursoSugestao::all();

            return response()->json(['items' => $sugestoes ? $sugestoes->toArray() : []]);
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
     * @param $id     
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id = null)
    {
        try {
            $sugestao = CursoSugestao::find($id);

            return response()->json(['data' => $sugestao]);
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
     * Cria uma sugestão
     * 
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
       try {
           
            $sugestao = new CursoSugestao($request->all());


            $validator = Validator::make($sugestao->toArray(), $sugestao->rules, $sugestao->messages);
            
            if ($validator->fails()) {
                throw new \InvalidArgumentException();
            } 

            $sugestao->save();  

            return response()->json([
                'success' => true,
                'data' => CursoSugestao::find($sugestao->id)->toArray()
            ]);
            
        } catch (\InvalidArgumentException $e){
           $sendMail = new EducazMail(7);
           $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'messages' => $validator->messages(),
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
           $sendMail = new EducazMail(7);
           $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
        }
        
    }
    
   


}
