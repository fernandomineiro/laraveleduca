<?php

namespace App\Http\Controllers\Admin;

use App\BannerSecundario;
use App\ConfiguracoesEstilosVariaveis;
use App\ConfiguracoesVariaveis;
use App\CursoCategoria;
use App\Faculdade;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Class Configuracoes
 * @package App\Http\Controllers\Admin
 * Classe de controle das configurações do aplicativo
 * O método salvar e atualizar vale para todos os itens configuráveis,
 * ficando dentro da request AJAX qual será a configuração a ser criada ou atualizada.
 * Como não existe a necessidade de extender a classe todos os métodos serão criados aqui.
 */
class BannerSecundarioController extends Controller {

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index() {

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['objlista'] = BannerSecundario::all()->where('status', 1);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
//        return view('configuracoes.banners_secundarios.lista', $this->arrayViewData);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir() {

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
//        return view('configuracoes.banners_secundarios.formulario', $this->arrayViewData);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editar($id){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');


        $this->arrayViewData['obj'] = BannerSecundario::findOrFail($id);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
//        return view('configuracoes.banners_secundarios.formulario', $this->arrayViewData);

    }

    /**
     * Este método tanto cria o registro como atualiza
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request, $id = 0, $delete = false){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new BannerSecundario();
        $validator = Validator::make($request->all(), [
            'titulo' => 'required',
            'fk_faculdade_id' => 'required',
            "tipo_banner" => "required"
        ], [
            'titulo.required' => 'Título é obrigatório',
            'fk_faculdade_id.required' => 'Projeto é obrigatório',
            'tipo_banner.required' => 'tipo Banner é obrigatório',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $dadosForm = $request->except('_token');
        $dadosForm['status'] = !empty($dadosForm['status']) ? $dadosForm['status'] : 1;
        $dadosForm = $this->insertAuditData($dadosForm);

        $file = $request->file('banner_url');
        if ($request->hasFile('banner_url') && $file->isValid()) {

            $sMessage = '1410x170px';
            $dimensions = getimagesize($request->file('banner_url'));

            $this->validate($request, [
                'banner_url' => 'dimensions:width=1410,height=170'
            ], [
                'banner_url.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
            ]);

            $ext = explode('.', $file->getClientOriginalName());

            $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
            $fileName .= '.' . $ext[count($ext) - 1];

            if ($file->move('files/banners/', $fileName)) {
                $dadosForm['banner_url'] = $fileName;
            }
        }

        if ($dadosForm['tipo_banner'] == 1) {
            $dadosForm['banner_url'] = null;
            $dadosForm['codigo_vimeo_1'] = null;
            $dadosForm['codigo_vimeo_2'] = null;
            $dadosForm['codigo_vimeo_3'] = null;
        }

        if ($dadosForm['tipo_banner'] == 2) {
            $dadosForm['texto'] = null;
            $dadosForm['codigo_vimeo_1'] = null;
            $dadosForm['codigo_vimeo_2'] = null;
            $dadosForm['codigo_vimeo_3'] = null;
        }

        if ($dadosForm['tipo_banner'] == 1) {
            $dadosForm['banner_url'] = null;
            $dadosForm['texto'] = null;
        }

        $resultado = $obj->create($dadosForm);

        if (!$resultado) {
            $this->msgInsertErro = 'Erro ao salvar o Banner';
            \Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $this->msgInsert = 'Banner salvo com sucesso';
        \Session::flash('mensagem_sucesso', $this->msgInsert);
        return Redirect::back();
    }

    /**
     * Utiliza o método salvar para executar as ações de UPDATE
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = BannerSecundario::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'titulo' => 'required',
            'fk_faculdade_id' => 'required',
            "tipo_banner" => "required"
        ], [
            'titulo.required' => 'Título é obrigatório',
            'fk_faculdade_id.required' => 'Projeto é obrigatório',
            'tipo_banner.required' => 'tipo Banner é obrigatório',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $dadosForm = $request->except('_token');
        $dadosForm['status'] = !empty($dadosForm['status']) ? $dadosForm['status'] : 1;
        $dadosForm = $this->insertAuditData($dadosForm);

        $file = $request->file('banner_url');
        if ($request->hasFile('banner_url') && $file->isValid()) {

            $sMessage = '1410x170px';
            $dimensions = getimagesize($request->file('banner_url'));

            $this->validate($request, [
                'banner_url' => 'dimensions:width=1410,height=170'
            ], [
                'banner_url.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
            ]);

            $ext = explode('.', $file->getClientOriginalName());

            $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
            $fileName .= '.' . $ext[count($ext) - 1];

            if ($file->move('files/banners/', $fileName)) {
                $dadosForm['banner_url'] = $fileName;
            }
        }

        if ($dadosForm['tipo_banner'] == 1) {
            $dadosForm['banner_url'] = null;
            $dadosForm['codigo_vimeo_1'] = null;
            $dadosForm['codigo_vimeo_2'] = null;
            $dadosForm['codigo_vimeo_3'] = null;
        }

        if ($dadosForm['tipo_banner'] == 2) {
            $dadosForm['texto'] = null;
            $dadosForm['codigo_vimeo_1'] = null;
            $dadosForm['codigo_vimeo_2'] = null;
            $dadosForm['codigo_vimeo_3'] = null;
        }

        if ($dadosForm['tipo_banner'] == 3) {
            $dadosForm['banner_url'] = null;
            $dadosForm['texto'] = null;
        }

        $resultado = $obj->update($dadosForm);

        if (!$resultado) {
            $this->msgInsertErro = 'Erro ao salvar o Banner';
            \Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $this->msgInsert = 'Banner salvo com sucesso';
        \Session::flash('mensagem_sucesso', $this->msgInsert);
        return Redirect::back();

    }

    /**
     * Utiliza o método salva para executar as ações de exclusão (desativação)
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = BannerSecundario::findOrFail($id);
        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            $this->msgDelete = 'Registro deletado com sucesso';
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        }
        $this->msgDeleteErro = 'Não foi possível deletar o registro';
        \Session::flash('mensagem_erro', $this->msgDeleteErro);
    }
}
