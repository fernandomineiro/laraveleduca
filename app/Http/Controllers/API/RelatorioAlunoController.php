<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use App\Pedido;
use App\PedidoStatus;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Faculdade;
use App\CursoTipo;
use App\Helper\TaxasPagamento;
use App\Aluno;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;

class RelatorioAlunoController extends Controller{
    public function index(Request $request){
        try {    
            $data_inicial = $request->get('data_inicial'); 
            $data_final = $request->get('data_final');
            $aluno_id = $request->get('aluno_id');
            $nome = $request->get('nome');
            $email = $request->get('email');
            $cpf = $request->get('cpf');
            $export = $request->get('export');

            if (!$request->header('Faculdade')){
                return response()->json(['success' => false, 'error' => 'Faculdade inválida!']);
            } 

            if (!is_null(JWTAuth::user())) {
                $params = [];
                $params['orderby'] = 'alunos.id';
                $params['sort'] = 'DESC';

                if(!empty($aluno_id ) ){
                    $params['id'] = $aluno_id ;
                }

                if(!empty($nome)){
                    $params['nome'] = $nome;
                }

                if(!empty($email)){
                    $params['email'] = $email;
                }

                if(!empty($cpf) ){
                    //$params['cpf'] = preg_replace("/[^0-9]/", "", $cpf);
                    $params['cpf'] = $cpf;
                    $params['cpf_mask'] = $this->formatCpf(preg_replace("/[^0-9]/", "", $cpf), '###.###.###-##');
                }

                $params['ies'] = $request->header('faculdade', 7);

                $params['data_registro'][0] = ($data_inicial) ?  $data_inicial . ' 00:00:00' : date('Y-m-d H-i-s', strtotime('-25 Year'));
                $params['data_registro'][1] = ($data_final) ?  $data_final . ' 23:59:59' : date('Y-m-d H:i:s');
            }

            $this->arrayViewData['alunos'] = Aluno::relatorio_aluno($params)->paginate(20);
            $exportar['alunos'] = Aluno::relatorio_aluno($params)->get();

            if ($export == 1){
                $this->export($exportar);
            }

            return response()->json(['success' => true, 'items' => $this->arrayViewData['alunos']]);
            
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    function formatCpf($value){
      $cpf = preg_replace("/\D/", '', $value);
      
      if (strlen($cpf) === 11) {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf);
      } else {
          return $cpf;
      }
    }
    
    public function export($data){
        $html = "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />\n";
        $html .= view('relatorio.aluno.table_alunos', $data);

        // Definimos o nome do arquivo que será exportado
        $arquivo = 'alunos_' . time() . '.xls';

        // Configurações header para forçar o download
        header ("Access-Control-Allow-Origin: *");
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        // header ("Content-type: application/x-msexcel; charset=utf-8");
        header ("Content-type: application/vnd.ms-Excel; charset=UTF-8");
        header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
        header ("Content-Description: PHP Generated Data" );
        // echo "\xEF\xBB\xBF";
        // echo pack("CCC",0xef,0xbb,0xbf);

        // Envia o conteúdo do arquivo
        echo $html; exit;
    }
}
