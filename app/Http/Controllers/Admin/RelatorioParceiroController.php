<?php

namespace App\Http\Controllers\Admin;

use App\Pedido;
use App\PedidoStatus;
use App\Exports\RelatorioVendasExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Helper\TaxasPagamento;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AlunosExport;
use App\Exports\RelatorioParceiroExport;

class RelatorioParceiroController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        $params = $this->processaRequest($request);

        $params['data_compra'][0] = (!empty($params['data_compra'][0])) ?  $params['data_compra'][0] . ' 00:00:00' : $params['data_compra'][0];
        $params['data_compra'][1] = (!empty($params['data_compra'][1])) ?  $params['data_compra'][1] . ' 23:59:59' : $params['data_compra'][1];

        $user_type = $this->getUserType($params);
        
        $this->arrayViewData['pedidos'] = (!empty($user_type)) ? Pedido::relatorio_parceiro($params)->paginate(20) : $this->arrayViewData['pedidos'] = array();

        foreach ($this->arrayViewData['pedidos'] as $key => &$row) {
            $row->professor_share_valor = 'R$ ' . number_format($row->professor_share_valor, 2, ',', '.');
            $row->faculdade_share_valor = 'R$ ' . number_format($row->faculdade_share_valor, 2, ',', '.');
            $row->curador_share_valor   = 'R$ ' . number_format($row->curador_share_valor, 2, ',', '.');
            $row->parceiro_share_valor  = 'R$ ' . number_format($row->parceiro_share_valor, 2, ',', '.');
        }

        if ($request->input('export') == 1){
            $pedidos_export['pedidos'] =(!empty($user_type)) ? Pedido::relatorio_parceiro($params)->paginate(10000) : array();

            $this->export($pedidos_export);
        }

        $this->arrayViewData['table'] = view('relatorio.parceiro.table_pedidos', $this->arrayViewData);

        $this->arrayViewData['pedidos_status'] = PedidoStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['pedidos_status']->prepend('Selecione', '0');
        
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['faculdades']->prepend('Selecione', '0');

        $this->arrayViewData['tipos_user'] = ['Selecionar o tipo', 'ies' => 'IES',
                                                'professor' => 'Professor', 
                                                'produtora' => 'Produtora', 
                                                'curador'   => 'Curador', 
                                                'parceiro'  => 'Parceiro'];
        
        $this->arrayViewData['tipos_item'] = CursoTipo::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['tipos_item']->prepend('Assinatura','ASSINATURA');
        $this->arrayViewData['tipos_item']->prepend('Trilha','TRILHA');
        $this->arrayViewData['tipos_item']->prepend('Evento', 'EVENTO');
        $this->arrayViewData['tipos_item']->prepend('Selecione', '0');
        
        $this->arrayViewData['data_compra'] = $params['data_compra'];
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    
    private function processaRequest($request){
        $params = [];
        $params['orderby'] = 'pedidos.id';
        $params['sort'] = 'DESC';
        
        if($request->get('orderby') && $request->get('sort')){
            $params['orderby'] = $request->get('orderby');
            $params['sort'] = $request->get('sort');
        }
        
        if( $request->has('pedido_pid') && !empty($request->get('pedido_pid')) ){
            $params['pedido_pid'] = $request->get('pedido_pid');
        }
        
        if( $request->has('pedidos_status') && !empty($request->get('pedidos_status')) ){
            $params['pedidos_status'] = $request->get('pedidos_status');
        }
        
        if( $request->has('ies') && !empty($request->get('ies')) ){
            $params['ies'] = $request->get('ies');
        }
        
        if( $request->has('nome_item') && !empty($request->get('nome_item')) ){
            $params['nome_item'] = $request->get('nome_item');
        }
        
        if( $request->has('nome_professor') && !empty($request->get('nome_professor')) ){
            $params['nome_professor'] = $request->get('nome_professor');
        }
        
        if( $request->has('nome_produtora') && !empty($request->get('nome_produtora')) ){
            $params['nome_produtora'] = $request->get('nome_produtora');
        }
        
        if( $request->has('nome_curador') && !empty($request->get('nome_curador')) ){
            $params['nome_curador'] = $request->get('nome_curador');
        }
        
        if( $request->has('tipo_item') && !empty($request->get('tipo_item')) ){
            $params['tipo_item'] = $request->get('tipo_item');
        }
        
        if( $request->has('data_compra') && !empty($request->get('data_compra')) ){
            $explode = explode("-",$request->get('data_compra'));
            $params['data_compra'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        }else{
            $params['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }
        
        if( $request->has('aluno') && !empty($request->get('aluno')) ){
            $params['aluno'] = $request->get('aluno');
        }
        
        return $params;
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

    public function export($data){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $html = view('relatorio.parceiro.table_pedidos', $data);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'parceiro_pedido' . time() . '.xls';

        // Configurações header para forçar o download
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        header ("Content-type: application/x-msexcel");
        header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
        header ("Content-Description: PHP Generated Data" );

        // Envia o conteúdo do arquivo
        echo utf8_decode($html);
        exit;
    } 
}
