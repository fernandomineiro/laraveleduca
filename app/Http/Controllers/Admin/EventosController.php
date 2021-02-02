<?php

namespace App\Http\Controllers\Admin;

use App\CursoCategoria;
use App\Exports\EventosExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\Faculdade;
use App\Eventos;
use Maatwebsite\Excel\Facades\Excel;

class EventosController extends Controller
{
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['eventos'] = Eventos::select('faculdades.fantasia as faculdade', 'eventos.*', 'cursos_categoria.titulo as categoria')
            ->join('faculdades', 'faculdades.id', 'eventos.fk_faculdade')
            ->leftjoin('cursos_categoria', 'cursos_categoria.id', '=', 'eventos.fk_categoria')
            ->where('eventos.status', '>', 0)
            ->get();
        $this->arrayViewData['lista_status'] = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_faculdades']->put(0, 'Selecione');
        $this->arrayViewData['lista_faculdades'] = $this->arrayViewData['lista_faculdades']->sortKeys();
        $this->arrayViewData['lista_categorias'] = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_categorias']->put(0, 'Selecione');
        $this->arrayViewData['lista_categorias'] = $this->arrayViewData['lista_categorias']->sortKeys();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['lista_status'] = [
            '1' => 'Rascunho',
            '2' => 'Revisar',
            '3' => 'Não Aprovado',
            '4' => 'Aprovado',
            '5' => 'Publicado'
        ];
        $this->arrayViewData['eventos'] = Eventos::findOrFail($id);
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_categorias'] = CursoCategoria::all()->where('status', '=', 1)->pluck('titulo', 'id');
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $eventos = new Eventos();
        $validator = Validator::make($request->all(), $eventos->rules, $eventos->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $dadosForm['imagem'] = $this->uploadFile('imagem', $file, 'eventos', $request);
            }
            $dadosForm = $this->insertAuditData($dadosForm);
            $resultado = $eventos->create($dadosForm);

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
        $eventos = Eventos::findOrFail($id);
        $validator = Validator::make($request->all(), $eventos->rules, $eventos->messages);

        if (!$validator->fails()) {
            $dadosForm = $request->except('_token');
            if ($request->hasFile('imagem')) {
                $file = $request->file('imagem');
                $dadosForm['imagem'] = $this->uploadFile('imagem', $file, 'eventos', $request);
            }
            $dadosForm = $this->insertAuditData($dadosForm, false);
            $resultado = $eventos->update($dadosForm);

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

        $obj = Eventos::findOrFail($id);

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

    public function uploadFile($input, $file, $tipo = 'eventos', $request)
    {
        if ($file->isValid()) {

            $dimensions = getimagesize($file);
            $this->validate($request, [
                'imagem' => 'dimensions:width=350,height=130'
            ], [
                'imagem.dimensions' => 'As dimensões da imagem deverá ser 350x130px. Sua imagem tem as dimensões de '. $dimensions[3]
            ]);

            $fileName = date('YmdHis') . '_' . $file->getClientOriginalName();
            if ($file->move('files/' . $tipo . '/' . $input, $fileName)) {
                return $fileName;
            }
        }

        return '';
    }

    /**
     * @param Request $request
     * @return Excel|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportar(Request $request) {
        //if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $retorno = Excel::download(new EventosExport($request->all()), 'eventos.'.strtolower($request->get('export-to-type')).'');
        return $retorno;

    }
}
