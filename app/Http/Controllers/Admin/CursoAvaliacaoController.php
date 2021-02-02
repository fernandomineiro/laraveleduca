<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\CursoAvaliacao;

use App\Curso;
use App\Faculdade;

class CursoAvaliacaoController extends Controller {

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->carregaCombox();

        $this->arrayViewData['cursos_avaliacao'] = CursoAvaliacao::select('cursos_avaliacao.*')->get();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->carregaCombox();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->carregaCombox();
        $this->arrayViewData['curso_avaliacao'] = CursoAvaliacao::findOrFail($id);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) {
            return redirect()->route($this->redirecTo);
        }
        $obj = new CursoAvaliacao();

        $dadosForm = $request->except('_token');

        $userLogged = \Session::get('user.logged');
        $dadosForm['fk_aluno'] = $userLogged->id;

        $validator = Validator::make($dadosForm, $obj->rules, $obj->messages);

        if (!$validator->fails()) {

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

    private function carregaCombox()
    {
        $lista_faculdade = Faculdade::all()->where('status', '!=', 0)->pluck('fantasia', 'id');

        $lista_cursos = Curso::select('titulo', 'id', 'fk_faculdade')->where('status', '!=', 0)->get();

        $lista = ['' => 'Selecione'];
        foreach($lista_cursos as $k => $curso) {
            if(isset($lista_faculdade[$curso->fk_faculdade])) {
                $lista[$curso->id] = $lista_faculdade[$curso->fk_faculdade] . ' - ' . $curso->titulo;
            }
        }

        $this->arrayViewData['lista_cursos'] = $lista;
        $this->arrayViewData['lista_status'] = [
            '3' => 'Não Avaliado',
            '1' => 'Aprovado',
            '2' => 'Reprovado'
        ];
        $this->arrayViewData['lista_estrelas'] = [
            ''  => 'Selecione',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5'
        ];
    }

    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) return redirect()->route($this->redirecTo);
        $obj = CursoAvaliacao::findOrFail($id);

        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');

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

    /**
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id) {

        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        try {
            $obj = CursoAvaliacao::findOrFail($id);
            $obj->delete();

            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
}
