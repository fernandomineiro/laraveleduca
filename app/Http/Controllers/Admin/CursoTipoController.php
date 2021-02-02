<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\CursoTipo;

class CursoTipoController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['cursos_tipo'] = CursoTipo::select()->where('status', '=', 1)->get();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);


    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['cursos_tipo'] = CursoTipo::findOrFail($id);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $cursos_tipo = new CursoTipo();
        $validator = Validator::make($request->all(), $cursos_tipo->rules, $cursos_tipo->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $dadosForm = $this->insertAuditData($dadosForm);


            $resultado = $cursos_tipo->create($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', 'Cadastrado com Sucesso!');
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $cursos_tipo = CursoTipo::findOrFail($id);
        $validator = Validator::make($request->all(), $cursos_tipo->rules, $cursos_tipo->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

            $dadosForm = $this->insertAuditData($dadosForm, false);
            $resultado = $cursos_tipo->update($dadosForm);

            if ($resultado) {
                \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', 'Não foi possível atualizar o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = CursoTipo::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }

}
