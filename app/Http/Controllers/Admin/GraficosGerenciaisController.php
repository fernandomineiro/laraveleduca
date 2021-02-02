<?php

namespace App\Http\Controllers\Admin;

use App\GraficosRelatorios;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GraficosGerenciaisController extends Controller{

    public function index(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequestVisaoGeral($request);
        
        $this->arrayViewData['dados_usuarios'] = GraficosRelatorios::graficos_usuarios($parametros)->get();
        $this->arrayViewData['dados_assinantes_ativos'] = GraficosRelatorios::graficos_assinantes_ativos($parametros)->get();
        $this->arrayViewData['dados_assinantes'] = GraficosRelatorios::graficos_assinantes($parametros);
        $this->arrayViewData['dados_assinaturas_canceladas'] = GraficosRelatorios::graficos_assinaturas_canceladas($parametros);
        $this->arrayViewData['dados_assinaturas'] = GraficosRelatorios::graficos_assinaturas($parametros);
        $this->arrayViewData['dados_assinantes_acessos'] = GraficosRelatorios::graficos_assinantes_acessos($parametros);
        $this->arrayViewData['dados_assinantes_faixa_etaria'] = GraficosRelatorios::graficos_assinantes_faixa_etaria($parametros);
        $this->arrayViewData['dados_assinantes_cidades'] = GraficosRelatorios::graficos_assinantes_cidades($parametros);

        $this->arrayViewData['data'] = $parametros['data'];
        $this->arrayViewData['group'] = ['m' => 'Mês','U' => 'Semana'];
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $parametros = $this->processaRequest($request);
        
        return (new RelatorioVendasExport($parametros))->download('invoices.xlsx');
        
    }
    
    private function processaRequestVisaoGeral($request){
        
        $parametros = [];
        
        if( $request->has('data') && !empty($request->get('data')) ){
            $explode = explode("-",$request->get('data'));
            $parametros['data'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d 00:00:00'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d 23:59:59')];
        }else{
            $parametros['data'] = [Carbon::today()->subYear(1)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('group') && !empty($request->get('group')) ){
            $parametros['group'] = $request->get('group');
        }else{
            $parametros['group'] = 'm';
        }
        
        return $parametros;
        
    }
    
    private function processaRequest($request){
        
        $parametros = [];
        $parametros['orderby'] = 'pedidos.id';
        $parametros['sort'] = 'DESC';
        
        if($request->get('orderby') && $request->get('sort')){
            $parametros['orderby'] = $request->get('orderby');
            $parametros['sort'] = $request->get('sort');
        }
        
        if( $request->has('pedido_pid') && !empty($request->get('pedido_pid')) ){
            $parametros['pedido_pid'] = $request->get('pedido_pid');
        }
        
        if( $request->has('data_compra') && !empty($request->get('data_compra')) ){
            $explode = explode("-",$request->get('data_compra'));
            $parametros['data_compra'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        }else{
            $parametros['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('aluno') && !empty($request->get('aluno')) ){
            $parametros['aluno'] = $request->get('aluno');
        }
        
        return $parametros;
        
    }
    
}
