<?php

namespace App\Http\Controllers\Admin;

use App\Curador;
use App\Faculdade;
use App\Helper\RelatorioAlunosMatriculadosHelper;
use App\Produtora;
use App\Professor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class GraficoAlunosMatriculadosController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $data_inicial = '';
        $data_final = '';
        $user = Session::get('user.logged');

        $this->arrayViewData['faculdades'] = Faculdade::lista();

        if (!empty($user) && isset($user->fk_perfil)){

            $filters = array_merge($request->all(), $this->getParceiro($user));

            if(isset($request->faculdade)) {
                $filters['fk_faculdade'] = $request->faculdade;
            }

            if(isset($request['data_range'])) {
                $explode = explode('-', $request['data_range']);
                $date_range = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d') . ' 00:00:00',Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d') . ' 23:59:59'];

                $data_inicial = $date_range[0];
                $data_final = $date_range[1];

                $filters['periodo'] = $date_range;
            } else {
                $filters['periodo'] = ['2019-01-01', date('Y-m-d')];
                $data_inicial = '2019-01-01';
                $data_final = date('Y-m-d');
            }

            if($request->agrupar_por == 'semana') {
                $filters['agrupar_por'] = 'semana';
            } else if($request->agrupar_por == 'mes') {
                $filters['agrupar_por'] = 'mes';
            } else if($request->agrupar_por == 'ano') {
                $filters['agrupar_por'] = 'ano';
            } else {
                $filters['agrupar_por'] = 'semana';
            }

        } else {
            return response()->json(['success' => false, 'error' => 'Usuário não encontrado.']);
        }

        $matriculas = RelatorioAlunosMatriculadosHelper::graficoRealizadas($filters)->get();

        if(count($matriculas)>0) {
            foreach ($matriculas as $k=>$m) {
                if($filters['agrupar_por'] == 'semana') {
                    $matriculas[$k]['quantidade'] = $m->semana;
                } else if($filters['agrupar_por'] == 'mes') {
                    $matriculas[$k]['quantidade'] = $m->mes;
                } else if($filters['agrupar_por'] == 'ano') {
                    $matriculas[$k]['quantidade'] = $m->ano;
                }
            }
        }
        $this->arrayViewData['matriculas'] = $matriculas;
        $this->arrayViewData['data_inicial'] = $data_inicial;
        $this->arrayViewData['data_final'] = $data_final;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
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
                case 20:
                    return [];
                    break;
            }
        }
    }
}
