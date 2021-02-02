<?php

namespace App\Http\Controllers\Admin;

use App\DiretoriaEnsino;
use App\Exports\GestoresExport;
use App\Faculdade;
use App\Produtora;
use App\Usuario;
use App\Gestao;
use App\UsuariosPerfil;
use App\Exports\UsuariosExport;
use App\Http\Controllers\PessoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use function redirect;

/**
 * Class GestaoController
 * @package App\Http\Controllers\Admin
 */
class GestaoController extends PessoasController
{

    /**
     * GestaoController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objLst'] = Gestao::select('usuarios.*', 'gestao_ies.*', 'usuarios.status AS usuario_ativo')
            ->join('usuarios', 'gestao_ies.fk_usuario_id', '=', 'usuarios.id')
            ->where('gestao_ies.status', '1')
            ->get();

        $this->arrayViewData['lista_perfis'] = UsuariosPerfil::all()
            ->where('status', '=', 1)
            ->whereIn('id', [13, 11, 10, 8, 22])
            ->pluck('titulo', 'id');

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->pluck('fantasia', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['lista_perfis'] = UsuariosPerfil::all()
            ->where('status', '=', 1)
            ->whereIn('id', [13, 11, 10, 8, 22])
            ->pluck('titulo', 'id');

        $this->arrayViewData['lista_diretoria'] = DiretoriaEnsino::all();

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->pluck('fantasia', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objGestao'] = Gestao::select('*')->where('id', $id)->first();
        $this->arrayViewData['objUsuario'] = Usuario::select('usuarios.*')
            ->join('gestao_ies', 'gestao_ies.fk_usuario_id', '=', 'usuarios.id')
            ->where('gestao_ies.id', '=', $id)
            ->first();

        $this->arrayViewData['lista_diretoria'] = DiretoriaEnsino::all()->pluck('nome', 'id')->prepend('Selecione', '');;

        $this->arrayViewData['lista_perfis'] = UsuariosPerfil::all()
            ->where('status', '=', 1)
            ->whereIn('id', [13, 11, 10, 8, 22])
            ->pluck('titulo', 'id');

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione', '');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request)
    {

        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $result = $this->salvarRegistro($request, new Gestao($request->all()));

        if (!empty($result['type'])) {
            $message = $result['message'];
            \Session::flash($result['type'], $message);
        }

        if (!empty($result['validatorMessage'])) {
            return Redirect::back()->withErrors($result['validatorMessage'])->withInput();
        }

        return redirect()->route('admin.gestao');
    }

    /**
     * @param         $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        if (is_null($request->get('password')) && is_null($request->get('password_confirmation'))) {
            $request->replace($request->except(['password', 'password_confirmation']));
        }

        $gestao = new Gestao();
        $validator = $gestao->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            return $this->_return($this->atualizarRegistro($request, Gestao::findOrFail($id)),
                '/admin/gestao/index');
        }
    }

    /**
     * @param         $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $result = $this->deletaRegistro(Gestao::findOrFail($id));
        if (!empty($result['type'])) {
            $message = $result['message'];
            \Session::flash($result['type'], $message);
        }

        if (!empty($result['validatorMessage'])) {
            return Redirect::back()->withErrors($result['validatorMessage'])->withInput();
        }

        return Redirect::back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recuperarCredenciais($id)
    {
        try {
            $this->recuperarSenhaUsuario($id);

            \Session::flash('mensagem_sucesso', 'Credencias enviadas com sucesso.');

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', $error->getMessage());
        }
        return Redirect::back();
    }

    /**
     * Exportar Usuários/Gestao
     *
     * @param Request $request
     * @return UsuariosExport
     */
    public function exportar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new GestoresExport(), 'gestores.' . strtolower($request->get('export-to-type')) . '');

    }

}
