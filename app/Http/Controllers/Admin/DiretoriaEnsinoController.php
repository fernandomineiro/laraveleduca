<?php

namespace App\Http\Controllers\Admin;

use App\DiretoriaEnsino;
use App\Faculdade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;


class DiretoriaEnsinoController extends Controller {

    /**
     * @desc Listar Faculdades
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objlista'] = DiretoriaEnsino::all()->where('status', 1);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir() {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->where('projeto_escolas', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');



        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @desc Salvar Faculdade
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function salvar(Request $request) {

        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $this->validate(
            $request,
            [
                'nome' => 'required',
                'fk_faculdade' => 'required',
            ],
            [
                'nome.required' => 'Nome é obrigatório',
                'fk_faculdade.required' => 'Projeto é obrigatório!',
            ]
        );

        \DB::beginTransaction();
        try {

            $diretoria = new DiretoriaEnsino();

            $diretoria->fill(array_merge($request->all(), ['slug' => Str::slug($request->get('nome'), '-')]));
            $diretoria->save();

            \DB::commit();

            \Session::flash('mensagem_sucesso', 'Registro inserido com sucesso!');
            return Redirect::back();

        } catch (\Exception $exception) {
            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
            return Redirect::back();
        }
    }

    /**
     * @desc Editar Faculdade
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editar($id) {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }
        $this->arrayViewData['obj'] = DiretoriaEnsino::select('*')->where('id', $id)->first();
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->where('projeto_escolas', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @desc Atualizar Faculdade
     *
     * @param integer $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $this->validate(
            $request,
            [
                'nome' => 'required',
                'fk_faculdade' => 'required',
            ],
            [
                'nome.required' => 'Nome é obrigatório',
                'fk_faculdade.required' => 'Projeto é obrigatório!',
            ]
        );

        \DB::beginTransaction();
        try {

            $data = $request->all();

            $diretoria = DiretoriaEnsino::findOrFail($id);

            $diretoria->fill(array_merge($request->all(), ['slug' => Str::slug($request->get('nome'), '-')]));
            $diretoria->save();

            \DB::commit();

            \Session::flash('mensagem_sucesso', 'Registro atualizado com sucesso!');
            return Redirect::back();

        } catch (\Exception $exception) {
            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível atualizar o registro! ' . $exception->getMessage());
        }
    }

    /**
     * @desc Deletar Faculdade
     *
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $diretoria = DiretoriaEnsino::findOrFail($id);
        $diretoria->delete();

        \Session::flash('mensagem_sucesso', 'Registro deletado com sucesso!');
        return Redirect::back();
    }
}