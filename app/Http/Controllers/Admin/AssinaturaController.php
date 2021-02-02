<?php

namespace App\Http\Controllers\Admin;

use App\AssinaturaConteudo;
use App\AssinaturaFaculdade;
use App\Curso;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

use App\Assinatura;
use App\TipoPagamento;
use App\Professor;

use App\Trilha;
use App\TipoAssinatura;
use App\CertificadoLayout;
use App\Faculdade;
use App\WirecardSignature;
use Illuminate\View\View;

class AssinaturaController extends Controller {

    /**
     * @return Factory|View
     */
    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.index', $this->arrayViewData);
    }

    public function lista($id) {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $tipoAssinatura = TipoAssinatura::findOrFail($id);
        $this->arrayViewData['titulo'] = $tipoAssinatura->titulo;
        $this->arrayViewData['assinaturas'] =
            Assinatura::select('assinatura.*', 'tipo_assinatura.titulo as tipo')
                ->join('tipo_assinatura', 'assinatura.fk_tipo_assinatura', '=', 'tipo_assinatura.id')
                ->where('assinatura.status', '=', 1)
                ->where('fk_tipo_assinatura', $id)
                ->get();

        $this->arrayViewData['lista_periodos'] = [
            '1' => 'Anual',
            '2' => 'Semestral',
            '3' => 'Livre cancelamento',
            '4' => 'Cancelamento Manual',
        ];

        $this->arrayViewData['tipo_assinatura'] = $id;
        $this->arrayViewData['lista_faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['lista_trilhas'] = Trilha::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_tipos'] = TipoAssinatura::all()->where('status', '=', 1)->pluck('titulo', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    public function incluir($id) {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $lista_status = ['0' => 'Inativo', '1' => 'Ativo'];

        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');
        $this->arrayViewData['faculdades'] = $lista_faculdades;
        $this->arrayViewData['lista_tipos'] = TipoAssinatura::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_tipos']->put(0, 'Selecione');
        $this->arrayViewData['lista_tipos'] = $this->arrayViewData['lista_tipos']->sortKeys();

        $this->arrayViewData['lista_periodos'] = [
            0 => 'Selecione',
            '1' => 'Anual',
            '2' => 'Semestral',
            '3' => 'Livre - cancelamento manual'
        ];

        $professores = Professor::select('professor.*')
        ->join('usuarios', 'professor.fk_usuario_id', '=', 'usuarios.id')
        ->where('professor.status', '=', 1)->where('wirecard_account_id', '>', '0')
        ->get();

        $lista_professor = array();
        foreach($professores as $professor) {
            $lista_professor[$professor->id] = $professor->nome;
        }

        $this->arrayViewData['tipo_assinatura'] = $id;
        $this->arrayViewData['lista_professor'] = $lista_professor;
        $this->arrayViewData['projetos'] = null;

        $query = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as tipo
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                        where cursos.status != 0
                        order by cursos.id";

        $this->arrayViewData['cursos'] = DB::select($query);;
        $this->arrayViewData['cursosAdicionados'] = [];

        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id')->toArray();
        $lista_form_faculdades = array();
        foreach($lista_faculdades as $fk_faculdade => $faculdade) {
            $lista_form_faculdades[$fk_faculdade]['id'] = $fk_faculdade;
            $lista_form_faculdades[$fk_faculdade]['descricao'] = $faculdade;
            $lista_form_faculdades[$fk_faculdade]['ativo'] = 0;
            if($id != null) {
                $faculdadeExiste = AssinaturaFaculdade::all()->where('fk_assinatura', '=', $id)->where('fk_faculdade', '=', $fk_faculdade)->first();
                if(isset($faculdadeExiste->id)) {
                    $lista_form_faculdades[$fk_faculdade]['ativo'] = 1;
                    $lista_form_faculdades[$fk_faculdade]['gratis'] = $faculdadeExiste->gratis;
                }
            }
        }

        $this->arrayViewData['lista_faculdades'] = $lista_form_faculdades;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.full.create', $this->arrayViewData);

    }

    public function editar($id) {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['assinatura'] = Assinatura::findOrFail($id);

        $this->arrayViewData['lista_periodos'] = [
            0 => 'Selecione',
            '1' => 'Anual',
            '2' => 'Semestral',
            '3' => 'Livre - cancelamento manual',
        ];

        $lista_faculdades = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id')->toArray();
        $lista_form_faculdades = array();
        foreach($lista_faculdades as $fk_faculdade => $faculdade) {
            $lista_form_faculdades[$fk_faculdade]['id'] = $fk_faculdade;
            $lista_form_faculdades[$fk_faculdade]['descricao'] = $faculdade;
            $lista_form_faculdades[$fk_faculdade]['ativo'] = 0;
            if($id != null) {
                $faculdadeExiste = AssinaturaFaculdade::all()->where('fk_assinatura', '=', $id)->where('fk_faculdade', '=', $fk_faculdade)->first();
                if(isset($faculdadeExiste->id)) {
                    $lista_form_faculdades[$fk_faculdade]['ativo'] = 1;
                    $lista_form_faculdades[$fk_faculdade]['gratis'] = $faculdadeExiste->gratis;
                }
            }
        }

        $this->arrayViewData['lista_faculdades'] = $lista_form_faculdades;

        /**
         *
         *
         * fk_curso, fk_faculdade
         */

        $this->arrayViewData['lista_certificados'] = CertificadoLayout::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_trilhas'] = Trilha::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_tipos'] = TipoAssinatura::all()->where('status', '=', 1)->pluck('titulo', 'id');
        $this->arrayViewData['lista_tipos']->put(0, 'Selecione');
        $this->arrayViewData['lista_tipos'] = $this->arrayViewData['lista_tipos']->sortKeys();
        $this->arrayViewData['faculdades'] = Faculdade::all()->where('status', '=', 1)->pluck('fantasia', 'id');

        $projetos = [];
        foreach (AssinaturaFaculdade::select('fk_faculdade')->where('fk_assinatura', $this->arrayViewData['assinatura']->id )->get() as $faculdade) {
            $projetos[] = $faculdade['fk_faculdade'];
        }

        $query = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as tipo
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                        where cursos.status != 0
                            AND cursos.id not in (
                                select fk_conteudo from assinatura_conteudos where fk_assinatura = {$this->arrayViewData['assinatura']->id}
                            )
                        order by cursos.id";

        $queryAdicionados = "select distinct
                            cursos.id,
                            cursos.titulo,
                            CONCAT(professor.nome, ' ', professor.sobrenome) as professor_nome,
                            cursos_tipo.titulo as tipo,
                            cursos_valor.valor,
                            cursos_valor.valor_de
                        from cursos
                            JOIN professor on professor.id = cursos.fk_professor
                            join cursos_tipo on cursos_tipo.id = cursos.fk_cursos_tipo
                            join cursos_valor on cursos.id = cursos_valor.fk_curso
                        where cursos.status != 0
                            AND cursos.id in (
                                select fk_conteudo from assinatura_conteudos where fk_assinatura = {$this->arrayViewData['assinatura']->id}
                            )
                        order by cursos.id";

        $this->arrayViewData['projetos'] = $projetos;
        $this->arrayViewData['cursos'] = DB::select($query);
        $this->arrayViewData['cursosAdicionados'] = DB::select($queryAdicionados);

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.full.update', $this->arrayViewData);
    }

    public function salvar(Request $request) {

        if (!$this->validateAccess(\Session::get('user.logged'),false)) {
            return redirect()->route($this->redirecTo);
        }

        $assinatura = new Assinatura();
        $validator = Validator::make($request->all(), $assinatura->rules, $assinatura->messages);

        $dadosForm = $request->except('_token');
        $dadosForm['valor_de'] = $dadosForm['valor_de'] ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor_de'])) : '';
        $dadosForm['valor'] = $dadosForm['valor'] ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor'])) : '';

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        \DB::beginTransaction();

        try {

            $plan_code = time();
            $plano_wirecard = $this->createPlanWirecard($dadosForm, $plan_code);

            if (!empty($plano_wirecard)) {
                $plano_wirecard = json_decode($plano_wirecard);

                if (isset($plano_wirecard->ERROR)) {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', 'Erro Wirecard - ' . $plano_wirecard->ERROR);
                    return Redirect::back()->withErrors($validator)->withInput();
                }

                if (isset($plano_wirecard->errors)) {
                    \DB::rollBack();
                    \Session::flash('mensagem_erro', 'Erro Wirecard - ' .
                        (!empty($plano_wirecard->errors[0])) ? $plano_wirecard->errors[0]->description : '
                            Erro não especificado pela wirecard!');
                    return Redirect::back()->withErrors($validator)->withInput();
                }

                $dadosForm['plano_wirecard_id'] = $plan_code;
            }

            $resultado = $assinatura->create($dadosForm);
            if (isset($resultado) && !$resultado) {
                \DB::rollBack();
                \Session::flash('mensagem_erro', 'Não foi possível inserir o registro!');
                return Redirect::back()->withErrors($validator)->withInput();
            }

            if (!empty($request->get('fk_curso'))) {
                foreach ($request->get('fk_curso') as $cursoId) {
                    $conteudo = new AssinaturaConteudo();
                    $conteudo->fill([
                        'fk_conteudo' => $cursoId,
                        'fk_assinatura' => $resultado->id
                    ]);

                    $conteudo->save();
                }
            }

            if (!empty($request->get('faculdades'))) {
                foreach ($request->get('faculdades') as $faculdade) {
                    $faculdadeAssinatura = new AssinaturaFaculdade();
                    $faculdadeAssinatura->fk_faculdade = $faculdade;
                    $faculdadeAssinatura->status = 1;
                    $faculdadeAssinatura->fk_assinatura = $resultado->id;
                    $faculdadeAssinatura->gratis = (isset($request->get('gratis')[$faculdade])) ? 1 : 0;

                    $faculdadeAssinatura->save();
                }
            }

            \Session::flash('mensagem_sucesso', $this->msgInsert);
            \DB::commit();
            return redirect('admin/assinatura/index');

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', $error->getMessage());
            DB::rollBack();
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }


    public function atualizar($id, Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) {
            return redirect()->route($this->redirecTo);
        }

        $assinatura = Assinatura::findOrFail($id);
        $validator = Validator::make($request->all(), $assinatura->rules, $assinatura->messages);

        $dadosForm = $request->except('_token');

        $dadosForm['valor_de'] = $dadosForm['valor_de'] ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor_de'])) : '';
        $dadosForm['valor'] = $dadosForm['valor'] ? str_replace(",", ".", str_replace(".", "", $dadosForm['valor'])) : null;

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

         /* CRIAR PLANO NA WIRECARD */
         $plano['code'] = time();
         $plano['name'] = $dadosForm['titulo'];
         $plano['description'] = 'Descrição do Plano Especial';
         $plano['amount'] = (int)($dadosForm['valor_de'] * 100);
         $plano['interval']['unit'] = 'MONTH';
         $plano['interval']['length'] = 1;
         $plano['payment_method'] = 'ALL';
         $plano['status'] = ($dadosForm['status'] == 1) ? 'ACTIVE' : 'INACTIVE';
         $plano['billing_cycles'] = ''; /* NUNCA EXPIRA */

        $this->authentication();
        $plano_wirecard = WirecardSignature::updatePlan($assinatura->plano_wirecard_id, $plano);

        if (isset($plano_wirecard->errors)){
            if (isset($plano_wirecard->errors[0]->description)){
                \Session::flash('mensagem_erro', 'Erro Wirecard - ' . $plano_wirecard->errors[0]->description);
                return Redirect::back()->withErrors($validator)->withInput();
            }
        }

        if ($dadosForm['tipo_liberacao'] != 2) {
            $dadosForm['periodo_em_dias'] = null;
            $dadosForm['qtd_cursos'] = null;
        }

        $resultado = $assinatura->update($dadosForm);

        if (!$resultado) {
            \Session::flash('mensagem_erro', $this->msgUpdateErro);
            return Redirect::back()->withErrors($validator)->withInput();
        }

        if (!empty($request->get('fk_curso'))) {
            AssinaturaConteudo::where('fk_assinatura', $assinatura->id)->delete();
            foreach ($request->get('fk_curso') as $cursoId) {
                AssinaturaConteudo::updateOrCreate([
                    'fk_conteudo' => $cursoId,
                    'fk_assinatura' => $assinatura->id
                ], [
                    'fk_conteudo' => $cursoId,
                    'fk_assinatura' => $assinatura->id
                ]);
            }
        }

        if (!empty($request->get('faculdades'))) {
            AssinaturaFaculdade::where('fk_assinatura', $assinatura->id)->delete();
            foreach ($request->get('faculdades') as $faculdade) {
                try {
                    AssinaturaFaculdade::updateOrCreate(['fk_assinatura' => $assinatura->id, 'fk_faculdade' => $faculdade], [
                        'fk_assinatura' => $assinatura->id,
                        'fk_faculdade' => $faculdade,
                        'status' => 1,
                        'gratis' => (isset($request->get('gratis')[$faculdade])) ? 1 : 0,
                    ]);
                } catch (\Exception $error) {
                    dd($error);
                }
            }
        }

        \Session::flash('mensagem_sucesso', $this->msgUpdate);
        return redirect('admin/assinatura/index');

    }


    public function deletar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        $obj = Assinatura::findOrFail($id);
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
    public function configurar_cursos($id) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $this->arrayViewData['assinatura'] = Assinatura::findOrFail($id);
        $this->arrayViewData['trilhas'] = Trilha::all()->where('status', '=', 1);
        $this->arrayViewData['cursos'] = Curso::lista(1);
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.configuracao', $this->arrayViewData);
    }
    public function salvarcursos(Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        $assinatura = $request->get('assinatura');
        dd($assinatura);
    }
    public function alteraStatusTrilha (Request $request) {

    }
    public function alteraStatusCurso (Request $request) {

    }

    private function createPlanWirecard($data, $plan_code){
        /* CRIAR PLANO NA WIRECARD */
        $plano['code'] = $plan_code;
        $plano['name'] = $data['titulo'];
        $plano['description'] = 'Descrição do Plano Especial';
        $plano['amount'] = (int)($data['valor_de'] * 100);
        $plano['interval']['unit'] = 'MONTH';
        $plano['interval']['length'] = 1;
        $plano['payment_method'] = 'ALL';
        $plano['status'] = ($data['status'] == 1) ? 'ACTIVE' : 'INACTIVE';
        $plano['billing_cycles'] = ''; /* NUNCA EXPIRA */
        $plano['setup_fee'] = 0;

        $this->authentication();
        return WirecardSignature::createPlan($plano);
    }

    private function authentication(){
        $setting = $this->getSetting();

        $auth = '';
        $enviroment = '';
        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $auth = 'Authorization: Basic ' . base64_encode($setting->token_producao . ':' . $setting->key_producao);
                $enviroment = 'https://api.moip.com.br/assinaturas/v1';
            } else {
                $auth = 'Authorization: Basic ' . base64_encode($setting->token_teste . ':' . $setting->key_teste);
                $enviroment = 'https://sandbox.moip.com.br/assinaturas/v1';
            }
        }

        WirecardSignature::authentication(['auth' => $auth, 'enviroment' => $enviroment]);
    }

    private function getSetting(){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        return $setting;
    }
}
