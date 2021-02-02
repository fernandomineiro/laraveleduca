<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use App\PagamentoItau;
use App\PagamentoBradesco;
use App\PagamentoPayPal;
use App\EmailsTemplate;
use App\Aluno;
use App\Pedido;

class PagamentosController extends Controller
{
    public function pagamentobradesco(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $objPagamento = new PagamentoBradesco();
        $validator = Validator::make($request->all(), $objPagamento->rules, $objPagamento->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $objPagamento->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function pagamentopaypal(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $objPagamento = new PagamentoPayPal();
        $validator = Validator::make($request->all(), $objPagamento->rules, $objPagamento->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $objPagamento->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function pagamentoitau(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $objPagamento = new PagamentoItau();
        $validator = Validator::make($request->all(), $objPagamento->rules, $objPagamento->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $objPagamento->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    private function obterPedido($pid)
    {
        return DB::table('pedidos')->where('pid', $pid)->first();
    }

    private function obterAluno($alunoCpf)
    {
        return DB::table('alunos')->where('cpf', $alunoCpf)->first();
    }
}
