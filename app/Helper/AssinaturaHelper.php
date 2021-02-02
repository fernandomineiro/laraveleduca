<?php

namespace App\Helper;

use App\UsuariosAssinaturasHistorico;
use App\UsuarioAssinatura;
use DB;

class AssinaturaHelper {
    public function atualizarAssinantesAtivos(){
        $ativos = $this->getAssinatesAtivos();
        
        if (isset($ativos['total']) && $ativos['total'] > 0){
            UsuariosAssinaturasHistorico::updateOrInsert(['mes' => (int)date('m'), 'ano' => date('Y'), 'tipo' => 'ativos'],
            ['total' => $ativos['total']]);
        }
    }

    private function getAssinatesAtivos(){
        $query = UsuarioAssinatura::select(DB::raw('COUNT(*) as total'))
        ->join('pedidos', 'pedidos.id', '=', 'usuarios_assinaturas.fk_pedido')
        ->where('usuarios_assinaturas.status', 1) # ASSINATURA ATIVA
        ->where('usuarios_assinaturas.renovacao_cancelada', 0) # NAO TEVE A RENOVACAO CANCELADA
        ->where('pedidos.status', 2); # PEDIDO PAGO

        return $query->first();
    } 
}
