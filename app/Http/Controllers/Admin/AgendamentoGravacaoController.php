<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
//
use App\Faculdade;
use App\Parceiro;
use App\Produtora;
use App\AgendamentoGravacao;

class AgendamentoGravacaoController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agendamentogravacao'] =AgendamentoGravacao::lista();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_parceiro'] = Parceiro::select('parceiro.id', 'usuarios.nome as parceiro')
            ->join('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
            ->where('usuarios.status', '=', 1)
            ->get()
            ->pluck('parceiro', 'id');

        $this->arrayViewData['lista_produtora'] = Produtora::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['lista_projetos'] = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_check'] = ['0' => 'Não', '1' => 'Sim'];
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['agendamentogravacao'] = AgendamentoGravacao::findOrFail($id);
        $this->arrayViewData['lista_parceiro'] = Parceiro::select('parceiro.id', 'usuarios.nome as parceiro')
            ->join('usuarios', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
            ->where('usuarios.status', '=', 1)
            ->get()
            ->pluck('parceiro', 'id');

        $this->arrayViewData['lista_produtora'] = Produtora::all()->where('status', '=', 1)->pluck('razao_social', 'id');
        $this->arrayViewData['lista_projetos'] = Faculdade::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_check'] = ['0' => 'Não', '1' => 'Sim'];
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $obj = new AgendamentoGravacao();
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {

            $dadosForm = $request->except('_token');

            $hrInicio = explode(':', $dadosForm['hora']);
            $dadosForm['hora'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
            $dadosForm['data'] = implode('-', array_reverse(explode('/', $dadosForm['data'])));

            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $obj->create($dadosForm);

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

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = AgendamentoGravacao::findOrFail($id);
        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $hrInicio = explode(':', $dadosForm['hora']);
            $dadosForm['hora'] = trim($hrInicio[0]) . ':' . trim($hrInicio[1]);
            $dadosForm['data'] = implode('-', array_reverse(explode('/', $dadosForm['data'])));

            $dadosForm = $this->insertAuditData($dadosForm, false);
            $resultado = $obj->update($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = AgendamentoGravacao::findOrFail($id);

        $resultado = $obj->delete($obj);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}


