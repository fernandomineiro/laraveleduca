<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\CursoModulo;
use App\Curso;
use App\Professor;

class CursoModuloController extends Controller
{

    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $professores = Professor::select('professor.*', 'usuarios.nome')->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')->where('professor.status', '=', 1)->get();

        $lista_professor = array();
        foreach($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;
        $this->arrayViewData['cursos_modulo'] = CursoModulo::select()->where('status', '=', 1)->get();
        $this->arrayViewData['lista_curso'] = $this->carregaComboCurso();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $professores = Professor::select('professor.*', 'usuarios.nome')->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')->where('professor.status', '=', 1)->get();

        $lista_professor = array();
        foreach($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;
        $this->arrayViewData['lista_curso'] = $this->carregaComboCurso();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $professores = Professor::select('professor.*', 'usuarios.nome')->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')->where('professor.status', '=', 1)->get();

        $lista_professor = array();
        foreach($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome;
        }

        $this->arrayViewData['lista_professor'] = $lista_professor;
        $this->arrayViewData['cursos_modulo'] = CursoModulo::findOrFail($id);
        $this->arrayViewData['lista_curso'] = $this->carregaComboCurso();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $params = $request->all();
        $params = $this->insertAuditData($params);
        $cursos_modulo = new CursoModulo($params);

        $validator = Validator::make($request->all(), $cursos_modulo->rules, $cursos_modulo->messages);

        if ($request->hasFile('url_arquivo')) {
            $file = $request->file('url_arquivo');
            $cursos_modulo->url_arquivo = $this->_uploadFile('url_arquivo', $file);
        } else {
            $cursos_modulo->url_arquivo = isset($cursos_modulo->url_arquivo) ? $cursos_modulo->url_arquivo : '';
        }

        if (!$validator->fails()) {
            $resultado = $cursos_modulo->save();

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

        $cursos_modulo = CursoModulo::findOrFail($id);
        $validator = Validator::make($request->all(), $cursos_modulo->rules, $cursos_modulo->messages);

        if ($request->hasFile('url_arquivo')) {
            $file = $request->file('url_arquivo');
            $cursos_modulo->url_arquivo = $this->uploadFile('url_arquivo', $file);
        } else {
            $cursos_modulo->url_arquivo = isset($cursos_modulo->url_arquivo) ? $cursos_modulo->url_arquivo : '';
        }

        $cursos_modulo->atualizacao = date('Y-m-d H:i:s');
        $cursos_modulo->fk_atualizador_id = $this->userLogged->id;
        $cursos_modulo->status = 1;

        if (!$validator->fails()) {
            $resultado = $cursos_modulo->save();

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

        $obj = CursoModulo::findOrFail($id);

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

    public function carregaComboCurso()
    {
        $cursos = Curso::all()->where('status', '=', 1)->pluck('titulo', 'id');

        $lista_cursos = [];
        foreach($cursos as $key => $curso) {
            $lista_cursos[$key] = $curso;
        }

        return $lista_cursos;
    }

    public function uploadFile($input, $file)
    {
        
        $supported_mime_types = array(
            'application/vnd.ms-excel',
            'application/zip',
            'application/msword',
            'application/pdf',
            'audio/mpeg3');

        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            $mime = $filename->getMimeType();

            if (in_array($mime, $supported_mime_types)) {
                if ($file->move('files/cursos_modulo/' . $input, $fileName)) {
                    return $fileName;
                }
            } 
            return 'Extensão de arquivo não permitida';   
        }
        return '';
    }
}	
