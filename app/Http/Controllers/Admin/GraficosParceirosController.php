<?php

namespace App\Http\Controllers\Admin;

use App\GraficosRelatorios;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Professor;
use App\Produtora;
use App\Curador;
use App\Parceiro;
use App\Faculdade;

class GraficosParceirosController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
    
        $parametros = $this->processaRequestVisaoGeral($request);

        $this->arrayViewData['dados_pedidos'] = array();
        
        if ($request->get('tipo_user')){
            $this->arrayViewData['dados_pedidos'] = GraficosRelatorios::graficos_parceiros($parametros)->get();
        }

        foreach ($this->arrayViewData['dados_pedidos'] as $key => &$row) {
            $row->valor_legenda = 'R$ ' . number_format($row->valor, 2);
            
            $row->valor = ($row->valor > 0 ) ? $row->valor : 0;
        }

        $this->arrayViewData['lista_ies'] = $this->getListFaculdades();
        $this->arrayViewData['lista_professor'] = $this->getListProfessores();
        $this->arrayViewData['lista_curador'] = $this->getListCuradores();
        $this->arrayViewData['lista_produtora'] = $this->getListProdutoras();
        $this->arrayViewData['lista_parceiro'] = $this->getListParceiros();

        $this->arrayViewData['tipos_user'] = ['Selecionar o tipo', 'ies' => 'IES',
                                              'professor' => 'Professor', 
                                              'produtora' => 'Produtora', 
                                              'curador'   => 'Curador', 
                                              'parceiro'  => 'Parceiro'];


        $this->arrayViewData['data'] = $parametros['data'];

        return view('graficos.parceiros.lista', $this->arrayViewData);
    }

    private function getUserType($params){
        $types = ['nome_professor', 'nome_produtora', 'nome_curador', 'nome_parceiro', 'ies'];

        $type = '';
        foreach ($params as $key => $param) {
            if (in_array($key, $types)){
                $type = str_replace('nome_', '', $key);

                break;
            }
        }

        return $type;
    }
    
    private function processaRequestVisaoGeral($request){
        $parametros = [];

        if( $request->has('data') && !empty($request->get('data')) ){
            $explode = explode("-",$request->get('data'));
            $parametros['data'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d 00:00:00'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d 23:59:59')];
        }else{
            $parametros['data'] = [Carbon::today()->subYear(1)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }

        if (!empty($request->get('tipo_user'))){
            $parametros['tipo'] = $request->get('tipo_user');

            $param = 'fk_' . $parametros['tipo'];
            $parametros['fk_' . $parametros['tipo']] = $request->get($param);
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

    private function getListFaculdades(){
        $faculdades = Faculdade::select("id", "fantasia")->get();

        $list = array();
        foreach($faculdades as $faculdade) {
            $list[$faculdade->id] = $faculdade->fantasia;
        }

        return $list;
    }

    private function getListProfessores(){
        $professores = Professor::select(DB::raw("CONCAT(professor.nome, ' ', professor.sobrenome) AS nome"), 'professor.id')->get();

        $list = array();
        foreach($professores as $professor) {
            $list[$professor->id] = $professor->nome;
        }

        return $list;
    }

    private function getListCuradores(){
        $curadores = Curador::select("curadores.titular_curador", "curadores.nome_fantasia", 'curadores.id')->get();

        $list = array();
        foreach($curadores as $curador) {
            $list[$curador->id] = (!empty($curador->nome_fantasia)) ? $curador->nome_fantasia : $curador->titular_curador;
        }

        return $list;
    }

    private function getListProdutoras(){
        $produtoras = Produtora::select("produtora.representante_legal", "produtora.fantasia", 'produtora.id')->get();

        $list = array();
        foreach($produtoras as $produtora) {
            $list[$produtora->id] = (!empty($produtora->fantasia)) ? $produtora->fantasia : $produtora->representante_legal;
        }

        return $list;
    }

    
    private function getListParceiros(){
        $parceiros = Parceiro::select("parceiro.titular", "parceiro.fantasia", 'parceiro.id')->get();

        $list = array();
        foreach($parceiros as $parceiro) {
            $list[$parceiro->id] = (!empty($parceiro->fantasia)) ? $parceiro->fantasia : $parceiro->titular;
        }

        return $list;
    }
}