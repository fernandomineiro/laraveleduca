<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;

use App\Api;
use App\Pedido;
use App\PedidoItem;
use App\Curso;
use App\Aluno;
use App\WirecardApp;
use App\WirecardOrder;
use App\Pagamento;
use App\WirecardAccount;
use App\PagamentoLog;
use App\TipoPagamento;
use App\Usuario;
use App\UsuarioAssinatura;
use App\Assinatura;
use App\Faculdade;
use App\Helper\EducazMail;
use App\Nfe;
use App\NfeLog;
use App\WirecardSignature;
use App\AssinaturaPagamentoLog;
use App\AssinaturaPagamento;
use App\Helper\AssinaturaHelper;

class WirecardSignatureController extends Controller
{
    private $error = array();

    public function __construct(){
        $this->authentication();
    }

    private function authentication(){
        $setting = $this->getSetting();

        $auth = '';
        $enviroment = '';
        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $auth = 'Authorization: Basic ' . base64_encode($setting->token_producao . ':' . $setting->key_producao);
                $enviroment = 'https://api.moip.com.br/assinaturas/v1';
            } else {
                $auth = 'Authorization: Basic ' . base64_encode($setting->token_teste . ':' . $setting->key_teste);
                $enviroment = 'https://sandbox.moip.com.br/assinaturas/v1';
            }
        }

        WirecardSignature::authentication(['auth' => $auth, 'enviroment' => $enviroment]);
    }

    public function create(Request $Request){
        $data = $Request->all();

        $messages = [
            'order_id.required'           => 'Pedido inválido!',
            'plan_id.required'            => 'Plano inválido!',
            'full_name.required'          => 'O nome completo é obrigatório.',
            'birth_date.required'         => 'A data de nascimento é obrigatória.',
            'credit_card_number.required' => 'Cartão de crédito inválido',
            'credit_card_number'          => 'Cartão de crédito inválido',
            'expiry_month.required'       => 'Data de vencimento inválida',
            'expiry_month.min'            => 'Data de vencimento inválida',
            'expiry_month.max'            => 'Data de vencimento inválida',
            'expiry_year.required'        => 'Data de vencimento inválida',
            'expiry_year.min'             => 'Data de vencimento inválida',
            'expiry_year.max'             => 'Data de vencimento inválida',
        ];

        $validator = Validator::make($Request->all(), [
            'order_id'           => 'required',
            'plan_id'            => 'required',
            'full_name'          => 'required',
            'birth_date'         => 'required',
            'credit_card_number' => 'required|min:13',
            'credit_card_number' => 'required|max:19',
            'expiry_month'       => 'required|min:2|max:2',
            'expiry_year'        => 'required|min:2|max:2',
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        if ($this->validPlan($data['plan_id'])){
            $order = $this->getOrder($data['order_id']);

            if ($this->signatureExists($order['fk_usuario'], $data['plan_id'])){
                return response()->json(['error' => 'Assinatura já existe!']);
            }

            $customer_id = $this->createSubscriber($order['fk_usuario'], $data);
            $emissor = substr(preg_replace("/[^0-9]/", "", $data['credit_card_number']), 0, 6);
            $signature = $this->createSignature($customer_id, $data['plan_id'], $order['fk_usuario'], $data['order_id'], $emissor);
        }

        if ($this->error){
            return response()->json($this->error);
        } else {
            $this->atualizarRelatorioDeAssinantesAtivos();

            return response()->json(['success' => 'Assinatura realizada com sucesso!', 'code' => $data['order_id']]);
        }
    }

    private function createSubscriber($user_id, $data_creditcard){
        if (empty($this->error)){
            $customer = Aluno::join('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
            ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
            ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
            ->join('usuarios', 'alunos.fk_usuario_id', '=', 'usuarios.id')
            ->where('alunos.fk_usuario_id', $user_id)->first();

            if (!empty($customer)){
                if (!empty(WirecardSignature::getSubscriber($customer->id))){
                    if ($this->updateBillingInfos($customer->id, $data_creditcard) && $this->updateSubscriber($customer->id, $this->prepareDataSubscriber($customer))){

                        return $customer->id;
                    }
                } else {
                    $subscriber = $this->prepareDataSubscriber($customer, $data_creditcard);

                    $account_subscriber = WirecardSignature::createSubscriber($subscriber);
                    $account_subscriber = json_decode($account_subscriber);

                    if (isset($account_subscriber->errors[0]->description)){
                        $this->error = ['error' => $account_subscriber->errors[0]->description, 'code' => '2305191602'];
                    }elseif ($account_subscriber->message){
                        return $subscriber['code'];
                    }
                }
            } else {
                $this->error = ['error' => 'Cliente não encontrado!', 'code' => '2307191537'];
            }
        }
    }

    private function updateBillingInfos($customer_id, $data){
        if (!$this->error){
            $billing_infos['credit_card']['holder_name']      = $data['full_name'];
            $billing_infos['credit_card']['number']           = preg_replace("/[^0-9]/", "", $data['credit_card_number']);
            $billing_infos['credit_card']['expiration_month'] = $data['expiry_month'];
            $billing_infos['credit_card']['expiration_year']  = $data['expiry_year'];

            $signature = WirecardSignature::updateBillingInfos($customer_id, $billing_infos);
            $signature = json_decode($signature);

            if (empty($signature->errors)){
                return true;
            } else {
                $this->error = ['error' => $signature->errors[0]->description, 'code' => '2407191637'];
            }
        }
    }

    private function updateSubscriber($customer_id, $data){
        $subscriber = WirecardSignature::updateSubscriber($customer_id, $data);
        $subscriber = json_decode($subscriber);

        if (!isset($subscriber->errors)){
            return true;
        } else {
            $this->error = ['error' => $subscriber->errors[0]->description, 'code' => '2907192030'];
        }
    }

    private function createSignature($customer_id, $plan_id, $fk_usuario, $fk_pedido, $emissor){
        if (empty($this->error)){
            # CRIAR ASSINATURA
            $plan = Assinatura::find($plan_id);

            if (!empty($plan->plano_wirecard_id)){
                $data_signature['code'] = $customer_id . '-' . time();
                $data_signature['amount'] = (int)($plan->valor_de * 100);
                $data_signature['payment_method'] = 'CREDIT_CARD';
                $data_signature['plan']['name'] = $plan->titulo;
                $data_signature['plan']['code'] = $plan->plano_wirecard_id;
                $data_signature['customer']['code'] = $customer_id;

                $signature = WirecardSignature::createSignature($data_signature);
                $signature = json_decode($signature);

                if (isset($signature->errors[0]->description)){
                    $this->error = ['error' => $signature->errors[0]->description, 'code' => '2407191102'];
                } elseif ($signature->id){
                    $status = $this->getStatusSignature($signature->status);

                    if ($status === false){
                        $this->error = ['error' => 'Erro interno, entre em contato com a conosco!', 'code' => '24072191715'];
                    }

                    if ($status == 1){
                        Pedido::where('id', $fk_pedido)->update(['status' => 2]);
                    }

                    UsuarioAssinatura::create(['status' => $status, 'fk_assinatura' => $plan_id, 'fk_usuario' => $fk_usuario, 'codigo_assinatura_wirecard' => $data_signature['code'], 'fk_pedido' => $fk_pedido, 'fk_criador_id' => $fk_usuario, 'invoice_id_wirecard' => $signature->invoice->id]);

                    $assinatura_pagamento_status = ($signature->invoice->status->code != 3) ? 0 : 1;

                    AssinaturaPagamento::create([
                        'codigo_assinatura_wirecard' => $data_signature['code'],
                        'fk_pedido' => $fk_pedido,
                        'tipo' => 'cartao',
                        'pagamento_wirecard_id' => $signature->invoice->id,
                        'emissor' => $emissor,
                        'status' =>  $assinatura_pagamento_status,
                        'data_criacao' => new Datetime]);

                    return $signature->id;
                }
            } else {
                $this->error = ['error' => 'Plano não encontrado!', 'code' => '24072191138'];
            }
        }
    }

    private function validPlan($plan_id){
        if (empty($this->error)){
            $plan = Assinatura::find($plan_id);

            if (!empty($plan->plano_wirecard_id)){
                $plan_wirecard = WirecardSignature::getPlan($plan->plano_wirecard_id);

                if ($plan_wirecard){
                    $plan_wirecard = json_decode($plan_wirecard);

                    if (!empty($plan_wirecard->status) && $plan_wirecard->status == 'ACTIVE'){
                        return true;
                    } else {
                        $this->error = ['error' => 'Plano desabilitado!', 'code' => '2307191512'];
                    }
                } else {
                    $this->error = ['error' => 'Plano não localizado!', 'code' => '2305191451'];
                }
            } else {
                $this->error = ['error' => 'O plano selecionado não está cadastrado na Wirecard', 'code' => '2305191451'];
            }
        }
    }

    private function getOrder($order_id){
        if (!$this->error){
            $order = Pedido::where('pedidos.id', $order_id)
            ->select(['pedidos.*', 'usuarios.nome', 'usuarios.email', 'pedidos.metodo_pagamento AS tipo_pagamento', 'pedidos.pid', 'usuarios.foto', 'usuarios.fk_atualizador_id'])
            ->join('usuarios', 'usuarios.id', '=', 'pedidos.fk_usuario')
            ->first();

            if (!empty($order)){
                return $order->toArray();
            } else {
                $this->error = ['error' => 'Pedido não encontrado!', 'code' => '2305191525'];
            }
        }
    }

    private function getPhone($customer){
        if (!empty($customer->telefone_1)){
            $number_phone = $customer->telefone_1;
        } elseif (!empty($customer->telefone_2)){
            $number_phone = $customer->telefone_2;
        } elseif (!empty($customer->telefone_3)) {
            $number_phone = $customer->telefone_3;
        }

        if (!empty($number_phone)){
            $phone = preg_replace("/[^0-9]/", "", $number_phone);
            $data['ddd']   = substr($phone, 0, 2);
            $data['number'] = substr($phone, 2, 9);

            return $data;
        } else {
            return false;
        }
    }

    public function cancelSignature($code){
        if (!empty($code)){
            $usuario_assinatura = UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->select('status', 'codigo_assinatura_wirecard', 'fk_assinatura', 'fk_usuario')->first();

            if (!empty($usuario_assinatura->fk_assinatura)){
                $assinatura = Assinatura::select('tipo_periodo')->where('id', $usuario_assinatura->fk_assinatura)->first();

                # TIPO PERIODO 1 ANUAL
                # TIPO PERIODO 2 CICLO SEMESTRAL
                # TIPO PERIODO 3 LIVRE MENSAL
                if (!empty($assinatura->tipo_periodo) && $assinatura->tipo_periodo == 1){
                    $usuario_assinatura = UsuarioAssinatura::select('data_criacao')->where('codigo_assinatura_wirecard', $code)->first();
                    $usuario_assinatura = $usuario_assinatura->toArray();

                    $date_end = $this->defineDateCancellation($usuario_assinatura['data_criacao'], 12);

                    # AGENDAR CANCELAMENTO PARA O FINAL DO PERIODO
                    UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->update(['renovacao_cancelada' => 1, 'cancelamento_agendado' => $date_end]);

                    return response()->json(['success' => 'Renovação cancelada com sucesso!']);
                } elseif (!empty($assinatura->tipo_periodo) && $assinatura->tipo_periodo == 2){
                    $usuario_assinatura = UsuarioAssinatura::select('data_criacao')->where('codigo_assinatura_wirecard', $code)->first();
                    $usuario_assinatura = $usuario_assinatura->toArray();

                    $date_end = $this->defineDateCancellation($usuario_assinatura['data_criacao'], 6);

                    # AGENDAR CANCELAMENTO PARA O FINAL DO PERIODO
                    UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->update(['renovacao_cancelada' => 1, 'cancelamento_agendado' => $date_end]);

                    return response()->json(['success' => 'Renovação cancelada com sucesso!']);
                } elseif (!empty($assinatura->tipo_periodo) && $assinatura->tipo_periodo == 3){
                    $usuario_assinatura = UsuarioAssinatura::select('data_criacao')->where('codigo_assinatura_wirecard', $code)->first();
                    $usuario_assinatura = $usuario_assinatura->toArray();

                    $date_end = $this->defineDateCancellation($usuario_assinatura['data_criacao'], 1);

                    # AGENDAR CANCELAMENTO PARA O FINAL DO PERIODO
                    UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->update(['renovacao_cancelada' => 1, 'cancelamento_agendado' => $date_end]);

                    $this->atualizarRelatorioDeAssinantesAtivos();

                    return response()->json(['success' => 'Assinatura cancelada com sucesso!']);
                } else {
                    return response()->json(['error' => 'Tipo de assinatura não identificada. Entre em contato como administrador.', 'code' => '29072191535']);
                }

            } else {
                return response()->json(['error' => 'Assinatura não localizada!', 'code' => '29072191535']);
            }
        } else {
            return response()->json(['error' => 'Assinatura não localizada!', 'code' => '29072191534']);
        }
    }

    private function unsubscribeFromWirecard($code){
        if (!empty($code)){
            $assinatura = UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->select('status', 'codigo_assinatura_wirecard', 'fk_assinatura', 'fk_usuario')->first();

            if (!empty($assinatura->codigo_assinatura_wirecard)){
                $signature = WirecardSignature::cancelSignature($assinatura->codigo_assinatura_wirecard);

                UsuarioAssinatura::where('codigo_assinatura_wirecard', $code)->update(['status' => 0]);

                $assin = Assinatura::where('id', $assinatura->fk_assinatura)->first();
                $usuario = Usuario::where('id', $assinatura->fk_usuario)->first();

                $sendMail = new EducazMail($usuario->fk_faculdade_id);
                $sendMail->cancelamentoAssinatura([
                    'messageData' => [
                        'nome' => $usuario->nome,
                        'email' => $usuario->email,
                        'tituloAssinatura' => $assin->titulo,
                        'valorAssinatura' => number_format($assin->valor_de, 2, ',', '.')
                    ]
                ]);

                return response()->json(['success' => 'Assinatura cancelada com sucesso!']);
            } else {
                return response()->json(['error' => 'Assinatura não localizada!', 'code' => '29072191535']);
            }
        } else {
            return response()->json(['error' => 'Assinatura não localizada!', 'code' => '29072191534']);
        }
    }

    public function scheduledCancellations(){
        $subscriptions = UsuarioAssinatura::select('codigo_assinatura_wirecard', 'fk_assinatura', 'cancelamento_agendado')
        ->where('renovacao_cancelada', 1)->where('status', '!=', '0')->get();

        foreach ($subscriptions as $key => $subscription) {
            $date_cancel = date("Y-m-d", strtotime($subscription->cancelamento_agendado));

            if ($date_cancel <= date("Y-m-d")){
                $this->unsubscribeFromWirecard($subscription->codigo_assinatura_wirecard);
            }
        }
    }

    # CALCULA DATA DE FIM DO CICLO PARA CANCELAMENTO DE PLANO AGENDADO
    private function defineDateCancellation($date_begin, $billing_cycles){
        switch ($billing_cycles) {
            case '1':
                if ($billing_cycles){
                    $cycles = range(1, 120);

                    $time = strtotime($date_begin);

                    foreach ($cycles as $key => $cycle) {
                        $date = date("Y-m-d", strtotime("-1 day", strtotime("+" . $cycle ." month", $time)));

                        if (strtotime($date) > strtotime(date('Y-m-d'))){
                            return $date;
                        }
                    }
                }

                break;
            case '6':
                if ($billing_cycles){
                    $cycles = [6, 12, 18, 24, 30, 36, 42, 48];

                    $time = strtotime($date_begin);

                    foreach ($cycles as $key => $cycle) {
                        $date = date("Y-m-d", strtotime("-1 day", strtotime("+" . $cycle ." month", $time)));

                        if (strtotime($date) > strtotime(date('Y-m-d'))){
                            return $date;
                        }
                    }
                }

                break;
            case '12':
                if ($billing_cycles){
                    $cycles = [12, 24, 36, 48, 60, 72];

                    $time = strtotime($date_begin);

                    foreach ($cycles as $key => $cycle) {
                        $date = date("Y-m-d", strtotime("-1 day", strtotime("+" . $cycle ." month", $time)));

                        if (strtotime($date) > strtotime(date('Y-m-d'))){
                            return $date;
                        }
                    }
                }
            break;
        }
    }

    public function webhook(Request $Request){
        $data = $Request->all();

        if (isset($data['event']) && $data['event'] == 'subscription.updated'){
            if (!empty($data['resource']['status']) && !empty($data['resource']['code'])){
                $signature_info = UsuarioAssinatura::where('codigo_assinatura_wirecard', $data['resource']['code'])->first();

                $status = $this->getStatusSignature($data['resource']['status']);

                if ($status === false || !isset($signature_info->fk_pedido)){
                    return response()->json(['error' => 'Erro interno, entre em contato com a conosco!', 'code' => '24072191715']);
                } else {
                    if ($status == 1){
                        $pedido = Pedido::where('id', $signature_info->fk_pedido)->update(['status' => 2]);

                        $this->sendPaidOrderMail($data['order_id'], $pedido->fk_faculdade);

                        $invoices = WirecardSignature::getInvoices($data['resource']['code']);
                    }

                    UsuarioAssinatura::where('codigo_assinatura_wirecard', $data['resource']['code'])->update(['status' => $status]);
                }
            }
        } elseif (isset($data['event']) && $data['event'] == 'payment.status_updated'){
            $signature_info = UsuarioAssinatura::where('invoice_id_wirecard', $data['resource']['invoice_id'])->first();

            # STATUS 4 DA WIRECARD E DE PAGAMENTO CONCLUIDO
            if (isset($data['resource']['status']['code']) && $data['resource']['status']['code'] == 1){
                if (!empty($signature_info->fk_pedido)){
                    $this->issueInvoice($signature_info->invoice_id_wirecard, $signature_info->fk_assinatura, $signature_info->fk_pedido);
                }

                AssinaturaPagamento::where('pagamento_wirecard_id', $data['resource']['invoice_id'])->update(['status' => 1]);
            }
        } elseif (isset($data['event']) && $data['event'] == 'payment.created'){
            if (!empty($data['resource']['status']) && !empty($data['resource']['subscription_code'])){
                $signature_info = UsuarioAssinatura::where('codigo_assinatura_wirecard', $data['resource']['subscription_code'])->first();
                $assinatura_pagamento = AssinaturaPagamento::where(['pagamento_wirecard_id' => $data['resource']['invoice_id'], 'codigo_assinatura_wirecard' => $data['resource']['subscription_code']])->first();

                if (!empty($signature_info->fk_pedido) && empty($assinatura_pagamento->id)){
                    AssinaturaPagamento::create([
                    'codigo_assinatura_wirecard' => $data['resource']['subscription_code'],
                    'fk_pedido' => $signature_info->fk_pedido,
                    'tipo' => 'cartao',
                    'pagamento_wirecard_id' => $data['resource']['invoice_id'],
                    'emissor' => $data['resource']['payment_method']['credit_card']['first_six_digits'],
                    'data_criacao' => new Datetime]);
                }
            }
        }

        $fk_pedido = (!empty($signature_info->fk_pedido)) ? $signature_info->fk_pedido : 0;
        
        $log = AssinaturaPagamentoLog::create(['fk_pedido' => $fk_pedido, 'recebido' => json_encode($data), 'data_criacao' => new Datetime]);

        
    }

    private function getStatusSignature($status){
        switch (ucfirst($status)) {
            case 'ACTIVE':
                return 1;
            break;
            case 'SUSPENDED':
                return 2;
            break;

            case 'EXPIRED':
                return 3;
            break;

            case 'OVERDUE':
                return 4;
            break;

            case 'CANCELED':
                return 0;
            break;

            case 'TRIAL':
                return 5;
            break;

            default:
                return false;
            break;
        }
    }

    private function prepareDataSubscriber($customer, $data_creditcard = array()){
        $subscriber['code'] = $customer->id;
        $subscriber['email'] = $customer->email;
        $subscriber['fullname'] = $customer->nome;
        $subscriber['cpf'] = $customer->cpf;

        $phone = $this->getPhone($customer);

        $subscriber['phone_area_code'] = $phone['ddd'];
        $subscriber['phone_number']    = $phone['number'];

        $birthdate = explode("-", $customer->data_nascimento);

        $subscriber['birthdate_day'] = $birthdate[2];
        $subscriber['birthdate_month'] = $birthdate[1];
        $subscriber['birthdate_year'] = $birthdate[0];

        $subscriber['address']['street'] = $customer->logradouro;
        $subscriber['address']['number'] = $customer->numero;
        $subscriber['address']['complement'] = $customer->complemento;
        $subscriber['address']['district'] = $customer->bairro;
        $subscriber['address']['city'] = $customer->descricao_cidade;
        $subscriber['address']['state'] = $customer->uf_estado;
        $subscriber['address']['country'] = 'BRA';
        $subscriber['address']['zipcode'] = $customer->cep;

        if (!empty($data_creditcard)){
            $subscriber['billing_info']['credit_card']['holder_name']      = $data_creditcard['full_name'];
            $subscriber['billing_info']['credit_card']['number']           = preg_replace("/[^0-9]/", "", $data_creditcard['credit_card_number']);
            $subscriber['billing_info']['credit_card']['expiration_month'] = $data_creditcard['expiry_month'];
            $subscriber['billing_info']['credit_card']['expiration_year']  = $data_creditcard['expiry_year'];
        }

        return $subscriber;
    }

    private function registerWebhook(){
        $webhooks = WirecardSignature::getWebhook();

        if (!isset($webhooks->notification->webhook)){
            $setting = $this->getSetting();

            if (!empty($setting->url_retorno)){
                $data['notification']['webhook']['url'] = $setting->url_retorno;
                $data['notification']['email']['merchant']['enabled'] = false; // Para ativar o recebimento de e-mails o valor deve ser true. Para desativar escolha false
                $data['notification']['email']['customer']['enabled'] = false; // Node de configuração de recebimento de e-mail pelo customer (cliente).

                WirecardSignature::createWebhook($data);
            }
        }
    }

    private function getSetting(){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        return $setting;
    }

    public function getSubscriberInfo($fk_usuario){
        $signatures = UsuarioAssinatura::where('fk_usuario', $fk_usuario)->get();
        $signatures = $signatures->toArray();

        $customer = Aluno::where('fk_usuario_id', $fk_usuario)->first();

        if (empty($signatures)){
            return response()->json(['error' => 'Assinatura não localizada!', 'code' => '3007191310']);
        } else {
            $details = array();

            $i = 0;
            foreach ($signatures as $key => $signature) {
                $subscription = WirecardSignature::getSubscriptions($signature['codigo_assinatura_wirecard']);
                $subscriber_invoices = WirecardSignature::getInvoices($signature['codigo_assinatura_wirecard']);

                if (!empty($subscription)){
                    $details[$i]['signature'] = (array)json_decode($subscription);
                    $details[$i]['invoices'][] = (array)json_decode($subscriber_invoices);
                }

                $i++;
            }

            if (isset($signatures[0]['codigo_assinatura_wirecard'])){
                $subscriber_info = WirecardSignature::getSubscriber($signatures[0]['fk_usuario']);

                return response()->json(['subscriber' => json_decode($subscriber_info), 'signatures' => $details]);
            }
        }
    }

    public function checkStatusSubscriber($fk_usuario){
        $signature = $this->getSubscriberInfo($fk_usuario);

        $message = false;
        if (isset($signature->original) && isset($signature->original['signatures'])){
            foreach ($signature->original['signatures'] as $data) {
                if (!empty($data['signature']['status'])){
                    $signature_active_status = ['ACTIVE', 'OVERDUE', 'TRIAL'];

                    if (in_array($data['signature']['status'], $signature_active_status)){
                        return response()->json(['status' => 1, 'alert' => 'Atenção: Os dados de pagamento serão atualizados para todas as assinaturas ativas.']);
                    }
                }
            }
        }

        return response()->json(['status' => 0, 'alert' => 'Usuário não possui assinatura ativa!']);
    }

    private function signatureExists($fk_usuario, $fk_assinatura){
        $signature = UsuarioAssinatura::where('fk_usuario', $fk_usuario)
        ->where('fk_assinatura', $fk_assinatura)
        ->whereIn('status', [1, 2, 4, 5])
        ->get();

        if (isset($signature->id)){
            return true;
        } else {
            return false;
        }
    }

    private function issueInvoice($invoice_id_wirecard, $fk_assinatura, $fk_pedido){
        $nfe = Nfe::where('invoice_id_wirecard', $invoice_id_wirecard)->first();

        if (!isset($nfe->id)){
            $order = Pedido::where('id', $fk_pedido)->first();

            if (!empty($order->fk_usuario)){
                $data = $this->getDataCustomer($order->fk_usuario);

                $viacep = json_decode(NFe::getIBGECode(preg_replace("/[^0-9]/", "", $data->cep)));

                if ($data){
                    $data_invoice = array(
                        // Código do serviço de acordo com o a cidade
                        'cityServiceCode' => '5762',
                        // Descrição dos serviços prestados
                        'description'     => 'Intermediação de serviços educacionais.',
                        // Valor total do serviços
                        'servicesAmount'  => $order->valor_bruto,
                        // Dados do Tomador dos Serviços
                        'borrower' => array(
                          // CNPJ ou CPF (opcional para tomadores no exterior)
                          'federalTaxNumber' => preg_replace("/[^0-9]/", "", $data->cpf),
                          // Nome da pessoa física ou Razão Social da Empresa
                          'name'             => $data->nome . ' ' . $data->sobre_nome,
                          // Email para onde deverá ser enviado a nota fiscal
                          'email'            => $data->email, // Para visualizar os e-mails https://www.mailinator.com/
                          // Endereço do tomador
                          'address'          => array(
                            // Código do pais com três letras
                            'country'               => 'BRA',
                            // CEP do endereço (opcional para tomadores no exterior)
                            'postalCode'            => preg_replace("/[^0-9]/", "", $data->cep),
                            // Logradouro
                            'street'                => $data->logradouro,
                            // Número (opcional)
                            'number'                => $data->numero,
                            // Complemento (opcional)
                            // 'additionalInformation' => '',
                            // Bairro
                            'district'              => $data->bairro,
                            // Cidade é opcional para tomadores no exterior
                            'city' => array(
                                // Código do IBGE para a Cidade
                                'code' => (isset($viacep->ibge)) ? $viacep->ibge : '',
                                // Nome da Cidade
                                'name' => $data->descricao_cidade
                            ),
                            // Sigla do estado (opcional para tomadores no exterior)
                            'state' => $data->uf_estado
                          )
                        )
                    );

                    $nfe_log = NfeLog::create(['fk_pedido' => $fk_pedido, 'fk_assinatura' => $fk_assinatura, 'enviado' => json_encode($data_invoice), 'data_criacao' => date('Y-m-d H:i:s')]);

                    $nfe = NFe::issueInvoice($data_invoice);

                    $data_nfe = json_decode($nfe);

                    if (isset($data_nfe->id)){
                        NfeLog::whereId($nfe_log->id)->update(['nfse_id' => $data_nfe->id, 'recebido' => json_encode($data_nfe), 'data_atualizacao' => date('Y-m-d H:i:s')]);

                        Nfe::create(['nfse_id' => $data_nfe->id, 'fk_pedido' => $fk_pedido, 'fk_assinatura' => $fk_assinatura, 'invoice_id_wirecard' => $invoice_id_wirecard, 'enviado' => json_encode($data_invoice), 'recebido' => $nfe, 'status' => $data_nfe->flowStatus, 'data_criacao' => date('Y-m-d H:i:s')]);

                    } elseif (isset($data_nfe->message)){
                        NfeLog::whereId($nfe_log->id)->update(['recebido' => json_encode(['message' => $data_nfe->message]), 'error' => 1, 'data_atualizacao' => date('Y-m-d H:i:s')]);

                        Nfe::create(['fk_pedido' => $fk_pedido, 'fk_assinatura' => $fk_assinatura, 'invoice_id_wirecard' => $invoice_id_wirecard, 'enviado' => json_encode($data_invoice), 'recebido' => $data_nfe->message, 'error' => 1, 'data_criacao' => date('Y-m-d H:i:s')]);

                        return response()->json($data_nfe->message);
                    }
                }
            }
        }
    }

    public function getDataCustomer($user_id){
        $customer = Aluno::join('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
        ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
        ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
        ->join('usuarios', 'alunos.fk_usuario_id', '=', 'usuarios.id')
        ->where('alunos.fk_usuario_id', $user_id)->first();

        return $customer;
    }

    public function createPlan(Request $Request){
        $data = $Request->all();

        WirecardSignature::createPlan($data);
    }

    private function sendPaidOrderMail($order_id, $pedido_historico_id = 0, $idFaculdade = 7){
        $order = $this->getOrder($order_id);

        if ($order){
            $total = $order['valor_bruto'] - $order['valor_desconto'];

            $table_products = $this->getTableProducts($order['pid'], $this->getOrderItems($order), $order['foto']);

            $EducazMail = new EducazMail($idFaculdade);

            $data = $EducazMail->confirmacaoPedidoAssinatura([
                'messageData' => [
                    'idPedido' => $order['pid'],
                    'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                    'nome' => $order['nome'],
                    'email' => $order['email'],
                    'linkPerfil' =>  $this->getURLFront($order['fk_atualizador_id']) . '/#/perfil',
                    'dataPedido' => strftime('%d de %B de %Y', strtotime('2019-03-10 03:36:01')),
                    'formaPagamento' => 'Cartão de crédito',
                    'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                    'tabelaCursos' => $table_products
                ]
            ]);

            if ($pedido_historico_id > 0){
                $this->updateNotifyOrderHistory($pedido_historico_id);
            }
        }
    }

    private function getTableProducts($pid, $products, $image_user){
        if (!empty($products)){
            $html = '';
            foreach ($products as $key => $product) {
                $voucher_url = '';
                $print_voucher_url = '';
                # VERIFICA SE CURSO E DO TIPO HIBRITOS 4 OU PRESENCIAIS 2
                if (!empty($product['fk_cursos_tipo']) && in_array($product['fk_cursos_tipo'], [2, 4])){
                    $file_name = $pid . '-' . 'curso' . '-' . $product['pedido_item_id'];

                    Voucher::getVoucher(Url('/'), $pid, 'curso', $product['pedido_item_id']);

                    $voucher_url = Url('/') . '/files/vouchers/' . $file_name . '.pdf';
                    $print_voucher_url = Url('/') . '/api/print-voucher/' . $file_name;
                }

                $image_path = '';

                $html .= view('emails.templates.1.confirmacao_de_compra_produtos',
                        ['nome' => $product['name'],
                        'foto' => Url('/') . '/files/usuario/' . $image_user,
                        'imagem' => $image_path,
                        'voucher_url' => $voucher_url,
                        'print_voucher_url' => $print_voucher_url]
                        )->render();
            }
        }

        return $html;
    }

    private function getOrderItems($order_id){
        $items = DB::table('pedidos_item')->where('fk_pedido', $order_id)
        ->select('pedidos_item.valor_bruto', 'assinatura.titulo',
        'pedidos_item.fk_assinatura','pedidos_item.fk_pedido as fk_pedido', 'pedidos_item.id as pedido_item_id')
        ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
        ->get();

        $order_items = array();
        if (isset($items)){
            $order_items[0]['order_id'] = $items[0]->pedido_item_id;
            $order_items[0]['name']     = $items[0]->titulo;
            $order_items[0]['quantity'] = 1;

            return $order_items;
        } else {
            return ['error' => 'Itens inválidos!'];
        }
    }

    private function getURLFront($fk_faculdade){
        $faculdade = Faculdade::select('url')->find($fk_faculdade);

        if (isset($faculdade->url)){
            return $faculdade->url;
        } else {
            return '';
        }
    }

    private function atualizarRelatorioDeAssinantesAtivos(){
        $AssinaturaHelper = new AssinaturaHelper();
        $AssinaturaHelper->atualizarAssinantesAtivos();
    }
}
