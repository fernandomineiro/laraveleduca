<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Helper\EducazMail;
use App\Pedido;
use App\Produtora;
use App\Professor;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RelatorioParceiroController extends Controller{

    private $tipo_perfil;
    private $tipos_perfil_parceiros = [Produtora::PERFIL_NOME,Professor::PERFIL_NOME,Curador::PERFIL_NOME];

    public function index(Request $request){
        try {
            $parametros = $this->processaRequest($request, JWTAuth::user());

            $pedidos = Pedido::relatorio_financeiro($parametros)->get();

            $data = [];

            // Somente enviar dados se for algum parceiro (Produtora, Professor, Curador)
            if (in_array($this->tipo_perfil, $this->tipos_perfil_parceiros)) {

                foreach ($pedidos as $pedido) {
                    $data[] = [
                        'ies' => $pedido->faculdade_nome,
                        'id_pedido' => $pedido->pedido_pid,
                        'nfe' => $pedido->nfse_id,
                        'data_venda' => $pedido->pedido_criacao,
                        'tipo_item' => $pedido->pedido_item_tipo,
                        'nome_item' => $pedido->pedido_item_nome,
                        'forma_pgto' => $pedido->pagamento_tipo,
                        'total_bruto' => $pedido->pedido_valor_bruto,
                        'total_impostos' => $pedido->pedido_valor_imposto,
                        'total_liquido' => $pedido->pedido_valor_liquido
                    ];
                }

            }


            return response()->json([
                'items' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
    
    private function processaRequest($request, $usuario = null){
        try {
            $parametros = [];
            $parametros['orderby'] = 'pedidos.id';
            $parametros['sort'] = 'DESC';

            if ($request->get('orderby') && $request->get('sort')) {
                $parametros['orderby'] = $request->get('orderby');
                $parametros['sort'] = $request->get('sort');
            }

            if ($request->has('pedidos_status') && !empty($request->get('pedidos_status'))) {
                $parametros['pedidos_status'] = $request->get('pedidos_status');
            }

            if (!is_null($usuario)) {

                // Produtora
                if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Produtora::ID_PERFIL) {
                    $this->tipo_perfil = Produtora::PERFIL_NOME;
                    $produtora = Produtora::where('fk_usuario_id', '=', $usuario->id)->first();
                    $parametros['produtora_id'] = !is_null($produtora) ? $produtora->id : null;
                }

                // Professor
                if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Professor::ID_PERFIL) {
                    $this->tipo_perfil = Professor::PERFIL_NOME;
                    $professor = Professor::where('fk_usuario_id', '=', $usuario->id)->first();
                    $parametros['professor_id'] = !is_null($professor) ? $professor->id : null;
                }

                // Curador
                if (!is_null($usuario->fk_perfil) && $usuario->fk_perfil == Curador::ID_PERFIL) {
                    $this->tipo_perfil = Curador::PERFIL_NOME;
                    $curador = Curador::where('fk_usuario_id', '=', $usuario->id)->first();
                    $parametros['curador_id'] = !is_null($curador) ? $curador->id : null;
                }

            }

            if ($request->has('data_compra') && !empty($request->get('data_compra'))) {
                $explode = explode("-", $request->get('data_compra'));
                $parametros['data_compra'] = [Carbon::createFromFormat('d/m/Y', trim($explode[0]))->format('Y-m-d'), Carbon::createFromFormat('d/m/Y', trim($explode[1]))->format('Y-m-d')];
            } else {
                $parametros['data_compra'] = [Carbon::today()->subDay(30)->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            }

            return $parametros;
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
    
}
