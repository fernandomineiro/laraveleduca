<?php

namespace App\Http\Controllers\Admin;

use App\CertificadoLayout;
use App\EstruturaCurricular;
use App\Faculdade;
use App\Http\Controllers\Controller;
use App\Http\Requests\EstruturaCurricularRequest;
use App\Services\EstruturaCurricularService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Redirect;


class EstruturaCurricularController extends Controller {
    
    private $estruturaCurricularService;
    
    public function __construct(EstruturaCurricularService $estruturaCurricularService) {
        $this->estruturaCurricularService = $estruturaCurricularService;
    }

    /**
     * @return Factory|RedirectResponse|View
     */
    public function index() {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['estruturas_curriculares'] = EstruturaCurricular::where('status', '!=', 0)->get() ;

        return $this->renderView('.lista');
    }

    /**
     * @return Factory|RedirectResponse|View
     */
    public function incluir() {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->setFaculdadesInView();
        $this->setCertificadosInView();
        $this->arrayViewData['projetos'] = null;
        
        return $this->renderView('.create');
    }

    public function editar($id) {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['estrutura'] = EstruturaCurricular::findOrFail($id);
        $this->setFaculdadesInView();
        $this->setCertificadosInView();

        $this->arrayViewData['projetos'] = $this->estruturaCurricularService
            ->retornaIdsProjetosEstruturaCurricular($this->arrayViewData['estrutura']->id);
        $this->arrayViewData['cursos'] = $this->estruturaCurricularService
            ->listarCursosNaoAdicionadosNaEstruturaCurricular($this->arrayViewData['estrutura']->id);
        $this->arrayViewData['cursosAdicionados'] = $this->estruturaCurricularService
            ->listarCursosAdicionadosNaEstruturaCurricular($this->arrayViewData['estrutura']->id);;
        
        return $this->renderView('.update');
    }

    public function salvar(EstruturaCurricularRequest $request) {

        if (!$this->validateAccess(\Session::get('user.logged'),false)) {
            return redirect()->route($this->redirecTo);
        }
        
        try {
            
            $this->estruturaCurricularService->salvar($request->except('_token'));

            \Session::flash('mensagem_sucesso', $this->msgInsert);
            return redirect('admin/estrutura-curricular/index');

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', $error->getMessage());
            return Redirect::back()->withErrors($error->getMessage())->withInput();
        }
    }

    public function atualizar($id, EstruturaCurricularRequest $request) {
        if (!$this->validateAccess(\Session::get('user.logged'),false)) {
            return redirect()->route($this->redirecTo);
        }
        
        try {
            $this->estruturaCurricularService->atualizar($request->except('_token'), $id);

            \Session::flash('mensagem_sucesso', $this->msgUpdate);
            return redirect('admin/estrutura-curricular/index');

        } catch (\Exception $error) {
            \Session::flash('mensagem_erro', $error->getMessage());
            return Redirect::back()->withErrors($error->getMessage())->withInput();
        }
    }


    public function deletar($id, Request $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $obj = EstruturaCurricular::findOrFail($id);

        $dadosEstrutura = $request->except('_token');
        $dadosEstrutura = $this->insertAuditData($dadosEstrutura, false);
        $dadosEstrutura['status'] = 0;

        $resultado = $obj->update($dadosEstrutura);
        if ($resultado) {
            \Session::flash('mensagem_sucesso', $this->msgDelete);
            return Redirect::back();
        } else {
            \Session::flash('mensagem_erro', $this->msgDeleteErro);
        }
    }
    
    private function setFaculdadesInView(): void {
        $this->arrayViewData['faculdades'] = Faculdade::all()
            ->where('status', '=', 1)
            ->pluck('fantasia', 'id');
    }

    /**
     * @param string $type
     * @return Application|Factory|View
     */
    public function renderView(string $type) {
        return view(
            $this->arrayViewData['modulo']['moduloDetalhes']->view . $type, 
            $this->arrayViewData
        );
    }
    
    private function setCertificadosInView(): void {
        $this->arrayViewData['lista_certificados'] = CertificadoLayout::where('status', '=', 1)
            ->pluck('titulo', 'id')
            ->prepend('Estrutura não possui certificado', '');
    }
}
