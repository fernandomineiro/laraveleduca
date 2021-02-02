<?php

namespace App\Http\Controllers\Admin;

use App\Pedido;
use App\PedidoStatus;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Helper\TaxasPagamento;
use App\Aluno;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioAlunoController extends Controller{

    public function index(Request $request){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $params = $this->processaRequest($request);

        $params['data_registro'][0] = (!empty($params['data_registro'][0])) ?  $params['data_registro'][0] . ' 00:00:00' : $params['data_registro'][0];
        $params['data_registro'][1] = (!empty($params['data_registro'][1])) ?  $params['data_registro'][1] . ' 23:59:59' : $params['data_registro'][1];

        if ($request->input('export') == 1){
            $data = Aluno::relatorio_aluno($params)->get();

            if (!empty($data)){
                $this->export($data);
            }
        }

        $this->arrayViewData['alunos'] = Aluno::relatorio_aluno($params)->paginate(20);

        $this->arrayViewData['table'] = view('relatorio.aluno.table_alunos', $this->arrayViewData);

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

        $this->arrayViewData['data_registro'] = $params['data_registro'];

        return view('relatorio.aluno.lista', $this->arrayViewData);
    }

    private function processaRequest($request){
        $params = [];
        $params['orderby'] = 'alunos.id';
        $params['sort'] = 'DESC';

        if($request->get('orderby') && $request->get('sort')){
            $params['orderby'] = $request->get('orderby');
            $params['sort'] = $request->get('sort');
        }

        if( $request->has('id') && !empty($request->get('id')) ){
            $params['id'] = $request->get('id');
        }

        if( $request->has('email') && !empty($request->get('email')) ){
            $params['email'] = $request->get('email');
        }

        if( $request->has('nome') && !empty($request->get('nome')) ){
            $params['nome'] = $request->get('nome');
        }

        if( $request->has('ies') && !empty($request->get('ies')) ){
            $params['ies'] = $request->get('ies');
        }

        if( $request->has('cpf') && !empty($request->get('cpf')) ){
            //$params['cpf'] = $request->get('cpf');
            $params['cpf'] = preg_replace("/[^0-9]/", "", $request->get('cpf'));
            $params['cpf_mask'] = $request->get('cpf');
        }

        if( $request->has('data_registro') && !empty($request->get('data_registro')) ){
            $explode = explode("-",$request->get('data_registro'));
            $params['data_registro'] = [Carbon::createFromFormat('d/m/Y',trim($explode[0]))->format('Y-m-d'),Carbon::createFromFormat('d/m/Y',trim($explode[1]))->format('Y-m-d')];
        }else{
            $params['data_registro'] = [Carbon::today()->subDay(30)->format('Y-m-d'),Carbon::today()->format('Y-m-d')];
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
        $html = view('relatorio.aluno.table_alunos', ['alunos' => $data]);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'alunos_' . time() . '.xls';

        // Configurações header para forçar o download
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        header ("Content-type: application/x-msexcel");
        header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
        header ("Content-Description: PHP Generated Data" );

        // Envia o conteúdo do arquivo
        echo utf8_decode($html); exit();
    }
    function formatCpf($value){
        $cpf = preg_replace("/\D/", '', $value);

        if (strlen($cpf) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf);
        } else {
            return $cpf;
        }
    }
}
