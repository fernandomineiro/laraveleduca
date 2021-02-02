<?php


namespace App\Http\Controllers\Admin;

use App\AssinaturaConteudo;
use App\Curso;
use App\CursoCategoria;
use App\Faculdade;
use App\Trilha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Assinatura;

class AssinaturaConteudoController extends Controller
{
    //
    public function index($id) {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $assinatura = Assinatura::findOrFail($id);
        $conteudo = null;
        if ($assinatura->fk_tipo_assinatura === 3) {
            $this->arrayViewData['assinatura_conteudo'] = Assinatura::select('assinatura.*', 'assinatura_conteudos.id as id_conteudo', 'assinatura_conteudos.assinatura', 'trilha.titulo', 'trilha.descricao', 'trilha.fk_faculdade', 'trilha.fk_faculdade')
                ->join('assinatura_conteudos', 'assinatura.id', '=', 'assinatura_conteudos.fk_assinatura')
                ->join('trilha', 'trilha.id', '=', 'assinatura_conteudos.fk_conteudo')
                ->where('assinatura_conteudos.fk_assinatura', '=', $id)
                ->get();
        } else if ($assinatura->fk_tipo_assinatura === 2) {
            $this->arrayViewData['assinatura_conteudo'] = Assinatura::select('assinatura.*', 'assinatura_conteudos.id as id_conteudo', 'assinatura_conteudos.assinatura', 'cursos.titulo', 'cursos.fk_faculdade', 'cursos.descricao')
                ->join('assinatura_conteudos', 'assinatura.id', '=', 'assinatura_conteudos.fk_assinatura')
                ->join('cursos', 'cursos.id', '=', 'assinatura_conteudos.fk_conteudo')
                ->where('assinatura_conteudos.fk_assinatura', '=', $id)
                ->get();
        }
        $this->arrayViewData['lista_status'] = array('0' => 'Inativo', '1' => 'Ativo');
        $this->arrayViewData['assinatura'] = $assinatura;
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_categorias'] = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id')->toArray();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }
    public function incluir ($id) {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['lista_status'] = array('0' => 'Inativo', '1' => 'Ativo');
        $this->arrayViewData['trilhas'] = Trilha::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['cursos'] = Curso::lista(1)->pluck('nome_curso', 'id');
        $this->arrayViewData['assinatura'] = Assinatura::findOrFail($id);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    public function editar ($id) {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $assinatura_conteudo = AssinaturaConteudo::findOrFail($id);
        $this->arrayViewData['assinatura'] = Assinatura::findOrFail($assinatura_conteudo->fk_assinatura);
        $this->arrayViewData['lista_status'] = array('0' => 'Inativo', '1' => 'Ativo');
        $this->arrayViewData['assinatura_conteudo'] =  $assinatura_conteudo;
        $this->arrayViewData['trilhas'] = Trilha::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['cursos'] = Curso::lista(1)->pluck('nome_curso', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    public function atualizar ($id, Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $conteudo = AssinaturaConteudo::findOrFail($id);
        $validator = Validator::make($request->all(), $conteudo->rules, $conteudo->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            $resultado = $conteudo->update($dadosForm);

            if($resultado) {
                \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
                return Redirect::back();
            } else {
                \Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }

    }
    public function salvar (Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $conteudo = new AssinaturaConteudo();
        $validator = Validator::make($request->all(), $conteudo->rules, $conteudo->messages);

        $dadosForm = $request->except('_token');

        if (!$validator->fails()) {
            $resultado = $conteudo->create($dadosForm);

            if($resultado) {
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
    public function deletar($id, Request $request) {

    }
}
