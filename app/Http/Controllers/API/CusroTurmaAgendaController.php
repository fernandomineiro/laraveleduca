<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Professor;
use App\CursoTurmaAgenda;

class CusroTurmaAgendaController extends Controller
{ 
    public function __construct() {
        
        parent::__construct();    

    }
      
    
    /**
     * Retorna os cursos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function agenda(Request $request)
    {
       try {
           
            $start = $request->date_start;
            $end = $request->date_end;
            
            if(!isset($start) or !isset($end)) {
                throw new \Exception('date_start ou date_end não informados');
            }
           
            $lista = [];

            $agendas = CursoTurmaAgenda::where('data', '>=', $start)
                    ->where('data', '<=', $end)
                    ->get();

            foreach($agendas as $agenda) {
             
                $lista[] = [
                    "day" => (new \DateTime($agenda->data))->format('d'),
                    "month" => (new \DateTime($agenda->data))->format('m'),
                    "year" => (new \DateTime($agenda->data))->format('Y'),
                    "title" => $agenda->turma->curso->titulo,
                    "agenda_nome" => $agenda->nome,
                    "agenda_descricao" => $agenda->descricao,
                    "date" => $agenda->turma->data,
                    "local" => $agenda->turma->curso->endereco_presencial
                ];
                
            }           

            return response()->json([
                        'items' => $lista,
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
