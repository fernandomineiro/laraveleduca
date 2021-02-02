<?php

namespace App\Http\Controllers\Admin;

use App\Banco;
use App\Cidade;
use App\DiretoriaEnsino;
use App\Endereco;
use App\Escola;
use App\Estado;
use App\Faculdade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class EscolaController extends Controller {

    /**
     * @desc Listar Faculdades
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objLst'] = Escola::all()->where('status', 1);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * @desc Formulário para incluir faculdade
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $cidades= Cidade::select('descricao_cidade', 'id')->get();
        $estados = Estado::select('descricao_estado', 'id')->get();

        $cidades = $cidades->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_cidade'])];
        });
        $estados = $estados->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_estado'])];
        });
        $this->arrayViewData['estados'] = $estados->toArray();
        $this->arrayViewData['cidades'] = $cidades->toArray();

        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->where('projeto_escolas', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        $this->arrayViewData['diretoriaEnsino'] = DiretoriaEnsino::all()->where('status', '=', 1)
            ->pluck('nome', 'id')
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
                'razao_social' => 'required',
                'fk_faculdade' => 'required',
                'fk_diretoria_ensino' => 'required',
            ],
            [
                'razao_social.required' => 'Nome/Razão Social é obrigatório',
                'fk_faculdade.required' => 'Projeto é obrigatório!',
                'fk_diretoria_ensino.required' => 'Diretoria de ensino é obrigatório!',
            ]
        );

        \DB::beginTransaction();

        try {

            $data = $request->all();

            $faculdade = new Escola($request->all());
            $endereco = $this->updateCreateAddress($data);
            // cria cadastro
            $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;

            $faculdade->create($data);

            \DB::commit();

            \Session::flash('mensagem_sucesso', 'Registro inserido com sucesso!');
            return Redirect::back();

        } catch (\Exception $exception) {
            \DB::rollBack();
            \Session::flash('mensagem_erro', 'Não foi possível inserir o registro! ' . $exception->getMessage());
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

        $cidades= Cidade::select('descricao_cidade', 'id')->get();
        $estados = Estado::select('descricao_estado', 'id')->get();

        $cidades = $cidades->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_cidade'])];
        });
        $estados = $estados->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_estado'])];
        });
        $this->arrayViewData['estados'] = $estados->toArray();
        $this->arrayViewData['cidades'] = $cidades->toArray();

        $this->arrayViewData['objFaculdade'] = Escola::select('*')->where('id', $id)->first();

        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->where('projeto_escolas', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        $this->arrayViewData['objEndereco'] = Endereco::select('endereco.*')
            ->join('faculdades', 'faculdades.fk_endereco_id', '=', 'endereco.id')
            ->where('faculdades.id', '=', $id)
            ->first();

        $this->arrayViewData['diretoriaEnsino'] = DiretoriaEnsino::all()->where('status', '=', 1)
            ->pluck('nome', 'id')
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
                'razao_social' => 'required',
                'fk_faculdade' => 'required',
                'fk_diretoria_ensino' => 'required',
            ],
            [
                'razao_social.required' => 'Nome/Razão Social é obrigatório',
                'fk_faculdade.required' => 'Projeto é obrigatório!',
                'fk_diretoria_ensino.required' => 'Diretoria de ensino é obrigatório!',
            ]
        );

        \DB::beginTransaction();
        try {

            $data = $request->all();
            $endereco = $this->updateCreateAddress($data, $data['fk_endereco_id']);

            $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;

            $faculdade = Escola::findOrFail($id);

            $faculdade->fill($data);
            $faculdade->save();


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

        $cidade = Escola::findOrFail($id);
        $cidade->delete();

        \Session::flash('mensagem_sucesso', 'Registro deletado com sucesso!');
        return Redirect::back();
    }

    /**
     * @param array $data
     * @param null  $id
     * @return mixed
     */
    public function updateCreateAddress(array $data, $id = null)  {
        if (!$id) {
            $id = null;
        }

        $validator = Endereco::validade($data);
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return false;
        }

        return Endereco::updateOrCreate(['id' => $id], $data);
    }
}