<?php

namespace App\Http\Controllers\Admin;

use App\Faculdade;
use App\GraficosRelatorios;
use App\Exports\RelatorioAudienciaMembershipExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RelatorioAudienciaMembershipController extends Controller{
    
    public function index(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $parametros = $this->processaRequest($request);
        
        $this->arrayViewData['relatorio_tempo_medio_navegacao'] = GraficosRelatorios::relatorio_audiencia_membership($parametros)->get();
        
        $this->arrayViewData['data'] = $parametros['data'];
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['faculdades']->prepend('Selecione', '0');
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequest($request);
        
        return (new RelatorioAudienciaMembershipExport($parametros))->download('relatorio_audiencia_membership.xlsx');
        
    }
    
    private function processaRequest($request){
        
        $parametros = [];
        
        if( $request->has('data') && !empty($request->get('data')) ){
            $explode = explode("-",$request->get('data'));
            $parametros['data'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d 00:00:00'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d 23:59:59')];
        }else{
            $parametros['data'] = [Carbon::today()->subYear(1)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('ies') && !empty($request->get('ies')) ){
            $parametros['ies'] = $request->get('ies');
        }else{
            $parametros['ies'] = '';
        }
        
        return $parametros;
        
    }
    
}
