<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Helper\RelatorioAlunosMatriculadosHelper;
use App\Produtora;
use App\Professor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class GraficoAlunosMatriculadosController extends Controller
{
    public function index(Request $request)
    {
        $user = JWTAuth::user();

        if (!$request->header('Faculdade')){
            return response()->json(['success' => false, 'error' => 'Faculdade inválida.']);
        }

        if (!empty($user) && isset($user->fk_perfil)){
            $filters = array_merge($request->all(), $this->getParceiro($user));
            $filters['fk_faculdade'] = $request->header('Faculdade');

            $filters['periodo'][0] = ($request->input('data_inicial')) ? $request->input('data_inicial') : '2019-01-01';
            $filters['periodo'][1] = ($request->input('data_final')) ? $request->input('data_final') : date('Y-m-d');
            $filters['agrupar_por'] = ($request->input('agrupar_por')) ? $request->input('agrupar_por') : 'semana';
        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $data = RelatorioAlunosMatriculadosHelper::graficoRealizadas($filters)->get();

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }
    

    private function getParceiro($user){
        if (!empty($user->fk_perfil)){
            switch ($user->fk_perfil) {
                case '1':
                    $parceiro = Professor::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_professor' => $parceiro->id];
                    }
                    break;
                case '4':
                    $parceiro = Curador::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_curador' => $parceiro->id];
                    }
                    break;
                case '5':
                    $parceiro = Produtora::select('id')->where('fk_usuario_id', $user->id)->first();

                    if (!empty($parceiro->id)){
                        return ['fk_produtora' => $parceiro->id];
                    }
                    break;

                # PERFIS DE GESTOR IES E FINANCEIRO IES 
                case 10:
                case 22:
                case 2:
                    return [];
                    break;
            }
        }
    }
}
