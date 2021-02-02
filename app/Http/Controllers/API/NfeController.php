<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;

use App\Nfe;

class NfeController extends Controller
{
    public function forceDownloadInvoice(Request $Request){
        $data = $Request->all();

        if (isset($data['pedido_id'])){
            $nfe = NFe::where('fk_pedido', $data['pedido_id'])->orderBy('id', 'DESC')->first();

            if (isset($nfe->id)){
                $response = NFe::downloadInvoice($nfe->nfse_id);

                header('Expires: 0'); // no cache
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
                header('Cache-Control: private', false);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="new.pdf"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . strlen($response)); // provide file size
                header('Connection: close');
                echo $response;

            }

        }
    }

    public function webhook(Request $Request){
        $data = $Request->all();

        if (isset($data['status'])){
            switch ($data['status']) {
                case 'Issued':
                    Nfe::where('nfse_id', $data['id'])->update(['status' => $data['status'], 'data_atualizacao' => date('Y-m-d H:i:s')]);
                break;
                
                case 'Error':
                    Nfe::where('nfse_id', $data['id'])->update(['status' => $data['status'], 'data_atualizacao' => date('Y-m-d H:i:s')]);
                break;
            }
        }
    }

    public function resentInvoice(Request $Request){
        $data = $Request->all();

        if (isset($data['pedido_id'])){
            $nfe = Nfe::where('fk_pedido', $data['pedido_id'])->orderBy('id', 'DESC')->first();

            if (isset($nfe->nfse_id)){
                Nfe::resentInvoice($nfe->nfse_id);

                return response()->json(['success' => 'Nota fiscal reenviada!']);
            } else {
                return response()->json(['error' => 'Notal Fiscal não localizada para este pedido!', 'code' => '1607191049']);
            }

        } else {
            return response()->json(['error' => 'Número do pedido é inválido!', 'code' => '1607191050']);
        }
    }
}
