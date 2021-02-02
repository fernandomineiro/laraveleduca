<?php

namespace App\Http\Controllers\Admin;

use App\Faculdade;
use App\Usuario;
use App\UsuariosPerfil;
use App\Exports\UsuariosExport;
use App\Http\Controllers\PessoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use function redirect;

/**
 * Class UsuarioController
 * @package App\Http\Controllers\Admin
 */
class UsuarioController extends PessoasController
{

    /**
     * UsuarioController constructor.
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

        $this->arrayViewData['usuarios'] = Usuario::select()->whereIn('fk_perfil', [2, 20])->where('status',
            '1')->get();
        $this->arrayViewData['lista_perfis'] = UsuariosPerfil::all()
            ->where('status', '=', 1)
            ->pluck('titulo', 'id');

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
            ->pluck('titulo', 'id');

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia',
            'id')->prepend('Selecione', '');

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

        $usuario = Usuario::findOrFail($id);

        $this->arrayViewData['lista_perfis'] = UsuariosPerfil::all()->pluck('titulo', 'id');
        $this->arrayViewData['obj'] = $usuario;

        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia',
            'id')->prepend('Selecione', '');

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

        $result = $this->salvarRegistro($request, new Usuario($request->all()));

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

        $result = $this->atualizarRegistro($request, Usuario::findOrfail($id));

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
     * @param         $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id)
    {

        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $result = $this->deletaRegistro(Usuario::findOrFail($id));
        if (!empty($result['type'])) {
            $message = $result['message'];

            if (!empty($result['exception'])) {
                $message .= ' Exception: '.$result['exception'];
            }

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
     * Exportar Usuários/Administradores
     *
     * @param Request $request
     * @return UsuariosExport
     */
    public function exportar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new UsuariosExport(), 'usuarios.' . strtolower($request->get('export-to-type')) . '');

    }

}
