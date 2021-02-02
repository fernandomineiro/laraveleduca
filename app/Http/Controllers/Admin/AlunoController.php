<?php

namespace App\Http\Controllers\Admin;

use App\Aluno;
use App\Assinatura;
use App\CursoTag;
use App\CursoTagAluno;
use App\CursoTipo;
use App\EstruturaCurricular;
use App\EstruturaCurricularUsuario;
use App\Http\Requests\AlunoRequest;
use App\Pedido;
use App\PedidoItem;
use App\Usuario;
use App\UsuarioAssinatura;
use App\ViewUsuariosAlunos;
use App\PedidoItemSplit;
use App\Services\AlunoService;
use App\Helper\EducazMail;
use App\Imports\AlunoImport;
use App\Exports\AlunosExport;
use App\Http\Controllers\PessoasController;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AlunoController extends PessoasController {

    /** @var AlunoService $alunoService */
    protected $alunoService;
    protected $urlRedirect = '/admin/aluno/index';
    
    public function __construct(AlunoService $alunoService) {
        $this->alunoService = $alunoService;
    }

    /**
     * Listar Alunos
     * @return Factory|RedirectResponse|View
     */
    public function index() {
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        return $this->renderView('.lista');
    }
    
    public function getMultiFilterSelectDataAluno() {
        return $this->alunoService->listAlunosDataTable();
    }

    /**
     * Incluir Alunos
     * @return Factory|RedirectResponse|View
     * @throws BindingResolutionException
     */
    public function incluir() {
        $this->initViewAlunos();
        return $this->renderView('.formulario');
    }

    /**
     * Editar Alunos
     *
     * @param integer $id
     * @return Factory|RedirectResponse|View
     * @throws BindingResolutionException
     */
    public function editar($id) {
        $this->initViewAlunos();
        
        $aluno = $this->alunoService->getAluno($id);
        
        $this->arrayViewData['objAluno'] = $aluno;
        $this->arrayViewData['objEndereco'] = $aluno->endereco;
        $this->arrayViewData['objUsuario'] = $aluno->usuario;

        $this->setCursoAluno($aluno);

        $this->arrayViewData['objPedidos'] = Pedido::lista($this->arrayViewData['objUsuario']->id);
        $this->arrayViewData['objEstruturaUsuario'] = EstruturaCurricularUsuario::where('fk_usuario', $this->arrayViewData['objUsuario']->id)->with('estrutura')->get();
        $this->arrayViewData['objEstrutura'] = EstruturaCurricular::where('status', '1')->pluck('titulo', 'id')->toArray();
        
        $this->arrayViewData['objTags'] = CursoTag::select(
            DB::raw("CONCAT(id,' - ',tag) AS tag"), 'id')->pluck('tag', 'id');

        $this->arrayViewData['objCursosTipo'] = CursoTipo::select( 'titulo', 'id')->pluck('titulo', 'id');
        $this->arrayViewData['objTagsAluno'] = CursoTagAluno::with('tagsAluno')->where('fk_aluno', $id)->get();
        $this->arrayViewData['objAssinaturas'] = Assinatura::where('assinatura.status', 1)->pluck('titulo', 'id');
        

        return $this->renderView('.formulario');
    }

    /**
     * Salvar Alunos
     *
     * @param AlunoRequest $request
     * @return RedirectResponse
     */
    public function salvar(AlunoRequest $request) {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        
        $this->alunoService->setFile($request->file('foto'));

        return $this->_return($this->alunoService->salvar($request->all()), $this->urlRedirect);
    }

    /**
     * Atualizar Alunos
     *
     * @param integer $id
     * @param AlunoRequest $request
     * @return RedirectResponse
     */
    public function atualizar($id, AlunoRequest $request) {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        
        $this->alunoService->setFile($request->file('foto'));

        return $this->_return($this->alunoService->atualizar($request->all()), $this->urlRedirect);
    }

    /**
     * Deletar Alunos
     *
     * @param integer $id
     * @return RedirectResponse
     */
    public function deletar($id) {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        return $this->deletaRegistro(Aluno::findOrFail($id));
    }

    public function saveTagsAluno(Request $request) {

        try {
            $data = $request->all();
            if (empty($data['id'])) {
                throw new \Exception('É necessário informar um aluno!');
            }

            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    CursoTagAluno::updateOrCreate(
                        ['fk_aluno' => $data['id'], 'fk_cursos_tag' => $tag],
                        ['fk_aluno' => $data['id'], 'fk_cursos_tag' => $tag]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Tags inseridas com sucesso']);

        } catch (\Exception $message) {
            return response()->json(['success' => false, 'message' => $message->getMessage()]);
        }
    }

    public function tagsUsuario(Request $request) {

        try {
            $data = $request->all();
            if (empty($data['id'])) {
                throw new \Exception('É necessário informar um aluno!');
            }

            $this->arrayViewData['objTagsAluno'] = CursoTagAluno::with('tagsAluno')->where('fk_aluno', $data['id'])->get();
            $this->arrayViewData['objTags'] = CursoTag::select(
                DB::raw("CONCAT(id,' - ',tag) AS tag"), 'id')->pluck('tag', 'id');

            $this->arrayViewData['objAluno'] = Aluno::select('*')->where('id', $data['id'])->first();

            return view('usuario.aluno.blocks.list', $this->arrayViewData)->render();

        } catch (\Exception $message) {
            return response()->json(['success' => false, 'message' => $message->getMessage()]);
        }
    }

    public function deletarTagAluno(Request $request) {

        try {
            $data = $request->all();
            if (empty($data['id'])) {
                throw new \Exception('É necessário informar um aluno!');
            }

            $tagAluno = CursoTagAluno::find($data['id']);
            $tagAluno->delete();

            return response()->json(['success' => true, 'message' => 'Tags deletada com sucesso']);

        } catch (\Exception $message) {
            return response()->json(['success' => false, 'message' => $message->getMessage()]);
        }
    }

    public function criarPedidoAluno(Request $request, $id) {

        $assinatura = Assinatura::findOrFail($request->get('produto'));
        $aluno = Aluno::findOrFail($id);

        $pedido = [
            "criacao" => date('Y-m-d H:i:s'),
            'fk_faculdade' => $aluno->fk_faculdade_id,
            'fk_usuario' => $aluno->fk_usuario_id,
            'valor_bruto' => $assinatura->valor_de,
            'valor_desconto' => $assinatura->valor_de,
            'valor_liquido' => 0,
            'valor_imposto' => 0,
            'status' => 2
        ];

        try {
            DB::beginTransaction();

            $pedidoObjeto = new Pedido($pedido);
            $id_pedido = $pedidoObjeto->save();

            if (empty($id_pedido)) {
                throw new \Exception('Erro ao criar o pedido');
            }

            $pedidoObjeto->pid = date('dmY') . '-' . $pedidoObjeto->id . '-' . $aluno->fk_usuario_id;
            $pedidoObjeto->save();

            $pedido_item = [
                'valor_bruto' => $assinatura->valor_de,
                'valor_desconto' => $assinatura->valor_de,
                'valor_imposto' => 0,
                'valor_liquido' => 0,
                'status' => 1,
                'fk_pedido' => $pedidoObjeto->id,
                'fk_curso' => null,
                'fk_evento' => null,
                'fk_trilha' => null,
                'fk_assinatura' => $assinatura->id
            ];

            $pedidoItemObjeto = new PedidoItem($pedido_item);
            $pedidoItemObjeto->save();

            $usuarioAssinatura = [
                'fk_assinatura' => $assinatura->id,
                'fk_pedido' => $pedidoObjeto->id,
                'status' => 1,
                'fk_usuario' => $aluno->fk_usuario_id,
            ];
            
            $usuarioAssinaturaObjeto = new UsuarioAssinatura($usuarioAssinatura);
            $usuarioAssinaturaObjeto->save();
            
            DB::commit();

            return $this->_return([
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro inserido com sucesso!',
                'validatorMessage' => null
            ]);

        } catch (\InvalidArgumentException $e){
            DB::rollBack();

            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            $this->validatorMsg = $e->getMessage();
            return Redirect::back()->withErrors($this->validatorMsg)->withInput();

        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);

            $this->validatorMsg = $e->getMessage();
            return Redirect::back()->withErrors($this->validatorMsg)->withInput();
        }
    }

    public function adicionarEstrutura(Request $request, $id) {
        try {

            $data = $request->all();
            $aluno = Aluno::findOrFail($id);

            EstruturaCurricularUsuario::updateOrCreate(
                ['fk_estrutura' => $data['fk_estrutura'], 'fk_usuario' => $aluno->fk_usuario_id],
                ['fk_estrutura' => $data['fk_estrutura'], 'fk_usuario' => $aluno->fk_usuario_id]
            );

            return $this->_return([
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro inserido com sucesso!',
                'validatorMessage' => null
            ]);

        } catch (\Exception $message) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($message);

            $this->validatorMsg = $message->getMessage();
            return Redirect::back()->withErrors($this->validatorMsg)->withInput();
        }
    }

    public function deletarEstrutura($id) {
        try {

            $estruturaAluno = EstruturaCurricularUsuario::findOrFail($id);
            $estruturaAluno->delete();

            return $this->_return([
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro deletado com sucesso!',
                'validatorMessage' => null
            ]);

        } catch (\Exception $message) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($message);

            $this->validatorMsg = $message->getMessage();
            return Redirect::back()->withErrors($this->validatorMsg)->withInput();
        }
    }

    public function importar(Request $request) {
        try {
            Excel::import(new AlunoImport(), request()->file('arquivo'));

            return $this->_return([
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Usuários importados com sucesso!',
                'validatorMessage' => null
            ]);

        } catch (\Exception $error) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($error);

            $this->validatorMsg = $error->getMessage();
            return Redirect::back()->withErrors($this->validatorMsg)->withInput();
        }
    }

    public function exports()
    {
        return Excel::download(new AlunosExport, 'users.xlsx');

        return 'Baixado';
    }

    /**
     * Exportar Alunos
     *
     * @param Request $request
     * @return AlunosExport
     */
    public function exportar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new AlunosExport(), 'alunos.' . strtolower($request->get('export-to-type')) . '');

    }

    /**
     * @param string $view
     * @return Application|Factory|View
     */
    public function renderView(string $view) {
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . $view, $this->arrayViewData);
    }

    /**
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    private function initViewAlunos() {
        if (!$this->validateAccess(Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['estados'] = app()->make(\App\Services\EstadoService::class)->getStatesForSelect();
        $this->arrayViewData['cidades'] = app()->make(\App\Services\CidadeService::class)->getCitiesForSelect();
        $this->arrayViewData['lista_faculdades'] =
            app()->make(\App\Services\FaculdadeService::class)->getFaculdadeComFantasiaParaSelect();
        $this->arrayViewData['cursos'] = app()->make(\App\Services\CursoService::class)->getCursosForSelect();
        $this->arrayViewData['semestres'] = app()->make(\App\Services\SemestreService::class)->getSemestreForSelect();
        $this->arrayViewData['lista_generos'] = app()->make(\App\Services\GeneroService::class)->getGeneroForSelect();
    }

    /**
     * @param $aluno
     * @return bool
     */
    private function eCursoEducaz($aluno): bool {
        return !empty($aluno->curso) && $aluno->universidade != 'outro';
    }

    /**
     * @param $aluno
     */
    private function setCursoAluno($aluno): void {
        $this->arrayViewData['cursoAluno'] = $aluno->curso_outro;
        if ($this->eCursoEducaz($aluno)) {
            $curso = app()->make(\App\Services\CursoService::class)->getCurso($aluno->curso);
            $this->arrayViewData['cursoAluno'] = !empty($curso) ? $curso->titulo : $aluno->curso;
        }
    }
}

