<?php

namespace App\Helper;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Pedido;
use App\Faculdade;
use App\Voucher;
use App\PedidoHistoricoStatus;

class PedidoHistorico {
    static function add($fk_pedido_status, $fk_pedido){
        $history = PedidoHistoricoStatus::create(['fk_pedido_status' => $fk_pedido_status, 'fk_pedido' => $fk_pedido, 'status' => 1, 'data_inclusao' => date('Y-m-d H:i:s')]);

        if (isset($history->id)){
            return $history->id;
        } else {
            return false;
        }
    }

    static function updateNotifyOrderHistory($pedidos_historico_status_id){
        $history = PedidoHistoricoStatus::where('id', $pedidos_historico_status_id)->update(['cliente_notificado' => 1]);

        if (isset($history->id)){
            return $history->id;
        } else {
            return false;
        }
    }
}
