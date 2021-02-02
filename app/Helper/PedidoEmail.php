<?php

namespace App\Helper;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Pedido;
use App\Faculdade;
use App\Voucher;

class PedidoEmail {
    public function sendPaidOrderMail($order_id){
        $order = $this->getOrder($order_id);

        if ($order){
            $total = $order['valor_bruto'] - $order['valor_desconto'];

            $table_products = $this->getTableProducts($order['pid'], $this->getOrderItems($order), $order['foto']);
            $table_products_related = $this->getTableProductsRelated($this->getOrderItems($order));

            $EducazMail = new EducazMail($order['fk_faculdade']);

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
        }
    }

    private function sendPaidBankSlipOrderMail($order_id, $pedido_historico_id = 0){
        $order = $this->getOrder($order_id);

        if ($order){
            $total = $order['valor_bruto'] - $order['valor_desconto'];

            $EducazMail = new EducazMail($order->fk_faculdade);

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
        }
    }

    private function getTableProducts($pid, $products, $image_user) {
        $html = '';
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $voucher_url = '';
                $print_voucher_url = '';
                # VERIFICA SE CURSO E DO TIPO HIBRITOS 4 OU PRESENCIAIS 2
                if (!empty($product['fk_cursos_tipo']) && in_array($product['fk_cursos_tipo'], [2, 4])){
                    $file_name = $pid . '-' . 'curso' . '-' . $product['pedido_item_id'];

                    Voucher::getVoucher(Url('/'), $pid, 'curso', $product['pedido_item_id']);

                    $voucher_url = Url('api/voucher-pdf/' . $pid . '/curso/' . $product['pedido_item_id']);
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

    private function getTableProductsRelated($products) {
        $html = '';
        if (!empty($products)) {
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

    private function addOrderHistory($fk_pedido_status, $fk_pedido){
        $history = PedidoHistoricoStatus::create(['fk_pedido_status' => $fk_pedido_status, 'fk_pedido' => $fk_pedido, 'status' => 1, 'data_inclusao' => date('Y-m-d H:i:s')]);

        if (isset($history->id)){
            return $history->id;
        } else {
            return false;
        }
    }

    private function updateNotifyOrderHistory($id){
        $history = PedidoHistoricoStatus::where('id', $id)->update(['cliente_notificado' => 1]);

        if (isset($history->id)){
            return $history->id;
        } else {
            return false;
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
}
