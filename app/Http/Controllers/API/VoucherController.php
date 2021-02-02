<?php

namespace App\Http\Controllers\API;

use App\Helper\EducazMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Pedido;
use App\PedidoItem;
use App\Curso;
use App\Voucher;
use PDF;
use QrCode;

set_time_limit(90);

class VoucherController extends Controller
{
    public function PDF($pid, $tipo, $pedidos_item_id, $fk_curso = false){
        if ($tipo == 'trilha'){
            $file_name = $pid . '-' . $tipo . '-' . $pedidos_item_id . '-' . $fk_curso;
        } else {
            $file_name = $pid . '-' . $tipo . '-' . $pedidos_item_id;
        }
        
        $url_file = Url('/') . '/files/vouchers/' . $file_name . '.pdf';

        if (!$this->remoteFileExists($url_file)){
            $this->issueVoucher($pid, $tipo, $pedidos_item_id, $fk_curso);
        }

        return redirect(Url('/files/vouchers/'. $file_name . ".pdf" ));
    }

    public function printVoucher($file_name){
        $url_file = Url('/') . '/files/vouchers/' . $file_name . '.pdf';

        if (!$this->remoteFileExists($url_file)){
            $explode = explode('-', $file_name);
            $pid = $explode[0] . '-' . $explode[1] . '-' . $explode[2];
            $tipo = $explode[3];
            $pedidos_item_id = $explode[4];
            $fk_curso = (!empty($explode[5])) ? $explode[5] : false;
            $this->issueVoucher($pid, $tipo, $pedidos_item_id, $fk_curso);
        }

        return view('voucher.print', ['url' => $url_file]);
    }

