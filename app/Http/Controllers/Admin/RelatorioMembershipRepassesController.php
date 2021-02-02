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
use App\UsuarioAssinatura;
use App\Assinatura;
use App\AssinaturaRepasse;
use DB;

class RelatorioMembershipRepassesController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $params = $this->processaRequest($request);

        $this->arrayViewData['repasses'] = Assinatura::relatorio_repasses_assinaturas($params)->paginate(20);

        if ($request->input('export') == 1){
            $data_export = Assinatura::relatorio_repasses_assinaturas($params)->get();

            $this->export(['repasses' => $data_export], 'relatorio.membership.table_repasses');
        }

        $this->getFiltros(); 
        $this->arrayViewData['group_by'] = $params['group_by'];

        $this->arrayViewData['grupos'] = ['0' => 'Selecione', 'plano' => 'Plano', 'ies' => 'IES', 'parceiro' => 'Parceiro', 'tipo' => 'Tipo'];

        $this->arrayViewData['table'] = view('relatorio.membership.table_repasses', $this->arrayViewData);
        $this->arrayViewData['data_registro'] = $params['data_registro'];

        return view('relatorio.membership.repasses', $this->arrayViewData);
    }

    private function processaRequest($request){
        $params = [];
        $params['orderby'] = 'pedidos.id';
        $params['sort'] = 'DESC';

        if ($request->get('orderby') && $request->get('sort')){
            $params['orderby'] = $request->get('orderby');
            $params['sort'] = $request->get('sort');
        }

        if (!empty($request->get('ies')) ){
            $params['ies'] = $request->get('ies');
        }

        if (!empty($request->get('mes'))){
            $params['mes'] = $request->get('mes');
        } else {
            $params['mes'] = date("m");
        }

        if (!empty($request->get('ano'))){
            $params['ano'] = $request->get('ano');
        } else {
            $params['ano'] = date("Y");
        }

        if (!empty($request->get('plano'))){
            $params['plano'] = $request->get('plano');
        }

        if (!empty($request->get('group_by'))){
            $params['group_by'] = $request->get('group_by');
        } else {
            $params['group_by'] = '';
        }

        if (!empty($request->get('data_registro')) ){
            $explode = explode("-",$request->get('data_registro'));
            $params['data_registro'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        } else {
            $params['data_registro'] = [Carbon::today()->subDay(30)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
        }

        if(!empty($request->get('aluno')) ){
            $params['aluno'] = $request->get('aluno');
        }

        return $params;
    }

    private function getFiltros(){
        $this->arrayViewData['data_atualizacao'] = $this->getUltimaAtualizacao();

        $this->arrayViewData['faculdades'] = Faculdade::orderBy('fantasia', 'ASC')->pluck('razao_social', 'id');
        $this->arrayViewData['faculdades']->prepend('Selecione', '0');

        $this->arrayViewData['meses'] = ['01','02','03','04','05','06','07','08','09','10','11','12'];

        foreach ($this->arrayViewData['meses'] as $key => $value) {
            $this->arrayViewData['meses'][$value] = $value;
            unset($this->arrayViewData['meses'][$key]);
        }

        $this->arrayViewData['anos'] = range(2019, date('Y'));

        foreach ($this->arrayViewData['anos'] as $key => $value) {
            $this->arrayViewData['anos'][$value] = $value;
            unset($this->arrayViewData['anos'][$key]);
        }
        
        $this->arrayViewData['planos'] = Assinatura::all()->where('status', 1)->pluck('titulo', 'id');
        $this->arrayViewData['planos']->prepend('Selecione', '0');

        return $this->arrayViewData;
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

    private function getUltimaAtualizacao(){
        $data = AssinaturaRepasse::orderBy('atualizacao', 'DESC')->select('atualizacao')->limit(1)->first();

        if (empty($data->atualizacao)){
            return '--';
        } else {
            return $data->atualizacao;
        }
    }

    public function export($data, $template){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $html = view($template, $data);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'membership_arracadacao' . time() . '.xls';

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
