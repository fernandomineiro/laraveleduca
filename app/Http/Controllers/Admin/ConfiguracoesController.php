<?php

namespace App\Http\Controllers\Admin;

use App\ConfiguracoesEstilosVariaveis;
use App\ConfiguracoesVariaveis;
use App\CursoCategoria;
use App\Faculdade;
use App\Http\Controllers\ConfigPerfilEducazController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

/**
 * Class Configuracoes
 * @package App\Http\Controllers\Admin
 * Classe de controle das configurações do aplicativo
 * O método salvar e atualizar vale para todos os itens configuráveis,
 * ficando dentro da request AJAX qual será a configuração a ser criada ou atualizada.
 * Como não existe a necessidade de extender a classe todos os métodos serão criados aqui.
 */
class ConfiguracoesController extends ConfigPerfilEducazController{

    public function index(Request $request) {

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['objlista'] = $this->GetListConfiguracoes($request->all());

        $this->arrayViewData['aFaculdades'] = Faculdade::all()->where('status', '=', 1)
                                                ->pluck('fantasia', 'id')
                                                ->prepend('Selecione','');

        $this->arrayViewData['paginas'] = [
            '' => 'Selecione',
            'home' => 'home',
            'cursosonline' => 'cursos-online',
            'cursospresenciais' => 'cursos-presenciais',
            'cursosremotos' => 'cursos-remotos',
            'eventos' => 'eventos',
            'trilhasconhecimento' => 'trilhas-conhecimento',
            'professores' => 'professores',
            'assinaturas' => 'assinaturas',
            'biblioteca' => 'biblioteca',
            'sejaumprofessor' => 'seja-um-professor',
            'Banner para Categoria' => CursoCategoria::select(DB::raw(" concat('categoria_',id) as categoria, titulo "))
                ->where('status','=','1')
                ->get()
                ->pluck('titulo','categoria')
        ];

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);

    }

    public function incluir(){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        $this->arrayViewData['faculdades'] = $this->GetAllFaculdades();
        $this->arrayViewData['faculdades']->prepend('Selecione','');
        $this->arrayViewData['lista_status'] = array('0' => 'Inativo', '1' => 'Ativo');
        $this->arrayViewData['paginas'] = [
            'home' => 'home',
            'cursosonline' => 'cursos-online',
            'cursospresenciais' => 'cursos-presenciais',
            'cursosremotos' => 'cursos-remotos',
            'eventos' => 'eventos',
            'trilhasconhecimento' => 'trilhas-conhecimento',
            'professores' => 'professores',
            'assinaturas' => 'assinaturas',
            'biblioteca' => 'biblioteca',
            'sejaumprofessor' => 'seja-um-professor',
            'Banner para Categoria' => CursoCategoria::select(DB::raw(" concat('categoria_',id) as categoria, titulo "))
                                            ->where('status','=','1')
                                            ->get()
                                            ->pluck('titulo','categoria')
        ];
        $this->arrayViewData['configVariaveis'] = ConfiguracoesVariaveis::where('editavel', '=', '1')->get();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    public function editar($id){

        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        $this->arrayViewData['faculdades'] = $this->GetAllFaculdades();
        $this->arrayViewData['lista_status'] = array('0' => 'Inativo', '1' => 'Ativo');
        $this->arrayViewData['paginas'] = [
            'home' => 'home',
            'cursosonline' => 'cursos-online',
            'cursospresenciais' => 'cursos-presenciais',
            'cursosremotos' => 'cursos-remotos',
            'eventos' => 'eventos',
            'trilhasconhecimento' => 'trilhas-conhecimento',
            'professores' => 'professores',
            'sejaumprofessor' => 'seja-um-professor',
            'Banner para Categoria' => CursoCategoria::select(DB::raw(" concat('categoria_',id) as categoria, titulo "))
                                            ->where('status','=','1')
                                            ->get()
                                            ->pluck('titulo','categoria')
        ];
        $this->arrayViewData['obj'] = $this->GetObjConfiguracao($id);
        $this->arrayViewData['configVariaveis'] = ConfiguracoesVariaveis::where('editavel', '1')->where('status', 1)->get();

        $estilosVariaveis = ConfiguracoesEstilosVariaveis::where('fk_configuracoes_estilos_id', $id)->get();
        $dataEstilos = [];
        foreach ($estilosVariaveis as $estilo) {
            $dataEstilos[$estilo->fk_configuracoes_variaveis_id] = $estilo->value;
        }

        $this->arrayViewData['configEstilosVariaveis'] = $dataEstilos;
        $data = $this->getRouterByUri();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);

    }


    //------------------------------------------------------------------------------------------------------------------

    /**
     * Este método tanto cria o registro como atualiza
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request, $id = 0, $delete = false){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        try {
            $result = $this->salvarRegistro($request, $id, $delete);
        } catch (\Exception $error) {
            $this->msgInsertErro = $error->getMessage();
            Session::flash('mensagem_erro', $this->msgInsertErro)->withErrors($this->validatorMsg)->withInput();
            return Redirect::back();
        }

        switch ($result) {
            case -4:
                Session::flash('mensagem_erro', $this->msgErroRegistroExiste);
                return Redirect::back();
            case -3:
                Session::flash('mensagem_erro', $this->msgRegistroInexistente);
                return Redirect::back();
            case -2:
                Session::flash('mensagem_erro', $this->msgAcaoIndisponivel);
                return Redirect::back();
            case -1:
                return Redirect::back()->withErrors($this->validatorMsg)->withInput();
            case 1:
                Session::flash('mensagem_sucesso', $this->msgInsert);
                return Redirect::back();
            case 2:
                Session::flash('mensagem_erro', $this->msgInsertErro);
                return Redirect::back()->withErrors($this->validatorMsg)->withInput();
            case 4:
                Session::flash('mensagem_sucesso', $this->msgUpdate);
                return Redirect::back();
            case 5:
                Session::flash('mensagem_erro', $this->msgUpdateErro);
                return Redirect::back()->withErrors($this->validatorMsg)->withInput();
            case 6:
                Session::flash('mensagem_sucesso', $this->msgDelete);
                return Redirect::back();
            case 7:
                Session::flash('mensagem_erro', $this->msgDeleteErro);
                return Redirect::back()->withErrors($this->validatorMsg)->withInput();
            case 8:
                return Redirect::back()->withErrors('Não é possível criar um registro sem uma imagem!')->withInput();
        }
    }

    /**
     * Utiliza o método salvar para executar as ações de UPDATE
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);

        return $this->salvar($request, $id);

    }

    /**
     * Utiliza o método salva para executar as ações de exclusão (desativação)
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id, Request $request){

        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        return $this->salvar($request, $id, true);
    }

    public function recriarEstilos() {

        $oFaculdades = Faculdade::all()->where('status', 1);
        $sResult = '';

        foreach ($oFaculdades as $oFaculdade) {
            try {
                $scss = new DynamicScssPhpController();
                $scss->index($oFaculdade->id);

                $sResult .= '<div class="row">Estilos da faculdade: '. $oFaculdade->razao_social . ' recriado com sucesso! </div>';

            } catch (\Exception $error) {
                $sResult .= '<div class="row">Erro ao recriar os estilos da faculdade: ' . $oFaculdade->razao_social . ' Error: '. $error->getMessage() . '</div>';
            }
            $sResult .= '<div class="clear"></div>';
        }

        $this->msgInsert = $sResult;

        Session::flash('custom_message', $this->msgInsert);
        return Redirect::back();
    }
    //------------------------------------------------------------------------------------------------------------------
}
