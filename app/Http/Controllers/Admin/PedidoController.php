<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

use App\Faculdade;
use App\Usuario;
use App\Pedido;
use App\PedidoItem;
use App\PedidoStatus;
use App\PedidoHistoricoStatus;
use App\Nfe;
use App\Pagamento;
use App\Helper\PedidoEmail;
use App\Helper\EducazMail;

use \Session;
use \Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PedidoController extends Controller
{

    /**
     * Index da pagina pedido
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }
        
        return view($this->module_view . '.index', $this->arrayViewData);
    }

    /* Busca Alunos: Datatable as a service */
    public function getMultiFilterSelectDataPedido(Request $request) {
        //$pedidos = Pedido::query();
        $pedidos = Pedido::query()
            ->select([
                'pedidos.*',
                'usuarios.nome as usuario',
                'usuarios.email as email',
                'pedidos_status.titulo as status_titulo',
                'pedidos_status.cor as status_cor',
                'alunos.cpf as cpf',
                'alunos.identidade as rg',
                'alunos.telefone_1 as telefone',
                'pagamento.tipo as forma_pagamento',
                'faculdades.fantasia as faculdade',
                'pedidos_status.titulo as status_titulo'
            ])
            ->join('faculdades', 'faculdades.id', '=', 'pedidos.fk_faculdade')
            ->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')
            ->leftjoin('pagamento', 'pagamento.fk_pedido', '=', 'pedidos.id')
            ->join('alunos', 'pedidos.fk_usuario', '=', 'alunos.fk_usuario_id')
            ->join('pedidos_status', 'pedidos.status', '=', 'pedidos_status.id')
        ;


        $datatables =  Datatables::of($pedidos)
            ->editColumn('formato', function ($model) {
                $item_lista = null;
                $items = $model->items;

                foreach ($items as $item) {
                    if(!empty($item->evento)) {
                        $item_lista .= ' Evento' ;
                    } elseif (!empty($item->curso)) {
                        $item_lista .= ' Curso';
                    } elseif (!empty($item->trilha)) {
                        $item_lista .= ' Trilha';
                    } elseif (!empty($item->assinatura)) {
                        $item_lista .= ' Assinatura';
                    }
                }

                return $item_lista;
            })
            ->editColumn('lista_cursos', function ($model) {
                $item_lista = null;
                $items = $model->items;

                foreach ($items as $item) {
                    if (!empty($item->evento)) {
                        $item_lista .= ' __ ' . $item->evento->titulo;
                    } elseif(!empty($item->curso->titulo)) {
                        $item_lista .= ' __ ' . $item->curso->titulo ;
                    } elseif (!empty($item->trilha->titulo)) {
                        $item_lista .= ' __ ' . $item->trilha->titulo;
                    } elseif (!empty($item->assinatura->titulo)) {
                        $item_lista .= ' __ ' . $item->assinatura->titulo;
                    }
                }

                return $item_lista;
            });

        return $datatables->make(true);
    }


    public function editar($id)
    {
        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['pedido'] = Pedido::findOrFail($id);

        $usuario = Usuario::findOrFail($this->arrayViewData['pedido']->fk_usuario);
        $this->arrayViewData['aluno'] = $usuario->aluno;

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '!=', 0)->pluck('fantasia', 'id');
        $this->arrayViewData['historico_status'] = PedidoHistoricoStatus::where('fk_pedido', $id)->where('status', '=', 1)->get();
        $this->arrayViewData['lista_status'] = PedidoStatus::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_items'] = PedidoItem::where('fk_pedido', $id)->get();
        $this->arrayViewData['pedido']->criacao = empty($this->arrayViewData['pedido']->criacao) ? '': Carbon::createFromFormat('Y-m-d H:i:s', $this->arrayViewData['pedido']->criacao)->format('d/m/Y');
        $this->arrayViewData['pedido']->metodo_pagamento = $this->getPaymentMethod($this->arrayViewData['pedido']->metodo_pagamento);
        $this->arrayViewData['nfe'] = (Nfe::where('fk_pedido', $id)->where('status', 'Issued')->first()) ? true : false;

        return view($this->module_view . '.formulario', $this->arrayViewData);
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $pedido = Pedido::findOrFail($id);
        
        if ($request->input('status') == 2 && isset($request['notificar'])){
            $PedidoEmail = new PedidoEmail;
            $PedidoEmail->sendPaidOrderMail($id);

            Session::flash('mensagem_sucesso', 'Usuário notificado!');
        }

        if ($pedido->status != $request->input('status')) {

            $pedido->status = $request->input('status');
            $dados_inserir_historico = [
                'data_inclusao' => date('Y-m-d H:i:s'),
                'fk_pedido_status' => $pedido->status,
                'fk_pedido' => $id,
                'status' => '1'
            ];

            $resultado = PedidoHistoricoStatus::create($dados_inserir_historico);

            if ($resultado) {
                $pedido = $this->insertAuditData($pedido, false);

                if ($pedido->update()) {
                    Session::flash('mensagem_sucesso', $this->msgUpdate);
                } else {
                    Session::flash('mensagem_erro', $this->msgUpdateErro);
                }
            }
        } else {
            Session::flash('mensagem_erro', 'Mesmo Status!');
        }

        return Redirect::back();
    }

    public function incluir()
    {
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['status'] = \App\PedidoStatus::all();

        return view('pedido.incluir', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        $user = Session::get('user.logged');

        //Validando usuario e se tem permissao, caso tenha seta as variaveis para a rota
        //Redirecionamento caso o usuario nao tenha permissao ou nao esteja logado
        if (!$this->validateAccess($user)) {
            return redirect()->route($this->redirecTo);
        }

        $dados = $request->all();
        $dados = $this->insertAuditData($dados);

        $pedido = new Pedido($dados);
        $pedido->fk_usuario = $user->id;

        $pedido->save();
        $pedido->pid = date('dmY') . '-' . $pedido->id . '-' . $user->id;
        $pedido->save();

        return redirect()->route('admin.pedido');
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Pedido::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return redirect('/admin/pedido/' . $id . '/index');
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }

    public function reenviarBoleto($fk_pedido){
        $pedido = Pedido::select(
            'pedidos.id', 'pedidos.pid', 'pedidos.link_boleto', 'pedidos.valor_desconto', 'pedidos.valor_bruto',
            'pedidos.criacao', 'usuarios.email', 'usuarios.nome', 'pedidos.fk_faculdade')
        ->where('pedidos.id', $fk_pedido)->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')->first();

        if (isset($pedido->id)){
            $EducazMail = new EducazMail($pedido->fk_faculdade);

            $total = $pedido->valor_bruto - $pedido->valor_desconto;

            $data = $EducazMail->reenviarBoleto([
                'messageData' => [
                    'idPedido' => $pedido->pid,
                    'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                    'nome' => $pedido->nome,
                    'email' => $pedido->email,
                    'link_boleto' => $pedido->link_boleto,
                    'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                    'dataPedido' => strftime('%d de %B de %Y', strtotime($pedido->criacao)),
                ]
            ]);

            return response()->json(['success' => 'Boleto reenviado para o cliente!']);            
        } else {
            return response()->json(['error' => 'Não foi possível notificar o cliente', 'code' => '201910121335']);
        }
    }

    public function enviarComprovantePagamento($fk_pedido){
        $pedido = Pedido::select('pedidos.id', 'pedidos.pid', 'pedidos.link_boleto', 'pedidos.valor_desconto',
            'pedidos.valor_bruto', 'pedidos.criacao', 'usuarios.email', 'usuarios.nome', 'pedidos.fk_faculdade')
        ->where('pedidos.id', $fk_pedido)->join('usuarios', 'pedidos.fk_usuario', '=', 'usuarios.id')->first();

        $pagamento = Pagamento::where('fk_pedido', $fk_pedido)->orderBy('id', 'DESC')->first();

        if (isset($pagamento->id)){
            $EducazMail = new EducazMail($pedido->fk_faculdade);

            $total = $pedido->valor_bruto - $pedido->valor_desconto;

            $data['messageData'] = [
                'idPedido' => $pedido->pid,
                'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                'nome' => $pedido->nome,
                'email' => $pedido->email,
                'metodo_pagamento' => $this->getPaymentMethod($pagamento->tipo),
                'totalPedido' => 'R$ ' . number_format($total, 2, ',', '.'),
                'dataPagamento' => strftime('%d de %B de %Y', strtotime($pagamento->data_criacao)),
            ];

            if ($pagamento->tipo == 'cartao'){
                $data['messageData']['infoCartao'] = ' (cartão iniciado com ' . $pagamento->emissor . ')';
            }

            if ($pagamento->tipo == 'cartao' && $pagamento->parcelas > 1){
                $data['messageData']['infoParcelamento'] = ' (Parcelado em ' . $pagamento->parcelas .' vezes.)';
            }

            $email = $EducazMail->comprovantePagamento($data);

            return response()->json(['success' => 'Comprovante enviado para o cliente!']);            
        } else {
            return response()->json(['error' => 'Não foi possível enviar o comprovante para o cliente.', 'code' => '201910121850']);
        }
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
}
