<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\TipoPagamento;
use App\Assinatura;
use App\Trilha;
use App\TipoAssinatura;
use App\Professor;
use App\Curador;
use App\Parceiro;
use App\ViewUsuariosProdutoras;
use App\Produtora;
use App\Faculdade;
use App\Usuario;
use App\Pedido;
use App\PedidoItem;
use App\Repasse;
use App\ContaBancaria;
use App\Banco;

use Moip\Moip;
use Moip\Auth\Connect;
use Moip\Auth\BasicAuth;
use Moip\Auth\OAuth;
use App\WirecardAccount;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\DB;

class RepasseController extends Controller {
    private $moip;
    private $moip_merchant;
    private $error = false;

    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['usuarios'] = $this->getUsuarios();

        return view('repasse.lista', $this->arrayViewData);
    }

    public function detalhes($usuario_id){
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['usuario'] = $this->getUsuario($usuario_id);

        $this->arrayViewData['extrato']['valor_indisponivel'] = 0;
        $this->arrayViewData['extrato']['valor_futuro']       = 0;
        $this->arrayViewData['extrato']['valor_disponivel']   = 0;

        $this->arrayViewData['lista_bancos'] = Banco::all()->where('status', 1)->pluck('titulo', 'id');

        $this->arrayViewData['repasses'] = $this->getRepasses($usuario_id);
        $this->arrayViewData['total_repasse'] = $this->getTotalRepasses($usuario_id);

        $this->arrayViewData['pedidos'] = false;

        $this->arrayViewData['lista_bancos'] = Banco::all()->where('status', 1)->pluck('titulo', 'id');

        switch ($this->arrayViewData['usuario']['fk_perfil']) {
            case '1':
                $this->arrayViewData['tipo'] = 'professor';

                $professor = Professor::select('usuarios.id', 'professor.id AS fk_professor', 'usuarios.status', 'wirecard_account.access_token',
                'professor.fk_conta_bancaria_id', 'professor.nome', 'professor.sobrenome')
                ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
                ->join('wirecard_account', 'professor.wirecard_account_id', '=', 'wirecard_account.id')
                ->where('professor.fk_usuario_id', $this->arrayViewData['usuario']['id'])
                ->where('usuarios.fk_perfil', 1)
                ->first();

                $this->arrayViewData['usuario']['nome'] = $professor['nome'] . ' ' . $professor['sobrenome'];

                if (!empty($professor->access_token)){
                    $moip = $this->autentication($professor->access_token);

                    $pedidos = $this->getPedidos($professor->fk_professor, 'professor');
                    $this->arrayViewData['objConta'] = $this->getContaBancaria($professor->fk_professor, 'professor');

                    $pedidos_wirecard = array();
                    foreach ($pedidos as $key => $pedido) {
                        if (!empty($pedido['id_wirecard'])){
                            try {
                                $pedido_wirecard = $moip->orders()->get($pedido['id_wirecard']);

                                $pedidos_wirecard[$pedido['id_wirecard']]['valor_bruto'] = 'R$ ' . number_format( $pedido_wirecard->getAmountTotal() / 100 , 2, ',', '.');
                                $pedidos_wirecard[$pedido['id_wirecard']]['pid']         = $pedido['pid'];
                                $pedidos_wirecard[$pedido['id_wirecard']]['produtos']    = $this->getPedidosItens($pedido['id']);
                                $pedidos_wirecard[$pedido['id_wirecard']]['data']        = $pedido['criacao'];
                            } catch (\Moip\Exceptions\UnautorizedException $e) {
                                //StatusCode 401
                                $this->error = $e->getMessage();
                            } catch (\Moip\Exceptions\ValidationException $e) {
                                //StatusCode entre 400 e 499 (exceto 401)
                                $this->error = $e->__toString();
                            } catch (\Moip\Exceptions\UnexpectedException $e) {
                                //StatusCode >= 500
                                $this->error = $e->getMessage();
                            }
                        }
                    }

                    $this->arrayViewData['pedidos'] = $pedidos_wirecard;
                    $this->arrayViewData['extrato'] = $this->getExtrato($professor->access_token);
                }

            break;
            case '4':
                $this->arrayViewData['tipo'] = 'curador';

                $curador = Curador::select('usuarios.id', 'curadores.id AS fk_curador', 'usuarios.status', 'wirecard_account.access_token',
                'curadores.fk_conta_bancaria_id', 'curadores.titular_curador')
                ->join('usuarios', 'curadores.fk_usuario_id', '=', 'usuarios.id')
                ->join('wirecard_account', 'curadores.wirecard_account_id', '=', 'wirecard_account.id')
                ->where('curadores.fk_usuario_id', $this->arrayViewData['usuario']['id'])
                ->where('usuarios.fk_perfil', 4)
                ->first();

                $this->arrayViewData['usuario']['nome'] = $curador['titular_curador'];

                if (!empty($curador->access_token)){
                    $moip = $this->autentication($curador->access_token);

                    $pedidos = $this->getPedidos($curador->fk_curador, 'curador');
                    $this->arrayViewData['objConta'] = $this->getContaBancaria($curador->fk_curador, 'curador');

                    $pedidos_wirecard = array();
                    foreach ($pedidos as $key => $pedido) {
                        if (!empty($pedido['id_wirecard'])){
                            try {
                                $pedido_wirecard = $moip->orders()->get($pedido['id_wirecard']);

                                $pedidos_wirecard[$pedido['id_wirecard']]['valor_bruto'] = 'R$ ' . number_format( $pedido_wirecard->getAmountTotal() / 100 , 2, ',', '.');
                                $pedidos_wirecard[$pedido['id_wirecard']]['pid']         = $pedido['pid'];
                                $pedidos_wirecard[$pedido['id_wirecard']]['produtos']    = $this->getPedidosItens($pedido['id']);
                                $pedidos_wirecard[$pedido['id_wirecard']]['data']        = $pedido['criacao'];
                            } catch (\Moip\Exceptions\UnautorizedException $e) {
                                //StatusCode 401
                                $this->error = $e->getMessage();
                            } catch (\Moip\Exceptions\ValidationException $e) {
                                //StatusCode entre 400 e 499 (exceto 401)
                                $this->error = $e->__toString();
                            } catch (\Moip\Exceptions\UnexpectedException $e) {
                                //StatusCode >= 500
                                $this->error = $e->getMessage();
                            }
                        }
                    }

                    $this->arrayViewData['pedidos'] = $pedidos_wirecard;
                    $this->arrayViewData['extrato'] = $this->getExtrato($curador->access_token);
                }

            break;
            case '15':
                $this->arrayViewData['tipo'] = 'faculdade';

                $faculdade = Faculdade::select('usuarios.id', 'faculdades.id AS fk_faculdade', 'usuarios.status', 'wirecard_account.access_token',
                'faculdades.fk_conta_bancaria_id', 'faculdades.fantasia')
                ->join('usuarios', 'faculdades.fk_usuario_id', '=', 'usuarios.id')
                ->join('wirecard_account', 'faculdades.wirecard_account_id', '=', 'wirecard_account.id')
                ->where('faculdades.fk_usuario_id', $this->arrayViewData['usuario']['id'])
                ->where('usuarios.fk_perfil', 15)
                ->first();

                $this->arrayViewData['usuario']['nome'] = $faculdade['fantasia'];

                if (!empty($faculdade->access_token)){
                    $moip = $this->autentication($faculdade->access_token);

                    $pedidos = $this->getPedidos($faculdade->fk_faculdade, 'faculdade');
                    $this->arrayViewData['objConta'] = $this->getContaBancaria($faculdade->fk_faculdade, 'faculdade');

                    $pedidos_wirecard = array();
                    foreach ($pedidos as $key => $pedido) {
                        if (!empty($pedido['id_wirecard'])){
                            try {
                                $pedido_wirecard = $moip->orders()->get($pedido['id_wirecard']);

                                $pedidos_wirecard[$pedido['id_wirecard']]['valor_bruto'] = 'R$ ' . number_format( $pedido_wirecard->getAmountTotal() / 100 , 2, ',', '.');
                                $pedidos_wirecard[$pedido['id_wirecard']]['pid']         = $pedido['pid'];
                                $pedidos_wirecard[$pedido['id_wirecard']]['produtos']    = $this->getPedidosItens($pedido['id']);
                                $pedidos_wirecard[$pedido['id_wirecard']]['data']        = $pedido['criacao'];
                            } catch (\Moip\Exceptions\UnautorizedException $e) {
                                //StatusCode 401
                                $this->error = $e->getMessage();
                            } catch (\Moip\Exceptions\ValidationException $e) {
                                //StatusCode entre 400 e 499 (exceto 401)
                                $this->error = $e->__toString();
                            } catch (\Moip\Exceptions\UnexpectedException $e) {
                                //StatusCode >= 500
                                $this->error = $e->getMessage();
                            }
                        }
                    }

                    $this->arrayViewData['pedidos'] = $pedidos_wirecard;
                    $this->arrayViewData['extrato'] = $this->getExtrato($faculdade->access_token);
                }
            break;
            case '5':
                $this->arrayViewData['tipo'] = 'produtora';

                $produtora = DB::table('produtora')->select('usuarios.id', 'produtora.id AS fk_produtora', 'usuarios.status', 'wirecard_account.access_token',
                'produtora.fk_conta_bancaria_id', 'produtora.fantasia')
                ->join('usuarios', 'produtora.fk_usuario_id', '=', 'usuarios.id')
                ->join('wirecard_account', 'produtora.wirecard_account_id', '=', 'wirecard_account.id')
                ->where('produtora.fk_usuario_id', $this->arrayViewData['usuario']['id'])
                ->where('usuarios.fk_perfil', 5)
                ->first();

                $this->arrayViewData['usuario']['nome'] = $produtora->fantasia;

                if (!empty($produtora->access_token)){
                    $moip = $this->autentication($produtora->access_token);

                    $pedidos = $this->getPedidos($produtora->fk_produtora, 'produtora');

                    $this->arrayViewData['objConta'] = $this->getContaBancaria($produtora->fk_produtora, 'produtora');

                    $pedidos_wirecard = array();
                    foreach ($pedidos as $key => $pedido) {
                        if (!empty($pedido['id_wirecard'])){
                            try {
                                $pedido_wirecard = $moip->orders()->get($pedido['id_wirecard']);

                                $pedidos_wirecard[$pedido['id_wirecard']]['valor_bruto'] = 'R$ ' . number_format( $pedido_wirecard->getAmountTotal() / 100 , 2, ',', '.');
                                $pedidos_wirecard[$pedido['id_wirecard']]['pid']         = $pedido['pid'];
                                $pedidos_wirecard[$pedido['id_wirecard']]['produtos']    = $this->getPedidosItens($pedido['id']);
                                $pedidos_wirecard[$pedido['id_wirecard']]['data']        = $pedido['criacao'];
                            } catch (\Moip\Exceptions\UnautorizedException $e) {
                                //StatusCode 401
                                $this->error = $e->getMessage();
                            } catch (\Moip\Exceptions\ValidationException $e) {
                                //StatusCode entre 400 e 499 (exceto 401)
                                $this->error = $e->__toString();
                            } catch (\Moip\Exceptions\UnexpectedException $e) {
                                //StatusCode >= 500
                                $this->error = $e->getMessage();
                            }
                        }
                    }

                    $this->arrayViewData['pedidos'] = $pedidos_wirecard;
                    $this->arrayViewData['extrato'] = $this->getExtrato($produtora->access_token);
                }
            break;
        }

        return view('repasse.detalhes', $this->arrayViewData);
    }

    private function getUsuario($id){
        $usuario = Usuario::select(['usuarios.*', 'usuarios_perfil.titulo AS perfil'])->where('usuarios.id', $id)->join('usuarios_perfil', 'usuarios.fk_perfil', '=', 'usuarios_perfil.id')->first();

        return $usuario->toArray();
    }

    private function getUsuarios(){
        $professores = Professor::select('usuarios.id', 'usuarios.nome', 'usuarios.status')
        ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
        ->where('fk_perfil', 1)->where('wirecard_account_id', '>', '0')
        ->get();

        $professores = $this->prepareDataUser($professores, 'professor');

        $curadores = Curador::select('usuarios.id', 'usuarios.nome', 'usuarios.status')
        ->join('usuarios', 'curadores.fk_usuario_id', '=', 'usuarios.id')
        ->where('fk_perfil', 4)->where('wirecard_account_id', '>', '0')
        ->get();

        $curadores = $this->prepareDataUser($curadores, 'curador');

        /* QUANDO DESENVOLVIDO USUARIO AINDA NAO TINHA PERFIL DEFINIDO */
        $parceiros = Parceiro::select('usuarios.id', 'usuarios.nome', 'usuarios.status')
        ->join('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
        ->get();

        $parceiros = $this->prepareDataUser($parceiros, 'parceiro');

        $produtoras = DB::table('produtora')->select('usuarios.id', 'usuarios.nome', 'usuarios.status')
        ->join('usuarios', 'produtora.fk_usuario_id', '=', 'usuarios.id')
        ->where('fk_perfil', 5)
        ->where('wirecard_account_id', '>', 0)
        ->get();

        $produtoras = $this->prepareDataUser($produtoras, 'produtora');

        $faculdades = Faculdade::select('usuarios.id', 'usuarios.nome', 'usuarios.status')
        ->join('usuarios', 'faculdades.fk_usuario_id', '=', 'usuarios.id')
        ->where('fk_perfil', 15)
        ->where('wirecard_account_id', '>', '0')
        ->get();

        $faculdades = $this->prepareDataUser($faculdades, 'faculdade');

        $users_split = array_merge($professores, $curadores, $parceiros, $produtoras, $faculdades);

        return $users_split;
    }

    private function prepareDataUser($users, $type){
        if (!empty($users)){
            $users_split = array();
            $i = 0;
            foreach ($users as $key => $user) {
                $users_split[$i]['id']     = $user->id;
                $users_split[$i]['nome']   = $user->nome;
                $users_split[$i]['status'] = (isset($user->status) && $user->status == 1) ? 'Ativo' : 'Inativo';
                $users_split[$i]['tipo']   = strtoupper($type);

                $i++;
            }

            return $users_split;
        }
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

    private function getPedidos($id, $type){
        switch ($type) {
            case 'professor':
                $pedidos = Pedido::select('pedidos.id', 'pedidos.id_wirecard', 'cursos.fk_professor', 'pedidos.pid', 'pedidos.criacao')
                ->where(['cursos.fk_professor' => $id, 'pedidos.status' => 2])->whereNotNull('pedidos.id_wirecard')
                ->join('pedidos_item_split', 'pedidos_item_split.fk_pedido', '=', 'pedidos.id')
                ->join('cursos', 'cursos.id', '=', 'pedidos_item_split.fk_curso')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();
            break;
            case 'curador':
                $pedidos = Pedido::select('pedidos.id', 'pedidos.id_wirecard', 'cursos.fk_curador', 'pedidos.pid', 'pedidos.criacao')
                ->where(['cursos.fk_curador' => $id, 'pedidos.status' => 2])->whereNotNull('pedidos.id_wirecard')
                ->join('pedidos_item_split', 'pedidos_item_split.fk_pedido', '=', 'pedidos.id')
                ->join('cursos', 'cursos.id', '=', 'pedidos_item_split.fk_curso')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();
            break;
            case 'produtora':
                $pedidos = Pedido::select('pedidos.id', 'pedidos.id_wirecard', 'cursos.fk_faculdade', 'pedidos.pid', 'pedidos.criacao')
                ->where(['cursos.fk_faculdade' => $id, 'pedidos.status' => 2])->whereNotNull('pedidos.id_wirecard')
                ->join('pedidos_item_split', 'pedidos_item_split.fk_pedido', '=', 'pedidos.id')
                ->join('cursos', 'cursos.id', '=', 'pedidos_item_split.fk_curso')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();
            break;
            case 'faculdade':
                $pedidos = Pedido::select('pedidos.id', 'pedidos.id_wirecard', 'cursos.fk_faculdade', 'pedidos.pid', 'pedidos.criacao')
                ->where(['cursos.fk_faculdade' => $id, 'pedidos.status' => 2])->whereNotNull('pedidos.id_wirecard')
                ->join('pedidos_item_split', 'pedidos_item_split.fk_pedido', '=', 'pedidos.id')
                ->join('cursos', 'cursos.id', '=', 'pedidos_item_split.fk_curso')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();
            break;
        }

        if (!empty($pedidos)){
            return $pedidos->toArray();
        } else {
            return false;
        }
    }

    private function getPedidosItens($fk_pedido){
        $pedidos_items = PedidoItem::select('cursos.titulo')->leftJoin('cursos', 'cursos.id', '=', 'pedidos_item.fk_curso')->where('pedidos_item.fk_pedido', $fk_pedido)->get();

        if (!empty($pedidos_items)){
            return $pedidos_items->toArray();
        } else {
            return array();
        }
    }

    private function getContaBancaria($id, $type){
        $conta = false;
        switch ($type) {
            case 'professor':
                $conta = ContaBancaria::select('conta_bancaria.*')
                       ->join('professor', 'professor.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                       ->where('professor.id', '=', $id)
                       ->first();
            break;
            case 'curador':
                $conta = ContaBancaria::select('conta_bancaria.*')
                       ->join('curadores', 'curadores.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                       ->where('curadores.id', '=', $id)
                       ->first();
            break;
            case 'produtora':
            $conta = ContaBancaria::select('conta_bancaria.*')
                   ->join('produtora', 'produtora.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                   ->where('produtora.id', '=', $id)
                   ->first();
            break;
            case 'faculdade':
            $conta = ContaBancaria::select('conta_bancaria.*')
                   ->join('faculdades', 'faculdades.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
                   ->where('faculdades.id', '=', $id)
                   ->first();
            break;
        }

        return $conta;
    }

    private function getExtrato($access_token){
        $balances = $this->autentication($access_token)->balances()->get();

        $data = array();
        if ($balances->getUnavailable()){
            $data['valor_indisponivel'] = 'R$ ' . number_format( $balances->getUnavailable()[0]->amount / 100 , 2, ',', '.');
        }

        if ($balances->getFuture()){
            $data['valor_futuro'] = 'R$ ' . number_format( $balances->getFuture()[0]->amount / 100 , 2, ',', '.');
        }

        if ($balances->getCurrent()){
            $data['valor_disponivel'] = 'R$ ' . number_format( $balances->getCurrent()[0]->amount / 100 , 2, ',', '.');
        }

        return $data;
    }

    public function getRepasses($fk_usuario){
        $repasses = Repasse::select('valor', 'criacao')->where('fk_usuario', $fk_usuario)->limit(10)->orderBy('id', 'DESC')->get();

        if (!empty($repasses)){
            return $repasses->toArray();
        } else {
            return false;
        }
    }

    private function getTotalRepasses($fk_usuario){
        $repasse = Repasse::select(DB::raw('SUM(valor) as total_repasse'))->where('fk_usuario', $fk_usuario)->first();

        if (!empty($repasse->total_repasse)){
            return $repasse->total_repasse;
        } else {
            return 0;
        }
    }

    public function registerTransferManual(Request $Request){
        $data = $Request->all();

        $messages = [
            'type.required'    => "O tipo (type) é obrigatório. Ex.: 'professor'",
            'user_id.required' => "O ID do usuário (user_id) é obrigatório.",
            'cents.required'   => "O valor (cents) a ser transferido é obrigatório.",
            'cents.min'        => "O valor mínimo para registro é R$ 10,00!",
        ];

        $validator = Validator::make($data, [
            'type'          => 'required',
            'user_id'       => 'required',
            'cents'         => 'required|numeric|min:1000',
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return response()->json(['error' => $error[0]]);
            }
        }

        $amount = $data['cents'] / 100;

        $repasse = Repasse::create(['fk_usuario' => $data['user_id'], 'valor' => $amount, 'criacao' => date('Y-m-d H:i:s') ]);

        if ($repasse){
            return response()->json(['success' => 'Repasse registrado com sucesso!']);
        } else {
            return response()->json(['error' => 'Não foi possível registrar o repasse', 'code' => '201909021519']);
        }
    }
}
