<?php

namespace App\Http\Controllers\Admin;

use App\Banco;
use App\Http\Requests\ProfessorRequest;
use App\Professor;
use App\Faculdade;
use App\Services\ProfessorService;
use App\Usuario;
use App\Exports\ProfessoresExport;
use App\Helper\EducazMail;
use App\Http\Controllers\PessoasController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProfessorController extends PessoasController {
    /** @var ProfessorService  */
    protected $professorService;
    protected $urlRedirect = '/admin/professor/index';
    
    public function __construct(ProfessorService $professorService) {
        $this->professorService = $professorService;
    }

    /**
     * @desc Listar Professores
     *
     * @return Factory|RedirectResponse|View
     */
    public function index() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }
        
        return $this->renderView('.lista');
    }

    /**
     * @return mixed
     */
    public function getMultiFilterSelectDataProfessor() {
        return $this->professorService->listaProfessoresDataTable();
    }
    
    /**
     * @desc Incluir Professores
     * @return Factory|RedirectResponse|View
     */
    public function incluir() {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->initViewProfessor();
        return $this->renderView('.formulario');
    }

    /**
     * @desc Editar Professores
     *
     * @param integer $id
     * @return Factory|RedirectResponse|View
     */
    public function editar($id) {
        
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->initViewProfessor();
        
        $professor = $this->professorService->getProfessor($id);
        
        $this->arrayViewData['objProfessor'] = $professor;
        $this->arrayViewData['objEndereco'] = $professor->endereco;
        $this->arrayViewData['objUsuario'] = $professor->usuario;
        $this->arrayViewData['objConta'] = $professor->conta;

        $this->arrayViewData['repasse_manual'] = $this->professorService->isRepasseManual($this->arrayViewData['objUsuario']->wirecard_account_id);
        
        return $this->renderView('.formulario'); 
    }

    /**
     * Aprova usuário professor
     *
     * @param integer $usuarioId
     * @return RedirectResponse
     */
    public function aprovar($usuarioId) {

        try {
            $usuario = Usuario::withTrashed()->find($usuarioId);

            if ($usuario) {
                $usuario->status = 1;
                $usuario->update();
            }

            $professor = Professor::withTrashed()->where('fk_usuario_id', '=', $usuarioId)->first();
            if ($professor) {
                $professor->status = 1;
                $professor->update();
            }

        } catch (Exception $e) {
            \Session::flash('mensagem_erro', 'Não foi possível aprovar usuário!');
            return Redirect::back();
        }

        $this->sendEmailCadastroEmAnalise($usuario->email, $usuario->fk_faculdade_id);

        \Session::flash('mensagem_sucesso', 'Usuário aprovado com sucesso!');
        return Redirect::back();
    }


    /**
     * @desc Salvar Professores
     *
     * @param ProfessorRequest $request
     * @return RedirectResponse|Redirector
     */
    public function salvar(ProfessorRequest $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $this->professorService->setFile($request->file('foto'));

        return $this->_return($this->professorService->salvar($request->all()), $this->urlRedirect);
    }

    /**
     * Atualizar Professor
     *
     * @param ProfessorRequest $request
     * @return RedirectResponse
     */
    public function atualizar(ProfessorRequest $request) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        $this->professorService->setFile($request->file('foto'));
        return $this->_return($this->professorService->atualizar($request->all()), $this->urlRedirect);
    }

    /**
     * Deletar Professor
     *
     * @param integer $id
     * @return RedirectResponse
     */
    public function deletar($id) {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return $this->_return($this->deletaRegistro(Professor::findOrFail($id)));
    }

    /**
     * Exportar Professores
     *
     * @param Request $request
     * @return ProfessoresExport
     */
    public function exportar(Request $request) {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new ProfessoresExport(),
            'professores.' . strtolower($request->get('export-to-type')) . '');

    }

    public function sendEmailCadastroEmAnalise($email, $fk_faculdade) {
        $EducazMail = new EducazMail(7);

        $faculdade = Faculdade::select('url')->where('id', 7)->first();

        $data = $EducazMail->notificarAprovacaoDeProfessor([
            'messageData' => [
                'email' => $email,
                'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                'link_login' => $faculdade->url
            ]
        ]);
    }

    private function initViewProfessor(): void {
        $this->arrayViewData['tipoParceiro'] = 'professores';
        $this->arrayViewData['repasse_manual'] = false;

        $this->arrayViewData['lista_bancos'] = Banco::all()
            ->where('status', 1)->pluck('titulo', 'id');

        $this->arrayViewData['estados'] = app()->make(\App\Services\EstadoService::class)->getStatesForSelect();
        $this->arrayViewData['cidades'] = app()->make(\App\Services\CidadeService::class)->getCitiesForSelect();
        $this->arrayViewData['lista_generos'] = app()->make(\App\Services\GeneroService::class)->getGeneroForSelect();
    }
}
