<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RelatorioAlunosMatriculadosExport;
use App\Faculdade;
use App\Helper\CertificadoHelper;
use App\Helper\RelatorioAlunosMatriculadosHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioAlunosMatriculadosController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user.logged');
        if (!$this->validateAccess($user)) return redirect()->route($this->redirecTo);

        $req = $request->all();
        $export = $request->get('export');
        
//        if(!isset($req['data_registro']) && !isset($req['data_matricula'])) {
//            $req['data_registro'] = date("d/m/Y") . ' - ' . date("d/m/Y");
//        }

        if(isset($req['data_registro']) && $req['data_registro'] != 'desativado') {
            $explode = explode('-', $req['data_registro']);
            $date_range = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d') . " 00:00:00",Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d') . ' 23:59:59'];
            $req['data_registro'] = $date_range;
        }

        if(isset($req['data_matricula']) && $req['data_matricula'] != 'desativado' ) {
            $explode = explode('-', $req['data_matricula']);
            $date_range = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d') . " 00:00:00",Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')  . ' 23:59:59'];
            $req['data_matricula'] = $date_range;
        }
        
//        dd($req);
        
        $this->arrayViewData['faculdades'] = Faculdade::lista();

        $alunos_matriculados = new RelatorioAlunosMatriculadosHelper();

        if ($export == 1){
            return Excel::download(new RelatorioAlunosMatriculadosExport($req), 'relatorio_alunos_cadastrados'. time() . '.xlsx');
        }
        
        if(isset($req['ies']) && $req['ies'] == 6) {
            $data = $alunos_matriculados->lista_alunos_matriculadosITV($req)->paginate(10);

            if($data && count($data) > 0) {
                $percentual_conclusao = new CertificadoHelper();

                $data->map(function($item) use ($percentual_conclusao) {
                    $percentual = $percentual_conclusao->percentualOnline($item->fk_usuario_id, $item->curso_id);
                    $item->percentual_conclusao = $percentual;
                    return $item;
                });
            }

        } else {
            $data = $alunos_matriculados->lista_alunos_matriculados($req)->paginate(10);

            if($data && count($data) > 0) {
                $percentual_conclusao = new CertificadoHelper();
    
                $data->map(function($item) use ($percentual_conclusao) {
                    $percentual = $percentual_conclusao->percentualOnline($item->fk_usuario, $item->curso_id);
                    $item->percentual_conclusao = $percentual;
    
                    return $item;
                });
            }  
        }

        $this->arrayViewData['data'] = $data;
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
}
