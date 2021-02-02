<?php

namespace App\Http\Controllers\Admin;

use App\CursoCategoria;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class CursoCategoriaController extends Controller{

    public function index(){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['cursos_categoria'] = CursoCategoria::all()->where('status', '=', 1);

				/*faço a validação para saber se por acaso existe alguma categoria em que a coluna slug_categoria 
				esteja vazia, então irei ajustar automaticamente elas*/
				CursoCategoria::verificarSlugsNaoCadastrados();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir(){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['cursos_categoria'] = CursoCategoria::all()->where('status', '=', 1);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['cursos_categoria'] = CursoCategoria::findOrFail($id);

        $filePath = 'files/categoria/icone/';
        $this->arrayViewData['cursos_categoria_icone'] = null;
        if (!empty($this->arrayViewData['cursos_categoria']['icone']) && Storage::disk('s3')->exists($filePath . $this->arrayViewData['cursos_categoria']['icone'])) {
            $this->arrayViewData['cursos_categoria_icone'] = getimagesizefromstring(Storage::disk('s3')->get($filePath . $this->arrayViewData['cursos_categoria']['icone']));
        }

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = new CursoCategoria();

        $validator = $obj->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $dadosForm = $this->insertAuditData($request->except('_token'));
        if ($request->hasFile('icone')) {
            $file = $request->file('icone');
            $dadosForm['icone'] = $this->uploadFile('icone', $file, 'categoria', $request);
        }

        $dadosForm['slug_categoria'] = CursoCategoria::configurarSlugCategoria($dadosForm['titulo']);
        $resultado = $obj->create($dadosForm);

        if ($resultado) {
            Session::flash('mensagem_sucesso', $this->msgInsert);
            return redirect('admin/cursos_categoria/index');
        }

        Session::flash('mensagem_erro', $this->msgInsertErro);
        return Redirect::back()->withErrors($validator)->withInput();
    }

    public function atualizar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $obj = CursoCategoria::findOrFail($id);

        $validator = $obj->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $dadosForm = $this->insertAuditData($request->except('_token'), false);
        if ($request->hasFile('icone')) {
            $file = $request->file('icone');
            $dadosForm['icone'] = $this->uploadFile('icone', $file, 'categoria', $request);

            if(!is_null($obj->icone)) {
                $image_path = '/files/categoria/icone/' . $obj->icone;
                if(Storage::disk('s3')->exists($image_path)) {
                    Storage::disk('s3')->delete($image_path);
                }
            }
        } else {
            $dadosForm['icone'] = $obj->icone;
            if (!$request->has('icone_categora')) {
                $dadosForm['icone'] = null;

                $image_path = '/files/categoria/icone/' . $obj->icone;
                if(Storage::disk('s3')->exists($image_path)) {
                    Storage::disk('s3')->delete($image_path);
                }
            }
        }

        $dadosForm['slug_categoria'] = CursoCategoria::configurarSlugCategoria($dadosForm['titulo']);

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            Session::flash('mensagem_sucesso', $this->msgUpdate);
            return redirect('admin/cursos_categoria/index');
        }

        Session::flash('mensagem_erro', $this->msgUpdateErro);
        return Redirect::back()->withErrors($validator)->withInput();
    }


    public function deletar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = CursoCategoria::findOrFail($id);

        $dadosForm = $request->except('_token');

        $dadosForm = $this->insertAuditData($dadosForm, false);
        $dadosForm['status'] = 0;

        $resultado = $obj->update($dadosForm);

        if ($resultado) {
            Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }

    public function uploadFile($input, $file, $tipo = 'categoria', Request $request){

        $validator = $this->validate($request, [
            'icone' => 'dimensions:width=400,height=400'
        ], [
            'icone.dimensions' => 'As dimensões da imagem deverá ser 400x400px'
        ]);

        if ($file->isValid()) {
            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();

            Storage::disk('s3')->put('files/' . $tipo . '/' . $input . '/' . $fileName, file_get_contents($file), 'public');
            return $fileName;
        }

        return '';
    }
}
