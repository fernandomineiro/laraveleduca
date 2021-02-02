<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Pedido;
use App\PedidoItem;
use App\Curso;
use App\Professor;
use App\Curador;
use App\Parceiro;
use App\Aluno;
use App\Api;
use App\WirecardApp;
use App\WirecardOrder;
use App\Pagamento;
use App\Produtora;
use App\Faculdade;
use App\WirecardAccount;
use App\PedidoItemSplit;
use App\TipoPagamento;
use App\Usuario;
use App\Repasse;

use Moip\Moip;
use Moip\Auth\Connect;
use Moip\Auth\BasicAuth;
use Moip\Auth\OAuth;

class WirecardTransferController extends Controller
{
    private $moip;
    private $moip_merchant;
    private $error = false;

    public function __construct(){
        $this->autenticationMerchant();

        if ($this->error){
            return response()->json($this->error);
        }
    }

    private function autentication($access_token){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $Auth = new Moip(new OAuth($access_token), Moip::ENDPOINT_PRODUCTION);
            } else {
                $Auth = new Moip(new OAuth($access_token), Moip::ENDPOINT_SANDBOX);
            }

            if (!isset($Auth) || !$Auth){
                $this->error = ['error' => 'Erro na autenticação!', 'code' => '04071409'];
            }
        } else {
            $this->error = ['error' => 'O módulo Wirecard não está habilitado!', 'code' => '04071228'];
        }

        return $Auth;
    }

    private function autenticationMerchant(){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $this->moip_merchant = new Moip(new OAuth($setting->app_producao), Moip::ENDPOINT_PRODUCTION);
            } else {
                $this->moip_merchant = new Moip(new OAuth($setting->app_teste), Moip::ENDPOINT_SANDBOX);
            }

            if (!isset($this->moip_merchant) || !$this->moip_merchant){
                $this->error = ['error' => 'Erro na autenticação!', 'code' => '04071409'];
            }
        } else {
            $this->error = ['error' => 'O módulo Wirecard não está habilitado!', 'code' => '04071228'];
        }
    }

    public function listSubAccounts(){
        $sub_accounts = WirecardAccount::get();

        $data = array();
        if (isset($sub_accounts[0])){
            foreach ($sub_accounts as $key => $account) {
                if (!empty($account->account_id) && !empty($account->access_token)){
                    $moip = $this->autentication($account->access_token);
                    $account_info = $moip->accounts()->get($account->account_id);

                    $data[$account->account_id]['info']['id']         = $account_info->getId();
                    $data[$account->account_id]['info']['name']       = $account_info->getFullname();
                    $data[$account->account_id]['info']['birth_date'] = $account_info->getBirthDate();
                    $data[$account->account_id]['info']['address']    = $account_info->getAddress();

                    $data[$account->account_id]['info']['balances'] = $moip->balances()->get();
                    $data[$account->account_id]['info']['banks'] = $moip->bankaccount()->getList($account->account_id)->getBankAccounts();


                }
            }
        } else {
            return response()->json(['alert' => 'Não existe nenhuma sub-conta cadastrada no sistema!', 'code' => '15082019']);
        }

        return response()->json($data);
    }

    public function transfer(Request $Request){
        $data = $Request->all();

        $messages = [
            'type.required'    => "O tipo (type) é obrigatório. Ex.: 'professor'",
            'user_id.required' => "O ID do usuário (user_id) é obrigatório.",
            'cents.required'   => "O valor (cents) a ser transferido é obrigatório.",
        ];

        $validator = Validator::make($data, [
            'type'          => 'required',
            'user_id'       => 'required',
            'cents'         => 'required',
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        switch ($data['type']) {
            case 'professor':
                $professor_info = Professor::where('professor.fk_usuario_id', $data['user_id'])
                ->select('wirecard_account.access_token', 'conta_bancaria.titular', 'conta_bancaria.fk_banco_id', 'conta_bancaria.agencia', 'conta_bancaria.conta_corrente', 'conta_bancaria.digita_conta', 'conta_bancaria.operacao', 'conta_bancaria.documento', 'conta_bancaria.status', 'banco.numero')
                ->join('wirecard_account', 'wirecard_account.id', '=', 'professor.wirecard_account_id')
                ->leftJoin('conta_bancaria', 'professor.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->leftJoin('banco', 'banco.id', '=', 'conta_bancaria.fk_banco_id')
                ->first();

                $access_token = $professor_info->access_token;
                $data_bank = $this->getDataBank($professor_info, $data['cents']);
            break;
            case 'curador':
                $curador_info = Curador::where('curadores.fk_usuario_id', $data['user_id'])
                ->select('wirecard_account.access_token', 'conta_bancaria.titular', 'conta_bancaria.fk_banco_id', 'conta_bancaria.agencia', 'conta_bancaria.conta_corrente', 'conta_bancaria.digita_conta', 'conta_bancaria.operacao', 'conta_bancaria.documento', 'conta_bancaria.status', 'banco.numero')
                ->join('wirecard_account', 'wirecard_account.id', '=', 'curadores.wirecard_account_id')
                ->leftJoin('conta_bancaria', 'curadores.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->leftJoin('banco', 'banco.id', '=', 'conta_bancaria.fk_banco_id')
                ->first();

                $access_token = $curador_info->access_token;
                $data_bank = $this->getDataBank($curador_info, $data['cents']);
            break;
            case 'produtora':
                $produtora_info = DB::table('produtora')->where('produtora.fk_usuario_id', $data['user_id'])
                ->select('wirecard_account.access_token', 'conta_bancaria.titular', 'conta_bancaria.fk_banco_id', 'conta_bancaria.agencia', 'conta_bancaria.conta_corrente', 'conta_bancaria.digita_conta', 'conta_bancaria.operacao', 'conta_bancaria.documento', 'conta_bancaria.status', 'banco.numero')
                ->join('wirecard_account', 'wirecard_account.id', '=', 'produtora.wirecard_account_id')
                ->leftJoin('conta_bancaria', 'produtora.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->leftJoin('banco', 'banco.id', '=', 'conta_bancaria.fk_banco_id')
                ->first();

                $access_token = $produtora_info->access_token;
                $data_bank = $this->getDataBank($produtora_info, $data['cents']);
            break;
            case 'faculdade':
                $faculdade_info = Faculdade::where('faculdades.fk_usuario_id', $data['user_id'])
                ->select('wirecard_account.access_token', 'conta_bancaria.titular', 'conta_bancaria.fk_banco_id', 'conta_bancaria.agencia', 'conta_bancaria.conta_corrente', 'conta_bancaria.digita_conta', 'conta_bancaria.operacao', 'conta_bancaria.documento', 'conta_bancaria.status', 'banco.numero')
                ->join('wirecard_account', 'wirecard_account.id', '=', 'faculdades.wirecard_account_id')
                ->leftJoin('conta_bancaria', 'faculdades.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->leftJoin('banco', 'banco.id', '=', 'conta_bancaria.fk_banco_id')
                ->first();

                $access_token = $faculdade_info->access_token;
                $data_bank = $this->getDataBank($faculdade_info, $data['cents']);
            break;
        }

        if ($data_bank['status_conta'] == 1){
            try {
                $document_type = (strlen($data_bank['tax_document']) > 11) ? 'CNPJ' : 'CPF';
                $transfer = $this->autentication($access_token)->transfers()
                    ->setTransfers($data_bank['amount'], (int)$data_bank['bank_number'], (int)$data_bank['agency_number'], (int)$data_bank['agency_check_number'], (int)$data_bank['account_number'], (int)$data_bank['account_check_number'])
                    ->setHolder($data_bank['holder_name'], $data_bank['tax_document'], $document_type)
                    ->execute();

                if ($transfer->getId()){
                    Repasse::create(['fk_usuario' => $data['user_id'], 'valor' => $data_bank['amount'] / 100, 'criacao' => date('Y-m-d H:i:s') ]);

                    return response()->json(['success' => 'Transferência realizada com sucesso!']);
                }

            } catch (\Moip\Exceptions\UnautorizedException $e) {
                //StatusCode 401
                return response()->json(['error' => $e->getMessage()]);
            } catch (\Moip\Exceptions\ValidationException $e) {
                //StatusCode entre 400 e 499 (exceto 401)
                $message = str_replace("[0] The following errors ocurred:\n-:", "", $e->__toString());
                return response()->json(['error' => trim(preg_replace('/\s\s+/', ' ', $message))]);
            } catch (\Moip\Exceptions\UnexpectedException $e) {
                //StatusCode >= 500
                return response()->json(['error' => $e->getMessage()]);
            }
        } else {
            return response()->json(['error' => 'Dados bancários inválidos ou desabilitados.']);
        }
    }

    private function getDataBank($info, $amount){
        $data_bank['status_conta'] = $info->status;
        $data_bank['amount'] = $amount;
        $data_bank['bank_number'] = $info->numero;

        $agency = explode("-", $info->agencia);

        $data_bank['agency_number'] = $agency[0];

        $data_bank['agency_check_number'] = (isset($agency[1])) ? $agency[1] : '';

        $data_bank['account_number'] = $info->conta_corrente;
        $data_bank['account_check_number'] = $info->digita_conta;

        if (!empty($account[1])){
            $data_bank['account_check_number'] = (isset($account[1])) ? $account[1] : '';
        }

        $data_bank['holder_name'] = $info->titular;
        $data_bank['tax_document'] =  preg_replace("/[^0-9]/", "", $info->documento);

        return $data_bank;
    }
}
