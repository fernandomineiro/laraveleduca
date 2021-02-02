<?php


namespace App\Http\Controllers\Admin;

use App\Email;
use App\Faculdade;
use App\TipoEmail;
use App\TipoEmailVariavel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailController extends Controller {

    public function index() {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['emails'] = Email::with('tipo')->with('faculdade')->get();

        $this->arrayViewData['aFaculdades'] = Faculdade::all()->where('status', '=', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista',
            $this->arrayViewData
        );
    }

    public function incluir() {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['aFaculdades'] = Faculdade::all()->where('status', '=', 1)
                                                        ->pluck('fantasia', 'id')
                                                        ->prepend('Selecione','');

        $this->arrayViewData['aTipoEmails'] = TipoEmail::all()->where('status', '=', 1)
                                                ->pluck('titulo', 'id')->prepend('Selecione','');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id) {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['aFaculdades'] = Faculdade::all()->where('status', '=', 1)
            ->pluck('fantasia', 'id')
            ->prepend('Selecione','');

        $this->arrayViewData['aTipoEmails'] = TipoEmail::all()->where('status', '=', 1)
            ->pluck('titulo', 'id')->prepend('Selecione','');

        $this->arrayViewData['obj'] = Email::find($id);

        $tipoEmail = TipoEmail::find($this->arrayViewData['obj']->fk_tipo_email);
        $templateName = Str::slug($tipoEmail->titulo, '_');
        $this->arrayViewData['obj']->mytextarea = file_get_contents(resource_path("views/emails/templates/{$this->arrayViewData['obj']->fk_faculdade_id}/{$templateName}.blade.php"));

        $this->arrayViewData['variaveis'] = TipoEmailVariavel::where('fk_tipo_email', $this->arrayViewData['obj']->fk_tipo_email)->get();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function variaveis(Request $request, $idFaculdade, $idTipo) {

//        $tipoEmail = TipoEmail::find($idTipo);
//        $templateName = Str::slug($tipoEmail->titulo, '_');
//        $content = "";
//        if (file_exists(resource_path("views/emails/templates/{$idFaculdade}/{$templateName}.blade.php"))) {
//            $content = file_get_contents(resource_path("views/emails/templates/{$idFaculdade}/{$templateName}.blade.php"));
//        }

        return response()->json([
            'variaveis' => TipoEmailVariavel::where('fk_tipo_email', $idTipo)->get(),
        ]);
    }

    public function salvar(Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        try {
            $tipoEmail = TipoEmail::find($request->get('fk_tipo_email'));
            $templateName = Str::slug($tipoEmail->titulo, '_');

            $email = new Email();

            $validator = Validator::make($request->all(), $email->rules, $email->messages);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $email->fill($request->all());

            $email->save();

            if (!is_dir(resource_path("views/emails/templates/{$email->fk_faculdade_id}"))) {
                mkdir(resource_path("views/emails/templates/{$email->fk_faculdade_id}"));
            }

            File::put(resource_path("views/emails/templates/{$email->fk_faculdade_id}/{$templateName}.blade.php"), $request->get('mytextarea'));

            \Session::flash('mensagem_sucesso', 'Cadastrado com sucesso');
            return Redirect('admin/emails/index');

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', 'Erro ao inserir o registro. Erro:'. $error->getMessage());
            return Redirect::back()->withInput();
        }
    }

    /**
     * @desc Atualizar Email
     *
     * @param integer $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        try {
            $tipoEmail = TipoEmail::find($request->get('fk_tipo_email'));
            $templateName = Str::slug($tipoEmail->titulo, '_');

            $email = new Email();

            $validator = Validator::make($request->all(), $email->rules, $email->messages);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $email = Email::findOrFail($id);
            $email->update($request->all());

            if (!is_dir(resource_path("views/emails/templates/{$email->fk_faculdade_id}"))) {
                mkdir(resource_path("views/emails/templates/{$email->fk_faculdade_id}"));
            }

            File::put(resource_path("views/emails/templates/{$email->fk_faculdade_id}/{$templateName}.blade.php"), $request->get('mytextarea'));

            \Session::flash('mensagem_sucesso', 'Atualizado com Sucesso!');
            return Redirect::back();

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', 'Não foi possível atualizar o registro! Erro: '. $error->getMessage());
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Deletar Alunos
     *
     * @param integer $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        try {
            $email = Email::findOrFail($id);
            $email->delete();

            \Session::flash('mensagem_sucesso', 'Registro deletado com sucesso');
            return Redirect::back();
        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', 'Erro ao deletar o registro');
        }
    }

    public function clonar($id, Request $request) {
        try {
            $email = Email::findOrFail($id);


            if (empty($request->get('fk_faculdade_id', null))) {
                \Session::flash('mensagem_erro', 'Erro ao clonar o template. É necessário escolher um projeto para clonar.');
                return Redirect::back();
            }

            $faculdadeEmail = Email::where('fk_faculdade_id', $request->get('fk_faculdade_id'))->where('fk_tipo_email', $email->fk_tipo_email)->first();
            if (!empty($faculdadeEmail)) {
                \Session::flash('mensagem_erro', 'Erro ao clonar o template. Template já existe para a faculdade escolhida.');
                return Redirect::back();
            }

            $emailData = $email->toArray();
            unset($emailData['id']);
            unset($emailData['fk_criador_id']);
            unset($emailData['fk_atualizador_id']);
            unset($emailData['criacao']);
            unset($emailData['atualizacao']);

            $emailData['fk_faculdade_id'] = $request->get('fk_faculdade_id');

            $tipoEmail = TipoEmail::find($emailData['fk_tipo_email']);
            $templateName = Str::slug($tipoEmail->titulo, '_');

            $newEmail = new Email();
            $newEmail->fill($emailData);
            $newEmail->save();

            if (!is_dir(resource_path("views/emails/templates/{$emailData['fk_faculdade_id']}"))) {
                mkdir(resource_path("views/emails/templates/{$emailData['fk_faculdade_id']}"));
            }

            File::put(resource_path("views/emails/templates/{$emailData['fk_faculdade_id']}/{$templateName}.blade.php"), file_get_contents(resource_path("views/emails/templates/{$email->fk_faculdade_id}/{$templateName}.blade.php")));

            \Session::flash('mensagem_sucesso', 'Template clonado com sucesso');
            return Redirect::back();
        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', 'Erro ao clonar o registro '.$error->getMessage());
            return Redirect::back();
        }
    }
}
