<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Pedido;
use App\PedidoItem;
use App\Curso;
use App\Professor;
use App\Faculdade;
use App\Curador;
use App\Parceiro;
use App\Produtora;
use App\Aluno;
use App\Api;
use App\WirecardApp;
use App\WirecardOrder;
use App\Pagamento;
use App\WirecardAccount;
use App\PedidoItemSplit;
use App\PagamentoLog;
use App\TipoPagamento;
use App\Usuario;
use App\Nfe;
use App\NfeLog;
use App\Helper\EducazMail;
use App\Helper\TaxasPagamento;
use App\Helper\PedidoHistorico;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InboxMessage;
use Illuminate\Support\Facades\Mail;
use Image;
use Moip\Moip;
use Moip\Auth\Connect;
use Moip\Auth\BasicAuth;
use Moip\Auth\OAuth;
use App\Voucher;
use App\PedidoHistoricoStatus;
use App\JurosCartao;
use App\Impostos;
use App\TrilhaCurso;

class WirecardController extends Controller
{
    private $moip;
    private $moip_merchant;
    private $error = false;
    private $url_front;

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

    public function adicionarCursosModulosAluno($idOrder, $idFaculdade) {
        $cursosModulos = new CursoModuloConclusaoController();

        $pedido = Pedido::find($idOrder);

        $aluno = Aluno::select('id')->where('fk_usuario_id', $pedido->fk_usuario)->first();

        $request = new Request();
        $request->merge(['faculdade' => $idFaculdade, 'aluno' => $aluno->id, 'pedido' => $idOrder]);

        /** @var PedidoItem $order */
        $pedidos = PedidoItem::where('fk_pedido', $idOrder)->get();
        foreach ($pedidos as $pedido) {
            if (!empty($pedido->fk_curso)){
                $cursosModulos->adicionarModulosPorCurso($pedido->fk_curso, $request);
            } elseif (!empty($pedido->fk_trilha)){
                $trilha_cursos = TrilhaCurso::select('fk_curso')->where('fk_trilha', $pedido->fk_trilha)->get();

                foreach ($trilha_cursos as $key => $curso) {
                    $cursosModulos->adicionarModulosPorCurso($curso->fk_curso, $request);
                }
            }
        }

        return true;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function pay(Request $request) {
        try {
            $validator = Validator::make($request->all(), 
                ['order_id' => 'required', 'method' => 'required']
            );
            
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return response()->json(['error' => $errors], 422);
            }

            $order_id = $request->input('order_id');
            $method   = $request->input('method');

            if (!$this->validOrder($order_id)) {
                return response()->json(['error' => [$this->error]]);
            }

            if (isset($order_id) && is_numeric($order_id) && $order_id > 0){

                if ($method == 'bank-slip'){
                    $response = $this->bankSlip($order_id);
                } elseif ($method == 'debit'){
                    $response = $this->debit($order_id);
                } elseif ($method == 'credit-card'){
                    $messages = [
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
                        'installment.required'        => 'Número de parcelas inválida',
                        'document.required'           => 'CPF ou CNPJ inválido',
                        'document.ddd'                => 'DDD inválido',
                        'document.phone'              => 'Telefone inválido',
                    ];

                    $validator = Validator::make($request->all(), [
                        'full_name'          => 'required',
                        'birth_date'         => 'required',
                        'credit_card_number' => 'required|min:13',
                        'credit_card_number' => 'required|max:19',
                        'cvv'                => 'required',
                        'expiry_month'       => 'required|min:2|max:2',
                        'expiry_year'        => 'required|min:2|max:2',
                        'installment'        => 'required',
                        'document'           => 'required',
                        'ddd'                => 'required',
                        'phone'              => 'required'
                    ], $messages);
                    
                    if ($validator->fails()) {
                        $errors = $validator->errors()->toArray();

                        $this->paymentLog($errors);
                        return response()->json(['error' => $errors], 422);
                    }

                    if (!$this->validDocument(preg_replace("/[^0-9]/", "", $request->input('document')))){
                        return response()->json(['error' => ['CPF ou CNPJ inválido!']]);
                    }

                    $data_credit_card['full_name']          = $request->input('full_name');
                    $data_credit_card['birth_date']         = $request->input('birth_date');
                    $data_credit_card['credit_card_number'] = preg_replace("/[^0-9]/", "", $request->input('credit_card_number'));
                    $data_credit_card['cvv']                = preg_replace("/[^0-9]/", "", $request->input('cvv'));
                    $data_credit_card['expiry_month']       = $request->input('expiry_month');
                    $data_credit_card['expiry_year']        = $request->input('expiry_year');
                    $data_credit_card['installment']        = preg_replace("/[^0-9]/", "", $request->input('installment'));
                    $data_credit_card['document']           = preg_replace("/[^0-9]/", "", $request->input('document'));
                    $data_credit_card['ddd']                = preg_replace("/[^0-9]/", "", $request->input('ddd'));
                    $data_credit_card['phone']              = preg_replace("/[^0-9]/", "", $request->input('phone'));

                    $response = $this->createCredit($order_id, $data_credit_card);
                } else {
                    return response()->json(['error' => 'Meio de pagamento não selecionado ou inválido...', 'code' => '6']);
                }
            } else {
                return response()->json(['error' => 'Pedido não encontrado...', 'code' => '6']);
            }

            if (!empty($response['success'])) {
                $this->adicionarCursosModulosAluno($order_id, $request->header('Faculdade', 7));
            }

            return response()->json($response);
        }  catch (\Exception $e) {
            
            $this->paymentLog([$e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrigir o problema',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function bankSlip($order_id){
        $data_order = $this->getDataOrder($order_id);

        if (isset($data_order->fk_usuario) && $data_order->fk_usuario > 0){
            $data_customer = $this->getDataCustomer($data_order->fk_usuario);

            if (!$data_customer){
                return ['error' => 'Aluno não encontrado!', 'code' => '100720191140'];
            }

            if (!$this->error){ $order_items = $this->getOrderItems($order_id); } # PREPARA ITENS PARA CRIACAO DO PEDIDO NA WIRECARD

            if (!$this->error){
                $this->updateValueSplitWidthTaxes($order_id, $order_items, 'bank_slip');

                # PREPARA DADOS PARA SPLIT
                $data_split = $this->getSplit($order_id, $order_items, 'bank_slip');
            }

            if (!$this->error){
                $wirecard_customer = $this->createCustomer($data_customer);

                if (is_array($wirecard_customer) && isset($wirecard_customer['error'])){
                    return $wirecard_customer;
                }

                $data_sent_create_order = json_encode(array_merge(['method' => 'bankslip', 'gateway' => 'wirecard'],
                ['itens' => $order_items],
                ['data_order' => $data_order->toArray()],
                ['splits' => $data_split]));

                $log = PagamentoLog::create(['fk_pedido' => $order_id, 'enviado' => $data_sent_create_order, 'data_criacao' => new Datetime]);

                $wirecard_order = $this->createOrder($order_id, $wirecard_customer, $order_items, $data_order->valor_desconto, $data_split);
                
                if ($wirecard_order){
                    $this->addWirecardOrder($order_id, $wirecard_order->getId());

                    $logo_uri = 'https://cdn.moip.com.br/wp-content/uploads/2016/05/02163352/logo-moip.png';
                    $expiration_date = new DateTime('now +2 days');
                    $instruction_lines = ['O BOLETO NÃO PODERÁ SER PAGO APÓS O VENCIMENTO!', 'O CURSO ESTARÁ DISPONÍVEL NA ÁREA DE PERFIL DO ALUNO, APÓS 48h DA CONFIRMAÇÃO DO PAGAMENTO.', ''];
                    $payment = $wirecard_order->payments()->setBoleto($expiration_date, $logo_uri, $instruction_lines)->execute();

                    if ($payment){
                        $data_received_create_order['id']             = $payment->getId();
                        $data_received_create_order['status']         = $payment->getStatus();
                        $data_received_create_order['total']          = $payment->getAmount()->total;
                        $data_received_create_order['link_bank_slip'] = $payment->getHrefPrintBoleto();
                        $data_received_create_order['created_at']     = $payment->getCreatedAt()->format('Y-m-d H:i:s');

                        Pedido::where('id', $order_id)->update(['status' => 5, 'metodo_pagamento' => 'boleto', 'link_boleto' => $payment->getHrefBoleto()]);
                        PagamentoLog::where('id', $log->id)->update(['recebido' => json_encode($data_received_create_order)]);

                        $this->sendPaidBankSlipOrderMail($order_id, 0, $data_order->fk_faculdade);

                        return [
                            'success' => 'Boleto gerado com sucesso!',
                            'link_bank_slip' => $payment->getHrefBoleto(),
                            'link_code_bank_lip' => $payment->getLineCodeBoleto(),
                            'link_print_bank_lip' => $payment->getHrefPrintBoleto(),
                        ];
                    } else {
                        return ['error' => 'Não foi possível gerar o boleto', 'code' => '07'];
                    }
                } else {
                    return ['error' => 'Erro na criação do pedido, na Wirecard. ' . $this->error , 'code' => '1'];
                }
            } else {
                return $this->error;
            }

        } else {
            return ['error' => 'Pedido não encontrado...', 'code' => '2'];
        }
    }

    private function debit($order_id){
        $data_order = $this->getDataOrder($order_id);

        if (isset($data_order->fk_usuario) && $data_order->fk_usuario > 0){
            $data_customer = $this->getDataCustomer($data_order->fk_usuario);

            if (!$data_customer){
                return ['error' => 'Aluno não encontrado!', 'code' => '100720191140'];
            }

            if (!$this->error){ $order_items = $this->getOrderItems($order_id); } # PREPARA ITENS PARA CRIACAO DO PEDIDO NA WIRECARD

            if (!$this->error){
                $this->updateValueSplitWidthTaxes($order_id, $order_items, 'debit');

                # PREPARA DADOS PARA SPLIT
                $data_split = $this->getSplit($order_id, $order_items, 'debit');
            }

            if (empty($this->error)) {
                $wirecard_customer = $this->createCustomer($data_customer);

                if (is_array($wirecard_customer) && isset($wirecard_customer['error'])){
                    return $wirecard_customer;
                }

                $data_sent_create_order = json_encode(array_merge(['method' => 'debit', 'gateway' => 'wirecard'],
                ['itens' => $order_items],
                ['data_order' => $data_order->toArray()],
                ['splits' => $data_split]));

                $pedido = Pedido::where('id', $order_id)->update(['status' => 5, 'atualizacao' => new Datetime(), 'metodo_pagamento' => 'debito']);
                $log = PagamentoLog::create(['fk_pedido' => $order_id, 'enviado' => $data_sent_create_order, 'data_criacao' => new Datetime]);

                $wirecard_order = $this->createOrder($order_id, $wirecard_customer, $order_items, $data_order->valor_desconto, $data_split);

                if ($wirecard_order){
                    $this->addWirecardOrder($order_id, $wirecard_order->getId());
                    
                    $bank_number = '341';
                    $return_uri = url('/');
                    $expiration_date = new Datetime();
                    $payment = $wirecard_order->payments()
                        ->setOnlineBankDebit($bank_number, $expiration_date, $return_uri)
                        ->execute();

                    if ($payment){
                        PagamentoLog::where('id', $log->id)->update(['recebido' => json_encode('')]);

                        $this->sendPaidBankSlipOrderMail($order_id, 0, $pedido->fk_faculdade);

                        return [
                            'success' => $payment->getHrefDebitItau(),
                        ];
                    } else {
                        return ['error' => 'Não foi possível gerar o boleto', 'code' => '07'];
                    }
                } else {
                    return ['error' => 'Erro na criação do pedido, na Wirecard.', 'code' => '1'];
                }
            } else {
                return ['error' => $this->error, 'code' => 1];
            }

        } else {
            return ['error' => 'Pedido não encontrado...', 'code' => '2'];
        }
    }

    private function createCredit($order_id, $data_credit_card) {
        $data_order = $this->getDataOrder($order_id);

        if (isset($data_order->fk_usuario) && $data_order->fk_usuario > 0){
            $data_customer =  $this->getDataCustomer($data_order->fk_usuario);
            
            if (!$data_customer) {
                $aluno = Aluno::where('fk_usuario_id', $data_order->fk_usuario)->first();
                return [
                    'error' => [
                        'Aluno não encontrado!',
                        empty($aluno->endereco->cidade) ? 'Cidade não foi preenchida' : null,
                        empty($aluno->endereco->estado) ? 'Estado não foi preenchido' : null,
                    ], 'code' => '100720191140'
                ];
            }

            if (!$this->error){
                # PREPARA ITENS PARA CRIACAO DO PEDIDO NA WIRECARD
                $order_items = $this->getOrderItems($order_id);
            }

            if (!$this->error){
                $this->updateValueSplitWidthTaxes($order_id, $order_items, 'creditcard', $data_credit_card['installment']);

                # PREPARA DADOS PARA SPLIT
                $data_split = $this->getSplit($order_id, $order_items, 'creditcard');
            }

            if (empty($this->error)){
                $wirecard_customer = $this->createCustomer($data_customer);

                if (is_array($wirecard_customer) && isset($wirecard_customer['error'])){
                    return $wirecard_customer;
                }

                $data_sent_create_order = json_encode(array_merge(['method' => 'creditcard', 'gateway' => 'wirecard'],
                                                      ['itens' => $order_items],
                                                      ['data_order' => $data_order->toArray()],
                                                      ['splits' => $data_split]));

                $log = PagamentoLog::create(['fk_pedido' => $order_id, 'enviado' => $data_sent_create_order, 'data_criacao' => new Datetime]);

                $wirecard_order = $this->createOrder($order_id, $wirecard_customer, $order_items, $data_order->valor_desconto, $data_split);

                if ($this->error) { return ['error' => $this->error, 'code' => '10072019']; }

                $this->addWirecardOrder($order_id, $wirecard_order->getId());

                if ($wirecard_order){
                    $document_type = (strlen($data_credit_card['document']) > 11) ? 'CNPJ' : 'CPF';

                    $holder = $this->moip_merchant->holders()->setFullname($data_credit_card['full_name'])
                    ->setBirthDate($data_credit_card['birth_date'])
                    ->setTaxDocument(preg_replace("/[^0-9]/", "", $data_credit_card['document']), $document_type)
                    ->setPhone($data_credit_card['ddd'], $data_credit_card['phone'], 55);

                    try {
                        $payment = $wirecard_order->payments()
                            ->setCreditCard($data_credit_card['expiry_month'], $data_credit_card['expiry_year'], $data_credit_card['credit_card_number'], $data_credit_card['cvv'], $holder)
                            ->setInstallmentCount($data_credit_card['installment'])
                            ->setStatementDescriptor('')
                            ->execute();
                    } catch (\Moip\Exceptions\ValidationException $error) {
                        
                        /** @var \Moip\Exceptions\Error $moipError */
                        $errors = collect($error->getErrors())->map(function($moipError) {
                            return $moipError->getDescription();
                        })->prepend('Erro na criação do pedido, na Wirecard.');
                        
                        return ['error' => $errors, 'code' => $error->getStatusCode()];
                    }
                    

                    $data_wirecard_order = $this->moip_merchant->orders()->get($wirecard_order->getId());

                    $currentPayment = $this->moip_merchant->payments()->get($payment->getId());
                    
                    if ($payment){
                        $data_received_create_order['id']                 = $payment->getId();
                        $data_received_create_order['status']             = $payment->getStatus();
                        $data_received_create_order['total']              = $payment->getAmount()->total;
                        $data_received_create_order['created_at']         = $payment->getCreatedAt()->format('Y-m-d H:i:s');
                        $data_received_create_order['funding_insturment'] = $payment->getFundingInstrument()->method;
                        $data_received_create_order['installment_count']  = $payment->getInstallmentCount();

                        PagamentoLog::where('id', $log->id)->update(['recebido' => json_encode($data_received_create_order)]);

                        if ($payment->getStatus() == 'IN_ANALYSIS' || $payment->getStatus() == 'WAITING'){
                            Pedido::where('id', $order_id)->update(['status' => 5, 'atualizacao' => new Datetime(), 'metodo_pagamento' => 'cartao']);

                            return ['success' => 'Pagamento em análise!'];
                        }
                        
                        if ($payment->getStatus() == 'CANCELLED') {
                            Pedido::where('id', $order_id)->update(['status' => 3, 'atualizacao' => new Datetime(), 'metodo_pagamento' => 'cartao']);
                            return ['error' => 'Seu Pedido não foi autorizado na wirecard',];
                        }
                        
                        return ['error' => '', 'payment' => $currentPayment, 'dataWirecard' => $data_wirecard_order];
                    } else {
                        return ['error' => 'Erro ao processar pedido', 'code' => '06'];
                    }

                } else {
                    return ['error' => 'Erro na criação do pedido, na Wirecard. ' . $this->error, 'code' => '03'];
                }
            } else {
                return ['error' => $this->error, 'code' => 'WR002'];
            }

        } else {
            return ['error' => 'Pedido não encontrado...', 'code' => '04'];
        }
    }

    private function getOrderItems($order_id){
        $items = DB::table('pedidos_item')->where('fk_pedido', $order_id)
        ->select('pedidos_item.valor_bruto', 'cursos.titulo AS titulo_curso', 'cursos.imagem', 'cursos.fk_parceiro', 'cursos.id as fk_curso',
            'pedidos_item.fk_trilha', 'pedidos_item.fk_evento', 'pedidos_item.fk_assinatura','pedidos_item.fk_pedido as fk_pedido',
            'cursos.fk_faculdade', 'cursos.fk_professor', 'cursos.fk_professor_participante', 'cursos.fk_curador', 'cursos.fk_conteudista', 'cursos.fk_produtora',
            'trilha.titulo AS titulo_trilha', 'trilha.valor AS valor_trilha', 'trilha.valor_venda AS valor_venda_trilha', 'cursos.fk_cursos_tipo', 'pedidos_item.id as pedido_item_id')
        ->leftJoin('cursos', 'pedidos_item.fk_curso', '=', 'cursos.id')
        ->leftJoin('trilha', 'pedidos_item.fk_trilha', '=', 'trilha.id')
        ->leftJoin('eventos', 'pedidos_item.fk_evento', '=', 'eventos.id')
        ->leftJoin('assinatura', 'pedidos_item.fk_assinatura', '=', 'assinatura.id')
        ->get();

        $order_items = array();
        if (isset($items)){
            $items_array = $items->toArray();

            foreach ($items_array as $key => $item) {
                if (!empty($item->fk_trilha) && $item->fk_trilha > 0){
                    $order_items[$key]['fk_trilha'] = $item->fk_trilha;
                    $order_items[$key]['fk_faculdade'] = $item->fk_faculdade;
                    $order_items[$key]['fk_professor'] = $item->fk_professor;
                    $order_items[$key]['fk_curador'] = $item->fk_curador;
                    $order_items[$key]['fk_professor_participante'] = $item->fk_professor_participante;
                    $order_items[$key]['fk_conteudista'] = $item->fk_conteudista;
                    $order_items[$key]['fk_produtora'] = $item->fk_produtora;
                    $order_items[$key]['fk_parceiro'] = $item->fk_parceiro;
                    $order_items[$key]['image']      = $item->imagem;
                    $order_items[$key]['fk_cursos_tipo'] = $item->fk_cursos_tipo;
                    $order_items[$key]['pedido_item_id'] = $item->pedido_item_id;

                    $order_items[$key]['name']     = $item->titulo_trilha;
                    $order_items[$key]['value']    = (isset($item->valor_venda_trilha) && $item->valor_venda_trilha > 0) ? $item->valor_venda_trilha : $item->valor_trilha;
                    $order_items[$key]['quantity'] = 1;
                } elseif (!empty($item->fk_curso) && $item->fk_curso > 0){
                    $order_items[$key]['fk_curso'] = $item->fk_curso;
                    $order_items[$key]['fk_faculdade'] = $item->fk_faculdade;
                    $order_items[$key]['fk_professor'] = $item->fk_professor;
                    $order_items[$key]['fk_curador'] = $item->fk_curador;
                    $order_items[$key]['fk_professor_participante'] = $item->fk_professor_participante;
                    $order_items[$key]['fk_conteudista'] = $item->fk_conteudista;
                    $order_items[$key]['fk_produtora'] = $item->fk_produtora;
                    $order_items[$key]['fk_parceiro'] = $item->fk_parceiro;
                    $order_items[$key]['image']      = $item->imagem;
                    $order_items[$key]['fk_cursos_tipo'] = $item->fk_cursos_tipo;
                    $order_items[$key]['pedido_item_id'] = $item->pedido_item_id;

                    $order_items[$key]['name']        = $item->titulo_curso;
                    $order_items[$key]['value']       = $item->valor_bruto;
                    $order_items[$key]['quantity']    = 1;
                }
            }

            return $order_items;
        } else {
            return ['error' => 'Itens inválidos!'];
        }
    }

    private function validOrder($order_id){
        $order = Pedido::where('id', $order_id)->first();

        if (!$order){
            $this->error = 'O pedido não existe!';

            return false;
        } elseif ($order->status != '1') {
            $this->error = 'O pedido já está sendo processado!';

            return false;
        } else {
            return true;
        }
    }

    private function getDataOrder($order_id){
        $order = Pedido::find($order_id);

        if ($order){
            return $order;
        } else {
            return false;
        }
    }

    private function getSplit($order_id, $items, $payment_method){
        if (!$this->error){
            $wirecard_account = array();
            $receiver = array();
            $items_trilha = array();

            foreach ($items as $key => $item) {
                if (!empty($item['fk_trilha'])){

                    unset($items[$key]);
                    $items_trilha = $this->getCursosTrilha($item['fk_trilha']);
                }
            }

            if (!empty($items) && !empty($items_trilha)){
                $items = array_merge($items_trilha, $items);
            } elseif (!empty($items_trilha)){
                $items = $items_trilha;
            }

            foreach ($items as $key => $item) {
                if (!empty($item['fk_curso'])) {
                    $values_split = $this->getPorcentageSplit($order_id, $item['fk_curso'], $payment_method);

                    if (!empty($values_split)){
                        /* USUARIO QUE FAZEM PARTE DO SPLIT DE PAGAMENTOS */
                        $users_split = array();
                        foreach ($values_split as $key => $porcentage) {
                            $name_type = str_replace("valor_split_", "", $key);

                            if ($porcentage > 0){
                                $users_split[$name_type] = $item['fk_' . $name_type];
                            }
                        }

                        $total_users_split = $this->countUserSplit($users_split);

                        if (!empty($users_split)){
                            foreach ($users_split as $type => $user_id) {
                                $user_split = $this->getDataUserSplit($user_id, $type);

                                if (!empty($user_split) && !is_null($user_split)){
                                    if (!empty($user_split['wirecard_account_id'])){
                                        $wirecardAccount = WirecardAccount::where('id', $user_split['wirecard_account_id'])->first();

                                        if (isset($wirecardAccount->id)){
                                            $wirecard_account = $wirecardAccount->toArray();
                                            $receiver[$wirecard_account['account_id']]['id']   = $wirecard_account['account_id'];
                                            $receiver[$wirecard_account['account_id']]['type'] = 'SECONDARY';

                                            if (isset($receiver[$wirecard_account['account_id']]['fixed'])){
                                                $receiver[$wirecard_account['account_id']]['fixed'] =  $receiver[$wirecard_account['account_id']]['fixed'] + ($values_split['valor_split_' . $type] * 100);
                                            } else {
                                                $receiver[$wirecard_account['account_id']]['fixed'] = $values_split['valor_split_' . $type] * 100;
                                            }

                                            $receiver[$wirecard_account['account_id']]['percentual'] = null;
                                            $receiver[$wirecard_account['account_id']]['feePayor']   = false;
                                        }
                                    }
                                } else {
                                    $this->error = [
                                        'error' => 'Participante do split não encontrado!', 
                                        'code' => '201908292108',
                                        'type' =>  $type,
                                        'id' =>  $user_id,
                                        'values' => $values_split
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($receiver)){
                return $receiver;
            }

        } else {
            return $this->error;
        }
    }

    private function getCursosTrilha($fk_trilha){
        $cursos = TrilhaCurso::select('cursos.id as fk_curso', 'cursos.fk_faculdade', 'cursos.fk_professor', 'cursos.fk_curador', 'cursos.fk_professor_participante',
        'cursos.fk_conteudista', 'cursos.fk_produtora', 'cursos.fk_parceiro', 'cursos.fk_cursos_tipo', 'cursos.titulo', 'cursos_valor.valor AS value')
            ->where(['fk_trilha' => $fk_trilha, 'cursos.status' => 5])
            ->join('cursos', 'cursos.id', '=', 'trilha_curso.fk_curso')
            ->join('cursos_valor', 'cursos_valor.fk_curso', '=', 'trilha_curso.fk_curso')
            ->get();

        if (isset($cursos[0]->fk_curso)){
            return $cursos->toArray();
        } else {
            return false;
        }
    }

    private function getPorcentageSplit($fk_pedido, $fk_curso, $payment_method){
        /* USUARIO QUE FAZEM PARTE DO SPLIT DE PAGAMENTOS */
        $porcentagem_split_item = PedidoItemSplit::where([
            ['fk_pedido', '=', $fk_pedido],
            ['fk_curso', '=', $fk_curso]
        ])->select('valor_split_professor', 'valor_split_professor_participante', 'valor_split_curador', 'valor_split_parceiro', 'valor_split_faculdade', 'valor_split_produtora')->first();

        /* USUARIO QUE FAZEM PARTE DO SPLIT DE PAGAMENTOS */
        $porcentagem_split_item_exception = PedidoItemSplit::where([
            ['fk_pedido', '=', $fk_pedido],
            ['fk_curso', '=', $fk_curso]
        ])->select('split_professor_manual', 'split_professor_participante_manual', 'split_curador_manual', 'split_parceiro_manual', 'split_faculdade_manual', 'split_produtora_manual')->first();

        if ($porcentagem_split_item){
            $porcentages = $porcentagem_split_item->toArray();

            foreach ($porcentages as $key => $porcentage) {
                $index = str_replace('valor_', '', $key);

                if ($porcentagem_split_item_exception[$index . '_manual'] == 1){
                    $porcentages[$key] = 0;
                }
            }

            return $porcentages;
        } else {
            return false;
        }
    }

    private function createOrder($order_id, $customer, $items, $discount = 0, $splits){
        try {
            $order = $this->moip_merchant->orders()->setOwnId($order_id);

            foreach ($items as $key => $item) {
                if (isset($item['fk_curso']) && $item['fk_curso'] > 0){
                    $ref_product = 'CURSO ID: ' . $item['fk_curso'];
                } elseif (isset($item['fk_trilha']) && $item['fk_trilha'] > 0){
                    $ref_product = 'TRILA ID: ' . $item['fk_trilha'];
                }

                $order->addItem($item['name'], 1, $ref_product, (int)($item['value'] * 100));
            }

            $order->setDiscount($discount * 100)->setCustomer($customer);

            if (!empty($splits)){
                foreach ($splits as $key => $receiver) {
                    $order->addReceiver($receiver['id'], $receiver['type'], (int)$receiver['fixed'], (int)$receiver['percentual'], $receiver['feePayor']);
                }
            }

            $order->create();

            if ($order->getId()){
                Pedido::where('id', $order_id)->update(['id_wirecard' => $order->getId()]);
            }

            return $order;
        } catch (\Moip\Exceptions\UnautorizedException $e) {
            //StatusCode 401
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            $this->error = $e->getMessage();
        } catch (\Moip\Exceptions\ValidationException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode entre 400 e 499 (exceto 401)
            $this->error = $e->__toString();
        } catch (\Moip\Exceptions\UnexpectedException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode >= 500
            $this->error = $e->getMessage();
        }
    }

    private function existsAccountWirecard($document){
        $document = (strlen($document) > 11) ? $this->mask($document, "###.###.###-##") : $this->mask($document, "##.###.###/####-##");

        if ($this->moip_merchant->accounts()->checkExistence($document)){
            return true;
        } else {
            return false;
        }
    }

    private function addWirecardAccount($id, $access_token, $channel_id){
        $wirecardAccount = new WirecardAccount;
        $wirecardAccount->account_id = $id;
        $wirecardAccount->access_token = $access_token;
        $wirecardAccount->channel_id = $channel_id;
        $wirecardAccount->save();

        return $wirecardAccount->id;
    }

    private function addWireCardAccountId($wirecard_account_id, $type, $id){
        if ($type == 'professor' || $type == 'professor_participante'){
            Professor::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        }  elseif ($type == 'curador'){
            Curador::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'parceiro'){
            Parceiro::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'faculdade'){
            Faculdade::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'produtora'){
            Produtora::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        }
    }

    private function createCustomer($data){
        $phone = preg_replace("/[^0-9]/", "", $data->telefone_1);

        $ddd   = substr($phone, 0, 2);
        $phone = substr($phone, 2, 9);

        try {
            $customer = $this->moip_merchant->customers()->setOwnId(uniqid())
            ->setFullname($data->nome . ' ' . $data->sobre_nome)
            ->setEmail($data->email)
            ->setBirthDate($data->data_nascimento)
            ->setTaxDocument($data->cpf)
            ->setPhone($ddd, $phone)
            ->addAddress('BILLING',
                $data->logradouro, $data->numero,
                $data->bairro, $data->descricao_cidade, $data->uf_estado,
                $data->cep, 8)
            ->addAddress('SHIPPING',
                $data->logradouro, $data->numero,
                $data->bairro, $data->descricao_cidade, $data->uf_estado,
                $data->cep, 8)
            ->create();

            return $customer;
        } catch (\Moip\Exceptions\UnautorizedException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode 401
            return ['code' => 401, 'error' => $e->getMessage()];
        } catch (\Moip\Exceptions\ValidationException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode entre 400 e 499 (exceto 401)
            return ['code' => 400, 'error' => $e->__toString()];
        } catch (\Moip\Exceptions\UnexpectedException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode >= 500
            return ['code' => 500, 'error' => $e->getMessage()];
        }
    }

    public function getOrders()
    {
        if ($this->moip){
            try {
                $orders = $this->moip_merchant->orders()->getList();
                return response()->json(['success' => true, 'data' => $orders]);
            } catch (\Moip\Exceptions\UnautorizedException $e) {
                //StatusCode 401
                return response()->json(['code' => 401, 'error' => $e->getMessage()]);
            } catch (\Moip\Exceptions\ValidationException $e) {
                //StatusCode entre 400 e 499 (exceto 401)
                return response()->json(['code' => 400, 'error' => printf($e->__toString())]);
            } catch (\Moip\Exceptions\UnexpectedException $e) {
                //StatusCode >= 500
                return response()->json(['code' => 500, 'error' => $e->getMessage()]);
            }
        } else {
            return response()->json(['error' => 'A autenticação falhou...']);
        }
    }

    private function validDocument($cpf_cnpj){
        if (strlen($cpf_cnpj) > 11){
            return $this->validCNPJ($cpf_cnpj);
        } else {
            return $this->validCPF($cpf_cnpj);
        }
    }

    private function validCPF($cpf) {
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function validCNPJ($cnpj){
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    private function mask($val, $mask){
        $maskared = '';
        $k = 0;

        for($i = 0; $i<=strlen($mask)-1; $i++){
            if($mask[$i] == '#'){
                if(isset($val[$k]))
                $maskared .= $val[$k++];
            } else{
                if(isset($mask[$i]))
                $maskared .= $mask[$i];
            }
        }

        return $maskared;
    }

    public function signature(Request $request){
        $messages = [
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
            'installment.required'        => 'Número de parcelas inválida',
            'document.required'           => 'CPF ou CNPJ inválido',
            'document.ddd'                => 'DDD inválido',
            'document.phone'              => 'Telefone inválido',
        ];

        $validator = Validator::make($request->all(), [
            'signature_id'       => 'required',
            'full_name'          => 'required',
            'birth_date'         => 'required',
            'credit_card_number' => 'required|min:16',
            'cvv'                => 'required',
            'expiry_month'       => 'required|min:2|max:2',
            'expiry_year'        => 'required|min:2|max:2',
            'installment'        => 'required',
            'document'           => 'required',
            'ddd'                => 'required',
            'phone'              => 'required'
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        /* SIGNATURE METHOD INITIATED */
        return response()->json(['success' => 'Pagamento em análise...']);
    }

    private function getUserForSplit($data){
        if (!$this->error){
            $data = $data->toArray();

            $data_account['id'] = $data['id'];
            $data_account['address'] = ['street' => $data['logradouro'], 'number' => $data['numero'], 'district' => $data['bairro'], 'zipcode' => preg_replace("/[^0-9]/", "", $data['cep']), 'city' => $data['descricao_cidade'], 'state'  => $data['uf_estado'], 'country' => 'BRA'];

            $data_account['type_account']   = 'MERCHANT';
            $data_account['tos_acceptance'] = ['accepted_at' => new Datetime(), 'ip' => '', 'user_agent' => ''];

            $data_account['email'] = $data['email'];

            if (!empty($data['responsavel'])){
                $name = explode(' ', $data['responsavel']);
                $data_account['name']       = (isset($name[0])) ? $name[0] : '';
                $data_account['lastname']   = end($name);
            } else {
                $name = explode(' ', $data['nome']);
                $data_account['name']       = (isset($name[0])) ? $name[0] : '';
                $data_account['lastname']   = end($name);
            }

            $data_account['birth_data'] = $data['data_nascimento'];
            $data_account['cpf']        = preg_replace("/[^0-9]/", "", $data['cpf']);

            $phone_number = $this->getPhone($data);

            $data_account['phone'] = ['ddd' => $phone_number['ddd'], 'number' => $phone_number['number'], 'prefix' => '55'];

            if (isset($data['cnpj']) && !empty($data['cnpj'])){
                $company_name = (!empty($data['nome_fantasia'])) ? $data['nome_fantasia'] : $data['fantasia'];
                $data_account['company']            = ['name' => $company_name, 'business_name' => $data['razao_social'], 'type_document' => 'CNPJ'];
                $data_account['company']['address'] = $data_account['address'];
                $data_account['company']['cnpj']    = preg_replace("/[^0-9]/", "", $data['cnpj']);
                $data_account['company']['phone']   = $data_account['phone'];
            }

            return $data_account;
        }
    }

    private function getPhone($customer){
        if (!empty($customer['telefone_1'])){
            $number_phone = $customer['telefone_1'];
        } elseif (!empty($customer['telefone_2'])){
            $number_phone = $customer['telefone_2'];
        } elseif (!empty($customer['telefone_3'])) {
            $number_phone = $customer['telefone_3'];
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

    private function addPagamento($fk_pedido, $id_pedido_gateway, $tipo, $total, $taxa = 0, $emissor = null, $parcelas = false){
        $juros = 0;
        if ($parcelas > 0){
            $juros = $this->getRateCreditCart($parcelas);
        }

        Pagamento::create(['fk_pedido' => $fk_pedido, 'id_pedido_gateway' => $id_pedido_gateway, 'tipo' => $tipo, 'taxa' => $taxa, 'total' => $total, 'emissor' => $emissor, 'parcelas' => $parcelas, 'juros' => $juros, 'data_criacao' => new Datetime]);
    }

    /* RETORNOS WIRECARD */
    public function callback(Request $request){
        $data = $request->all();

        $order_id          = (isset($data['resource']['order']['ownId'])) ? $data['resource']['order']['ownId'] : false;
        $order_wirecard_id = (isset($data['resource']['order']['id'])) ? $data['resource']['order']['id'] : false;
        $order_status      = (isset($data['resource']['order']['status'])) ? $data['resource']['order']['status'] : false;
        $total             = (isset($data['resource']['order']['amount']['total'])) ? $data['resource']['order']['amount']['total'] / 100 : 0;

        if ($order_id){
            $log = PagamentoLog::create(['fk_pedido' => $order_id, 'enviado' => 'RETORNO AUTOMATICO', 'recebido' => json_encode($data), 'data_criacao' => date('Y-m-d H:i:s')]);
        } else {
            $log = PagamentoLog::create(['fk_pedido' => '0', 'enviado' => 'RETORNO AUTOMATICO', 'recebido' => json_encode($data), 'data_criacao' => date('Y-m-d H:i:s')]);
        }

        $validOrderWirecard = $this->validIdWirecardOrder($order_id, $order_wirecard_id);

        if ($validOrderWirecard){
            $order = $this->moip_merchant->Orders()->get($validOrderWirecard['order_wirecard_id']);

            foreach ($order->getPaymentIterator() as $payment) {
                $installment = $payment->getInstallmentCount();

                $payment = (array)$payment->getFundingInstrument();
            }

            if ($order_status == 'PAID'){
                $status_pago = 2;
                $pedido = Pedido::where('id', $order_id)->select('status', 'fk_faculdade')->first();

                $this->adicionarCursosModulosAluno($order_id, $pedido->fk_faculdade);

                if ($pedido->status != $status_pago){
                    if ($payment['method'] == 'CREDIT_CARD') {
                        $rate = ($order->getAmountFees()) ? ($order->getAmountFees() / 100) : 0;

                        $this->addPagamento($order_id, $order_wirecard_id, 'cartao', $total, $rate, $payment['creditCard']->first6, $installment);
                    } elseif ($payment['method'] == 'BOLETO') {
                        $this->addPagamento($order_id, $order_wirecard_id, 'boleto', $total);
                    } elseif ($payment['method'] == 'ONLINE_BANK_DEBIT') {
                        $this->addPagamento($order_id, $order_wirecard_id, 'debito_itau', $total);
                    }

                    Pedido::where('id', $order_id)->update(['status' => 2, 'atualizacao' => new Datetime()]);

                    $pedido_historico_id = PedidoHistorico::add(2, $order_id);

                    # EMITIR NOTAFISCAL
                    $this->issueInvoice($order_id, $order->getAmountLiquid() / 100);

                    /* DISPARA EMAIL DE CONFIRMACAO DO PEDIDO */
                    $this->sendPaidOrderMail($order_id, $pedido_historico_id, $pedido->fk_faculdade);
                }
            } elseif ($order_status == 'CANCELLED' || $order_status == 'REVERSED'){
                Pedido::where('id', $order_id)->update(['status' => 4]);

                PedidoHistorico::add(4, $order_id);
            }
        }
    }

    public function createPreferenceNotification(Request $request){
        if (isset($request['url_callback'])){
            try {
                /* CADASTRA WEBHOOK PARA NOTIFICACOES DE PAGAMENTO */
                $notification = $this->moip_merchant->notifications()->addEvent('ORDER.*')->setTarget($request['url_callback'])->create();

                $data = ['success' => 'Wirecard - Evento cadastrado no webhook!'];
            } catch (\Moip\Exceptions\UnautorizedException $e) {
                //StatusCode 401
                 $data = ['error' => $e->getMessage()];
            } catch (\Moip\Exceptions\ValidationException $e) {
                //StatusCode entre 400 e 499 (exceto 401)
                 $data = ['error' => $e->__toString()];
            } catch (\Moip\Exceptions\UnexpectedException $e) {
                //StatusCode >= 500
                 $data = ['error' => $e->getMessage()];
            }
        } else {
            $data = ['error' => 'A URL informada é inválida!', 'code' => '05071225'];
        }

        return response()->json($data);
    }

    private function validIdWirecardOrder($order_id, $order_wirecard_id){
        $order = WirecardOrder::where([['order_id', '=', $order_id], ['order_wirecard_id', '=', $order_wirecard_id]])->first();

        if (isset($order->id)){
            return $order->toArray();
        } else {
            return false;
        }
    }

    private function addWirecardOrder($order_id, $order_wirecard_id){
        WirecardOrder::create(['order_id' => $order_id, 'order_wirecard_id' => $order_wirecard_id]);
    }

    private function issueInvoice($order_id, $liquid_total){
        $nfe = Nfe::where('fk_pedido', $order_id)->first();

        if (!isset($nfe->id)){
            $order = Pedido::where('id', $order_id)->first();

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
                        'servicesAmount'  => $liquid_total,
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

                    $nfe_log = NfeLog::create(['fk_pedido' => $order_id, 'enviado' => json_encode($data_invoice), 'data_criacao' => date('Y-m-d H:i:s')]);

                    $nfe = NFe::issueInvoice($data_invoice);

                    $data_nfe = json_decode($nfe);

                    if (isset($data_nfe->id)){
                        NfeLog::whereId($nfe_log->id)->update(['nfse_id' => $data_nfe->id, 'recebido' => json_encode($data_nfe), 'data_atualizacao' => date('Y-m-d H:i:s')]);
                        
                        Nfe::create(['nfse_id' => $data_nfe->id, 'fk_pedido' => $order_id, 'enviado' => json_encode($data_invoice), 'recebido' => $nfe, 'status' => $data_nfe->flowStatus, 'data_criacao' => date('Y-m-d H:i:s')]);

                    } elseif (isset($data_nfe->message)){
                        NfeLog::whereId($nfe_log->id)->update(['recebido' => json_encode(['message' => $data_nfe->message]), 'error' => 1, 'data_atualizacao' => date('Y-m-d H:i:s')]);

                        Nfe::create(['fk_pedido' => $order_id, 'enviado' => json_encode($data_invoice), 'recebido' => $data_nfe->message, 'error' => 1, 'data_criacao' => date('Y-m-d H:i:s')]);

                        return response()->json($data_nfe->message);
                    }
                }
            }
        }
    }

    # METODO API PARA CRIACAO DE SUBCONTAS WIRECARD NO MOMENTO DO CADASTRO DO USUARIO #
    public function createAccount(Request $Request){
        $data = $Request->all();

        $types = ['professor', 'curador', 'faculdade', 'produtora', 'parceiro'];
        if (!isset($data['type']) || !in_array($data['type'], $types)){
            return response()->json(['error' => 'Tipo de usuário inválido!', 'code' => '1907191233']);
        }

        if (!isset($data['cpf']) || empty($data['cpf']) || !$this->validCPF(preg_replace("/[^0-9]/", "", $data['cpf']))){
            return response()->json(['error' => 'CPF inválido.', 'code' => '1907191413']);
        }

        $messages = [
            'name.required'             => 'Nome inválido!',
            'lastname.required'         => 'Sobrenome inválido!',
            'email.required'            => 'E-mail inválido!',
            'birth_data.required'       => 'Data de nascimento inválida!',
            'cpf.required'              => 'CPF inválido!',
            'phone.prefix.required'     => 'Prefixo do telefone é inválido!',
            'phone.ddd.required'        => 'DDD inválido!',
            'phone.number.required'     => 'Número de telefone inválido!',
            'address.street.required'   => 'Endereço: Rua inválida!',
            'address.number.required'   => 'Endereço: Número inválido!',
            'address.district.required' => 'Endereço: Bairro inválido!',
            'address.city.required'     => 'Endereço: Cidade inválida!',
            'address.state.required'    => 'Endereço: Estado inválido!',
            'address.zipcode.required'  => 'Endereço: CEP inválido',
            'address.country.required'  => 'Endereço: CODE ISO 3 inválido. (ex.: "BRA")',
        ];

        $validator = Validator::make($data, [
            'name'             => 'required',
            'lastname'         => 'required',
            'email'            => 'required',
            'birth_data'       => 'required',
            'cpf'              => 'required',
            'phone.prefix'     => 'required',
            'phone.ddd'        => 'required',
            'phone.number'     => 'required',
            'address.district' => 'required',
            'address.country'  => 'required',
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        if (isset($data['company'])){
            if (!$this->validCNPJ(preg_replace("/[^0-9]/", "", $data['company']['cnpj']))){
                return response()->json(['error' => 'CNPJ inválido.', 'code' => '1907191418']);
            }

            $messages = [
                'phone.prefix.required'     => 'Prefixo do telefone é inválido!',
                'phone.ddd.required'        => 'DDD inválido!',
                'phone.number.required'     => 'Número de telefone inválido!',
                'address.street.required'   => 'Empresa - Endereço: Rua inválida!',
                'address.number.required'   => 'Empresa - Endereço: Número inválido!',
                'address.district.required' => 'Empresa - Endereço: Bairro inválido!',
                'address.city.required'     => 'Empresa - Endereço: Cidade inválida!',
                'address.state.required'    => 'Empresa - Endereço: Estado inválido!',
                'address.zipcode.required'  => 'Empresa - Endereço: CEP inválido',
                'address.country.required'  => 'Empresa - Endereço: CODE ISO 3 inválido. (ex.: "BRA")',
            ];

            $validator = Validator::make($data, [
                'phone.prefix'     => 'required',
                'phone.ddd'        => 'required',
                'phone.number'     => 'required',
                'address.street'   => 'required',
                'address.number'   => 'required',
                'address.district' => 'required',
                'address.city'     => 'required',
                'address.state'    => 'required',
                'address.zipcode'  => 'required',
                'address.country'  => 'required',
            ], $messages);

            if ($validator->errors()){
                $errors = $validator->errors()->toArray();

                foreach ($errors as $key => $error) {
                    $this->error = $error[0];
                }
            }
        }

        if (!$this->error){
            try {
                $account = $this->moip_merchant->accounts()
                ->setName($data['name'])
                ->setLastName($data['lastname'])
                ->setEmail($data['email'])
                ->setBirthDate($data['birth_data'])
                ->setTaxDocument((string)$data['cpf'])
                ->setType($data['type_account'])
                ->setTransparentAccount(true)
                ->setPhone($data['phone']['ddd'], $data['phone']['number'], $data['phone']['prefix'])
                ->addAddress($data['address']['street'], $data['address']['number'], $data['address']['district'], $data['address']['city'], $data['address']['state'], $data['address']['zipcode'], '', $data['address']['country']);

                if (isset($data['company'])){
                    $account->setCompanyName($data['company']['name'], $data['company']['business_name'])
                    ->setCompanyPhone($data['company']['phone']['ddd'], $data['company']['phone']['number'], $data['company']['phone']['prefix'])
                    ->setCompanyTaxDocument((string)$data['company']['cnpj'])
                    ->setCompanyAddress($data['company']['address']['street'], $data['company']['address']['number'], $data['company']['address']['district'], $data['company']['address']['city'], $data['company']['address']['state'], $data['company']['address']['zipcode'], '', $data['company']['address']['country']);
                }

                $account->create();

                if ($account->getId()){
                    # ADICIONA DADOS DA CONTA WIRECARD NA BASE
                    $wirecard_account_id = $this->addWirecardAccount($account->getId(), $account->getAccessToken(), $account->getChannelId());

                    $this->addWireCardAccountId($wirecard_account_id, $data['type'], $data['reference_id']);

                    return response()->json(['success' => 'Conta criada com sucesso!']);
                } else {
                    return response()->json(['error' => 'Não foi possível criar sua conta', 'code' => '190720191241']);
                }

                return $account;
            } catch (\Moip\Exceptions\UnautorizedException $e) {
                //StatusCode 401
                return response()->json(['message' => $e->getMessage()]);
            } catch (\Moip\Exceptions\ValidationException $e) {
                //StatusCode entre 400 e 499 (exceto 401)
                return response()->json(['message' => $e->__toString()]);
            } catch (\Moip\Exceptions\UnexpectedException $e) {
                //StatusCode >= 500
                return response()->json(['message' => $e->getMessage()]);
            }
        }
    }

    # METODO API PARA CRIACAO DE SUBCONTAS WIRECARD NO MOMENTO DO CADASTRO DO USUARIO #
    public function addBankAccount(Request $Request){
        $data = $Request->all();

        $messages = [
            'moip_account_id.required' => 'moip_account_id inválido!',
            'bank_number.required' => 'Número de identificação do banco é inválido!',
            'agency_number.required' => 'Número da agência é inválido!',
            'account_number.required' => 'Número da conta é inválido!',
            'account_check_number.required' => 'Digito verificador da agência é inválido!',
            'type.required' => 'Tipo de conta é obrigatório'
        ];

        $validator = Validator::make($data, [
            'moip_account_id.required',
            'bank_number.required',
            'agency_number.required',
            'account_number.required',
            'account_check_number.required',
            'type.required',
        ]);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        $wirecard_account = WirecardAccount::select('access_token')->where('account_id', $data['moip_account_id'])->first();

        if (!isset($wirecard_account->access_token)){
            return response()->json(['error' => 'Conta Wirecard não localizada!', 'code' => '1907191635']);
        }

        $account = $this->moip_merchant->accounts()->get($data['moip_account_id']);

        try {
            $moip = $this->autentication($wirecard_account->access_token);
        } catch (\Moip\Exceptions\UnautorizedException $e) {
            //StatusCode 401
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json(['error' => 'Conta não existe ou é inválida!', 'code' => '1907191551']);
        } catch (\Moip\Exceptions\ValidationException $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            //StatusCode entre 400 e 499 (exceto 401)
            return response()->json(['error' => 'Conta não existe ou é inválida!', 'code' => '1907191552']);
        } catch (\Moip\Exceptions\UnexpectedException $e) {
            //StatusCode >= 500
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json(['error' => 'Conta não existe ou é inválida!', 'code' => '1907191553']);
        }

        try {
            $bank_account = $moip->bankaccount()
            ->setBankNumber($data['bank_number'])
            ->setAgencyNumber($data['agency_number'])
            ->setAgencyCheckNumber($data['agency_check_number'])
            ->setAccountNumber($data['account_number'])
            ->setAccountCheckNumber($data['account_check_number'])
            // CHECKING conta corrente, SAVING conta poupanca
            ->setType('CHECKING')
            ->setHolder($account->getFullName(), $account->getTaxDocumentNumber(), $account->getTaxDocumentType())
            ->create($data['moip_account_id']);

            if ($bank_account->getId()){
                return response()->json(['success' => 'Conta bancária cadastrada com sucesso!']);
            }

        } catch (\Moip\Exceptions\UnautorizedException $e) {
            //StatusCode 401
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json(['message' => $e->getMessage()]);
        } catch (\Moip\Exceptions\ValidationException $e) {
            //StatusCode entre 400 e 499 (exceto 401)
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json(['message' => $e->__toString()]);
        } catch (\Moip\Exceptions\UnexpectedException $e) {
            //StatusCode >= 500
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function getDataCustomer($user_id){
        return Aluno::join('endereco', 'alunos.fk_endereco_id', '=', 'endereco.id')
                    ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                    ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                    ->join('usuarios', 'alunos.fk_usuario_id', '=', 'usuarios.id')
                    ->where('alunos.fk_usuario_id', $user_id)->first();
    }

    public function getDataUserSplit($id, $type){
        $user = array();
        switch ($type) {
            case 'professor':
            case 'professor_participante':
                $user = Professor::join('endereco', 'professor.fk_endereco_id', '=', 'endereco.id')
                ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                ->leftJoin('conta_bancaria', 'professor.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
                ->select('*', 'professor.id AS id')
                ->where('professor.id', $id)->first();
            break;

            case 'curador':
                $user = Curador::join('endereco', 'curadores.fk_endereco_id', '=', 'endereco.id')
                ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                ->leftJoin('conta_bancaria', 'curadores.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->join('usuarios', 'curadores.fk_usuario_id', '=', 'usuarios.id')
                ->select('*', 'curadores.id AS id')
                ->where('curadores.id', $id)->first();
            break;

            case 'faculdade':
                $user = Faculdade::join('endereco', 'faculdades.fk_endereco_id', '=', 'endereco.id')
                ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                ->leftJoin('conta_bancaria', 'faculdades.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->join('usuarios', 'faculdades.fk_usuario_id', '=', 'usuarios.id')
                ->select('*', 'faculdades.id AS id')
                ->where('faculdades.id', $id)->first();
            break;

            case 'produtora':
                $user = Produtora::join('endereco', 'produtora.fk_endereco_id', '=', 'endereco.id')
                ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                ->leftJoin('conta_bancaria', 'produtora.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                ->join('usuarios', 'produtora.fk_usuario_id', '=', 'usuarios.id')
                ->select('*', 'produtora.id AS id')
                ->where('produtora.id', $id)->first();
            break;

            case 'parceiro':
                $user = Parceiro::join('endereco', 'parceiro.fk_endereco_id', '=', 'endereco.id')
                    ->join('cidades', 'endereco.fk_cidade_id', '=', 'cidades.id')
                    ->join('estados', 'endereco.fk_estado_id', '=', 'estados.id')
                    ->leftJoin('conta_bancaria', 'parceiro.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                    ->join('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
                    ->select('*', 'parceiro.id AS id')
                    ->where('parceiro.id', $id)->first();
                break;
        }

        return $user;
    }

    private function getOrder($order_id){
        $order = Pedido::where('pedidos.id', $order_id)
        ->select(['pedidos.*', 'usuarios.nome', 'usuarios.email', 'pedidos.metodo_pagamento AS tipo_pagamento', 'pedidos.pid', 'usuarios.foto', 'usuarios.fk_atualizador_id'])
        ->join('usuarios', 'usuarios.id', '=', 'pedidos.fk_usuario')
        ->first();

        if (!empty($order)){
            return $order->toArray();
        } else {
            return false;
        }
    }

    private function sendPaidOrderMail($order_id, $pedido_historico_id = 0, $idFaculdade = 7){
        $order = $this->getOrder($order_id);

        if ($order){
            $total = $order['valor_bruto'] - $order['valor_desconto'];

            $table_products = $this->getTableProducts($order['pid'], $this->getOrderItems($order), $order['foto']);
            $table_products_related = $this->getTableProductsRelated($this->getOrderItems($order));

            $EducazMail = new EducazMail($idFaculdade);

            $data = $EducazMail->confirmacaoPedido([
                'messageData' => [
                    'idPedido' => $order['pid'],
                    'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                    'nome' => $order['nome'],
                    'email' => $order['email'],
                    'linkPerfil' =>  $this->getURLFront($order['fk_atualizador_id']) . '/#/perfil',
                    'dataPedido' => strftime('%d de %B de %Y', strtotime($order['criacao'])),
                    'formaPagamento' => $this->getPaymentMethod($order['tipo_pagamento']),
                    'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                    'tabelaCursos' => $table_products,
                    'maisCursos' => $table_products_related
                ]
            ]);

            if ($pedido_historico_id > 0){
                PedidoHistorico::updateNotifyOrderHistory($pedido_historico_id);
            }
        }
    }

    private function sendPaidBankSlipOrderMail($order_id, $pedido_historico_id = 0, $idFaculdade = 7) {
        $order = $this->getOrder($order_id);

        if ($order){
            $total = $order['valor_bruto'] - $order['valor_desconto'];

            $EducazMail = new EducazMail($idFaculdade);

            $table_products = $this->getTableProducts($order['pid'], $this->getOrderItems($order), $order['foto']);

            $data = $EducazMail->confirmacaoPedidoNoBoleto([
                'messageData' => [
                    'idPedido' => $order['pid'],
                    'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                    'nome' => $order['nome'],
                    'email' => $order['email'],
                    'linkPerfil' =>  $this->getURLFront($order['fk_atualizador_id']) . '/#/perfil',
                    'dataPedido' => strftime('%d de %B de %Y', strtotime('2019-03-10 03:36:01')),
                    'formaPagamento' => $this->getPaymentMethod($order['tipo_pagamento']),
                    'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                    'tabelaCursos' => $table_products,
                ]
            ]);

            $data = $EducazMail->reenviarBoleto([
                'messageData' => [
                    'idPedido' => $order['pid'],
                    'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                    'nome' => $order['nome'],
                    'email' => $order['email'],
                    'link_boleto' => $order['link_boleto'],
                    'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                    'dataPedido' => strftime('%d de %B de %Y', strtotime($order['criacao'])),
                ]
            ]);
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

                $image_path = Url('/') . '/files/curso/imagem/' . rawurlencode($product['image']);

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

    private function getTableProductsRelated($products){
        if (!empty($products)){
            $html = '';
            foreach ($products as $key => $product) {
                $image_path = url('/') . '/files/curso/imagem/' . rawurlencode($product['image']);

                $html .= view('emails.templates.1.confirmacao_de_compra_produtos_sugeridos', ['name' => $product['name'], 'imagem' => $image_path])->render();
            }
        }

        return $html;
    }

    private function getPaymentMethod($method){
        if ($method == 'cartao'){
            return 'Cartão de crédito';
        } elseif ($method == 'boleto'){
            return 'Boleto Bancário';
        } elseif ($method == 'debito_itau'){
            return 'Débito Itaú';
        }
    }

    private function customerAlreadyNotified($fk_pedido, $fk_pedido_status){
        $history = PedidoHistoricoStatus::where(['fk_pedido' => $fk_pedido, 'fk_pedido_status' => $fk_pedido_status])->select('cliente_notificado')->orderBy('id', 'DESC')->first();

        if (!empty($history->cliente_notificado) && $history->cliente_notificado == 1){
            return true;
        } else {
            return false;
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

    public function getInstallmentFee(){
        $fee = JurosCartao::select('parcela', 'percentual', 'minimo')->get();

        return response()->json([$fee]);
    }

    public function getRateCreditCart($installments){
        $juros = JurosCartao::where('parcela', $installments)->first();

        if (isset($juros->id)){
            return $juros->percentual;
        } else {
            $this->error = ['error' => 'Não foi possível cálcular os juros para o parcelamento!', 'code' => '210920190906'];
        }
    }

    private function countUserSplit($users_split){
        $count = 0;
        foreach ($users_split as $type => $user_id) {
            $user_split = $this->getDataUserSplit($user_id, $type);

            $wirecardAccount = WirecardAccount::where('id', $user_split['wirecard_account_id'])->first();

            if (isset($user_split->wirecard_account_id) && isset($wirecardAccount->id)){
                $count = $count + 1;
            }
        }

        return $count;
    }

    private function paymentLog($errors){
        $ip = request()->ip(); //$_SERVER['REMOTE_ADDR'];
        
        $my_file = base_path() . '/app/Libraries/moip/wirecard_log.txt';
        $handle = fopen($my_file, 'a');
        $data = "\n"."[" . date("d-m-y H:i:s") ."][$ip]>" . json_encode($errors);
        fwrite($handle, $data);
    }

    public function updateValueSplitWidthTaxes($order_id, $items, $payment_method, $number_installments = 1){
        foreach ($items as $key => $item) {
            if (!empty($item['fk_trilha'])){

                unset($items[$key]);
                $items_trilha = $this->getCursosTrilha($item['fk_trilha']);
            }
        }

        if (!empty($items) && !empty($items_trilha)){
            $items = array_merge($items_trilha, $items);
        } elseif (!empty($items_trilha)){
            $items = $items_trilha;
        }

        $SubtractTaxes = new TaxasPagamento;

        foreach ($items as $key => $item) {
            $split = PedidoItemSplit::where(['fk_pedido' => $order_id, 'fk_curso' => $item['fk_curso']])->first();

            $value_splits = array();
            $total_users_split = 0;
            if (!empty($split)){
                $split = $split->toArray();

                $total_users_split = (!empty($split['porcentagem_split_professor']) && $split['porcentagem_split_professor'] > 0) ? $total_users_split + 1 : $total_users_split;
                $total_users_split = (!empty($split['porcentagem_split_professor_participante']) && $split['porcentagem_split_professor_participante'] > 0) ? $total_users_split + 1 : $total_users_split;
                $total_users_split = (!empty($split['porcentagem_split_curador']) && $split['porcentagem_split_curador'] > 0) ? $total_users_split + 1 : $total_users_split;
                $total_users_split = (!empty($split['porcentagem_split_parceiro']) && $split['porcentagem_split_parceiro'] > 0) ? $total_users_split + 1 : $total_users_split;
                $total_users_split = (!empty($split['porcentagem_split_faculdade']) && $split['porcentagem_split_faculdade'] > 0) ? $total_users_split + 1 : $total_users_split;
                $total_users_split = (!empty($split['porcentagem_split_produtora']) && $split['porcentagem_split_produtora'] > 0) ? $total_users_split + 1 : $total_users_split;

                if (!empty($split['porcentagem_split_professor']) && $split['porcentagem_split_professor'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_professor']);
                    $value_splits['valor_split_professor'] =  number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_professor'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                if (!empty($split['porcentagem_split_professor_participante']) && $split['porcentagem_split_professor_participante'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_professor_participante']);
                    $value_splits['valor_split_professor_participante'] = number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_professor_participante'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                if (!empty($split['porcentagem_split_curador']) && $split['porcentagem_split_curador'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_curador']);
                    $value_splits['valor_split_curador'] = number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_curador'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                if (!empty($split['porcentagem_split_parceiro']) && $split['porcentagem_split_parceiro'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_parceiro']);
                    $value_splits['valor_split_parceiro'] = number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_parceiro'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                if (!empty($split['porcentagem_split_produtora']) && $split['porcentagem_split_produtora'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_produtora']);
                    $value_splits['valor_split_produtora'] = number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_produtora'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                if (!empty($split['porcentagem_split_faculdade']) && $split['porcentagem_split_faculdade'] > 0){
                    $total = (($item['value'] / 100) * $split['porcentagem_split_faculdade']);
                    $value_splits['valor_split_faculdade'] = number_format($SubtractTaxes->subtractTaxes($total, $payment_method, $total_users_split, $number_installments), 2);
                    $value_splits['impostos_taxas_split_faculdade'] =  $SubtractTaxes->getTaxes($total, $payment_method, $total_users_split, $number_installments);
                }

                $total_users_split = 0;

                if (!empty($value_splits)){
                    PedidoItemSplit::where(['fk_pedido' => $order_id, 'fk_curso' => $item['fk_curso']])->update($value_splits);
                }
            }
        }
    }
}