    public function issueVoucher($pid, $tipo, $pedidos_item_id, $fk_curso = false){
        try {
            $order = $this->getDataOrder($pid, $tipo, $pedidos_item_id, $fk_curso);

            if (!$order) {
                return response()->json(['error' => 'Pedido não localizado!', 'code' => '201909151105']);
            } elseif ($order['status'] != 2) {
                return response()->json(['error' => 'Pagamento ainda não foi confirmado!', 'code' => '201909151106']);
            }

            if ($tipo == 'trilha') {
                $file_name = $pid . '-' . $tipo . '-' . $pedidos_item_id . '-' . $fk_curso;
            } else {
                $file_name = $pid . '-' . $tipo . '-' . $pedidos_item_id;
            }

            $this->printPDF($order, $file_name, $fk_curso);

            return json_encode(['success' => Url("/files/vouchers/" . $file_name . ".pdf")], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function getDataOrder($pid, $tipo, $pedidos_item_id, $fk_curso = false){
        $order = Pedido::select(['pedidos.status', 'usuarios.nome', 'pedidos.id', 'pedidos.pid', 'usuarios.foto', 'usuarios.id AS fk_usuario'])
        ->join('usuarios', 'usuarios.id', '=', 'pedidos.fk_usuario')
        ->join('pedidos_item', 'pedidos_item.fk_pedido', '=', 'pedidos.id')
        ->where('pid', $pid)->first();

        if (isset($order->status)){
            $item = $this->getOrderItems($order->id, $tipo, $pedidos_item_id, $fk_curso);

            if (!empty($item['nome'])){
                $data = $order->toArray();
                $data['item'] = $item;
                $data['item']['fk_curso '] = $fk_curso;
                $data['item']['pedidos_item_id'] = $pedidos_item_id;

                return $data;
            } else {
                return false;
            }

        } else {
            return false;
        }

    }

    # BUSCA CURSOS HIBRITOS 4 E PRESENCIAIS 2
    private function getOrderItems($order_id, $tipo, $pedidos_item_id, $fk_curso = false){
        $filter_type_cursos = [2, 4];

        if ($tipo == 'curso'){
            $item = DB::table('pedidos_item')->where(['fk_pedido' => $order_id, 'pedidos_item.id' => $pedidos_item_id])->whereIn('cursos_tipo.id', $filter_type_cursos)
            ->select('pedidos_item.valor_bruto', 'cursos.titulo AS titulo_curso', 'cursos.fk_parceiro', 'cursos.id as fk_curso',
                'pedidos_item.fk_trilha', 'pedidos_item.fk_evento', 'pedidos_item.fk_assinatura','pedidos_item.fk_pedido as fk_pedido',
                'cursos.fk_faculdade', 'cursos.fk_professor', 'cursos.fk_professor_participante', 'cursos.fk_curador', 'cursos.fk_conteudista', 'cursos.fk_produtora',
                'trilha.titulo AS titulo_trilha', 'trilha.valor AS valor_trilha', 
            'trilha.titulo AS titulo_trilha', 'trilha.valor AS valor_trilha', 
                'trilha.titulo AS titulo_trilha', 'trilha.valor AS valor_trilha', 
                'trilha.valor_venda AS valor_venda_trilha', 'cursos_tipo.id as cursos_tipo_id', 'cursos.endereco_presencial', DB::raw('CONCAT(professor.nome, " ", professor.sobrenome) as professor_nome'))
            ->leftJoin('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
            ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
            ->leftJoin('trilha', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->leftJoin('eventos', 'pedidos_item.fk_evento', '=', 'eventos.id')
            ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
            ->leftJoin('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->first();
        } elseif ($tipo == 'trilha') {
            $item = DB::table('pedidos_item')->where(['fk_pedido' => $order_id, 'pedidos_item.id' => $pedidos_item_id, 'cursos.id' => $fk_curso])->whereIn('cursos_tipo.id', $filter_type_cursos)
            ->select('pedidos_item.valor_bruto', 'cursos.titulo AS titulo_curso', 'cursos.fk_parceiro', 'cursos.id as fk_curso',
                'pedidos_item.fk_trilha', 'pedidos_item.fk_evento', 'pedidos_item.fk_assinatura','pedidos_item.fk_pedido as fk_pedido',
                'cursos.fk_faculdade', 'cursos.fk_professor', 'cursos.fk_professor_participante', 'cursos.fk_curador', 'cursos.fk_conteudista', 'cursos.fk_produtora',
                'trilha.titulo AS titulo_trilha', 'trilha.valor AS valor_trilha', 
                'trilha.valor_venda AS valor_venda_trilha', 'cursos_tipo.id as cursos_tipo_id', 'cursos.endereco_presencial', DB::raw('CONCAT(professor.nome, " ", professor.sobrenome) as professor_nome'))
            ->leftJoin('trilha', 'pedidos_item.fk_trilha', '=', 'trilha.id')
            ->leftJoin('trilha_curso', 'trilha_curso.fk_trilha', '=', 'trilha.id')
            ->leftJoin('cursos', 'trilha_curso.fk_curso', '=', 'cursos.id')
            ->leftJoin('cursos_tipo', 'cursos_tipo.id', '=', 'cursos.fk_cursos_tipo')
            ->leftJoin('eventos', 'pedidos_item.fk_evento', '=', 'eventos.id')
            ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
            ->leftJoin('professor', 'professor.id', '=', 'cursos.fk_professor')
            ->first();
        }

        $order_items = array();

        if (!empty($item->titulo_curso)){
            $order_items['nome']  = $item->titulo_curso;
            $order_items['valor'] = $item->valor_bruto;
            $order_items['endereco_presencial']  = $item->endereco_presencial;
            $order_items['professor_nome'] = $item->professor_nome;
        }

        if (!empty($order_items)){
            return $order_items;
        } else {
            return false;
        }        
    }

    public function printPDF($data, $file_name, $fk_curso){
        $path = "files/vouchers/";

        $data['img_url'] = false;
        if (!empty($data['foto'])){
            $file = Url('files/usuario') . '/' . $data['foto'];

            if ($this->remoteFileExists($file)){
                $data['img_url'] = $file;
            }
        }

        $code_pid = explode("-", $data['pid'])[1] . explode("-", $data['pid'])[2];

        $data['code_qrcode'] = $data['item']['pedidos_item_id'] . $code_pid;
        $data['qrcode'] = $this->generateQRCode(base64_encode($data['code_qrcode']));
        
        if(!is_dir($path)){
            mkdir($path, 0700);
        }  

        $url_fk_curso = (!empty($fk_curso) && $fk_curso > 0) ? '/' . $fk_curso : '';
        
        $fk_curso = (!empty($fk_curso) && $fk_curso > 0) ? $fk_curso : 0;

        if ($fk_curso > 0){
            $url = url('/api/voucher-pdf/' . $data['pid'] . '/trilha/' . $data['item']['pedidos_item_id'] . $url_fk_curso);
        } else {
            $url = url('/api/voucher-pdf/' . $data['pid'] . '/curso/' . $data['item']['pedidos_item_id'] . $url_fk_curso);
        }

        $this->updateOrCreateVoucher($data['fk_usuario'], $data['id'], $fk_curso, $url, $data['code_qrcode']);

        $pdf = PDF::loadView('voucher.index', $data);
        $pdf->save('files/vouchers/' . $file_name . '.pdf');
    }

    private function remoteFileExists($url) {
        $curl = curl_init($url);
    
        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);
    
        //do request
        $result = curl_exec($curl);
    
        $ret = false;
    
        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
    
            if ($statusCode == 200) {
                $ret = true;   
            }
        }
    
        curl_close($curl);
    
        return $ret;
    }

    private function updateOrCreateVoucher($fk_usuario, $fk_pedido, $fk_curso, $url, $code){
        $voucher = Voucher::updateOrCreate(
            ['code' => $code],
            ['fk_usuario' => $fk_usuario, 'fk_pedido' => $fk_pedido, 'fk_curso' => $fk_curso, 'url' => $url, 'criacao' => new Datetime()]
        );

        if (isset($voucher->id)){
            return $voucher;
        } else {
            return $voucher;
        }
    }

    public function generateQRCode($code){
        $qrcode = QrCode::format('png')->size(400)->generate(url("/autentica-voucher/"  . $code ));

        return $qrcode;
    }
}
