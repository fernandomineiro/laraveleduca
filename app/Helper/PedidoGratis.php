<?php

namespace App\Helper;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Pedido;
use App\PedidoItem;
use App\Aluno;
use App\Helper\PedidoHistorico;
use App\Helper\PedidoEmail;
use App\TrilhaCurso;
use App\Http\Controllers\API\CursoModuloConclusaoController;

class PedidoGratis {
    public function release($order_id, $faculdade_id){
        $this->updateOrderStatus($order_id, 2);
        
        PedidoHistorico::add(2, $order_id);

        $this->adicionarCursosModulosAluno($order_id, $faculdade_id);

        $PedidoEmail = new PedidoEmail;
        $PedidoEmail->sendPaidOrderMail($order_id);
    }

    private function updateOrderStatus($order_id, $pedido_status_id){
        return Pedido::where('id', $order_id)->update(['status' => $pedido_status_id, 'metodo_pagamento' => 'gratis']);
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
}
