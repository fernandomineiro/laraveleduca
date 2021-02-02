<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use PHPMailer\PHPMailer\PHPMailer;
use App\ViewUsuariosMenus;
use App\ViewPerfilModulosAcoes;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $redirecTo;
    public $routeNameIndex;
    public $userLogged;


    public $msgInsert = '';
    public $msgUpdate = '';
    public $msgDelete = '';
    public $msgAcaoIndisponivel = '';
    public $msgRegistroInexistente = '';

    public $msgInsertErro = '';
    public $msgUpdateErro = '';
    public $msgDeleteErro = '';

    public $arrayViewData;
    public $module_view;

    public $validatorMsg;
    public $msgErroRegistroExiste;

    public $sistemaURL;
    public $sistmaNome;

    public $sistemaSendFromEmail;
    public $sistemaSendFromNome;

    public function __construct()
    {
        //Definindo como array ao criar a classe
        $this->arrayViewData = array();
        $this->sistemaURL = 'http://3.81.68.4/admin';
        $this->sistmaNome = 'PLATAFORMA EDUCAZ';

        $this->sistemaSendFromEmail = 'daniloborgespereira@gmail.com';
        $this->sistemaSendFromNome = $this->sistmaNome;
    }

    /**
     * Verificando se o usuario tem o devido acesso ao Controller e Action Chamado
     * @param $session
     * @return bool
     */
    public function validateAccess($user, $arraView = true)
    {
        if (!Auth()->guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        //Definindo o usuario logado com dados na Session
        $this->userLogged = !empty($user) ? $user : auth()->user();

        //Rota de redirecionamento caso tente uma ação não permitida
        $this->redirecTo = 'admin.logout';

        if (!$this->userLogged) return false;

        //Obtendo dados do Controller e Action chmados
        $data = explode('@', request()->route()->getAction()['controller']);


        //Pegando o nome do controller conforme registrado no banco de dados
        $controllerNameShow = array_reverse(explode('\\', $data[0]))[0];
        $actionName = $data[1];

        //Verificando se tem permissão de acesso ao Módulo/Controller
        $dataAccess = $this->getModuloPermissoes($controllerNameShow, $this->userLogged->fk_perfil, $actionName);
        $menus = $this->getMenus($this->userLogged->fk_perfil);
        //Desloga o usuario caso tente acesar algo que nao tem permissao, pode-se mudar para uma pagina com a mensagem sem acesso
        if (count($dataAccess['permissoes']) == 0) return false;
        //FIM Validacao de acesso

        //Mensagens de sucesso como o nome do módulo
        $this->msgInsert = $dataAccess['moduloDetalhes']->modulo . ': Cadastrado com Sucesso!';
        $this->msgUpdate = $dataAccess['moduloDetalhes']->modulo . ': Atualizado com Sucesso!';
        $this->msgDelete = $dataAccess['moduloDetalhes']->modulo . ': Excluído com Sucesso!';

        //Mensagens de erro como o nome do módulo
        $this->msgInsertErro = $dataAccess['moduloDetalhes']->modulo . ': Não foi possível inserir o registro!';
        $this->msgUpdateErro = $dataAccess['moduloDetalhes']->modulo . ': Não foi possível atualizar o registro!';
        $this->msgDeleteErro = $dataAccess['moduloDetalhes']->modulo . ': Não foi possível excluir o registro!';

        $this->msgAcaoIndisponivel = $dataAccess['moduloDetalhes']->modulo . ': Esta ação não está disponível neste módulo!';

        $this->msgErroRegistroExiste = $dataAccess['moduloDetalhes']->modulo;
        $this->msgRegistroInexistente = $dataAccess['moduloDetalhes']->modulo;

        //
        //Utilizar nas views
        if ($arraView)
            //Definindo dados que vão para o controller e view
            $this->arrayViewData = array(
                'modulo' => $dataAccess,                                        //Dados do módulo, permissões, nome de rota, caminho view e url
                'userData' => $this->userLogged,                                //Dados do usuário logado
                'userMenu' => $menus,                                           //Menu permitido ao usuário logado
                'lista_status' => array('0' => 'Inativo', '1' => 'Ativo'),      //Status do registro
                'lista_sim_nao' => array('0' => 'Não', '1' => 'Sim'));          //Lista para escolha Sim/Não

        else
            $this->arrayViewData = array('modulo' => $dataAccess);              //Utilizado somente no Controller

        return true;
    }

    /**
     * Insere dados de auditoria
     * @param $data
     * @param bool $new
     * @return mixed
     */
    public function insertAuditData($data, $new = true)
    {
        //Caso o registro seja novo
        if ($new) {
            $data['criacao'] = date('Y-m-d H:i:s');      //Data e Hora de criação não se altera com Atualiza/Deletar
            $data['fk_criador_id'] = $this->userLogged->id;     //Id do usuário que criou não se altera com Atualiza/Deletar
            $data['status'] = 1;                                //Alteravel somente no caso de DELTE
        }

        //Utilizado tanto em novo, atualização e delte
        $data['atualizacao'] = date('Y-m-d H:i:s');     //Data e Hora de criação sempre se altera com Atualiza/Deletar
        $data['fk_atualizador_id'] = $this->userLogged->id;     //Id do usuário que criou, alterou ou excluiu sempre altera Atualiza/Deletar

        return $data;
    }

    /**
     * Insere dados de auditoria
     * @param $data
     * @param bool $new
     * @return mixed
     */
    public function insertAuditDataApi($data, $new = true)
    {
        //Caso o registro seja novo
        if ($new) {
            $data['criacao'] = date('Y-m-d H:i:s');      //Data e Hora de criação não se altera com Atualiza/Deletar
            $data['fk_criador_id'] = null;     //Id do usuário que criou não se altera com Atualiza/Deletar
            $data['status'] = 1;                                //Alteravel somente no caso de DELTE
        }

        //Utilizado tanto em novo, atualização e delte
        $data['atualizacao'] = date('Y-m-d H:i:s');     //Data e Hora de criação sempre se altera com Atualiza/Deletar
        $data['fk_atualizador_id'] = null;     //Id do usuário que criou, alterou ou excluiu sempre altera Atualiza/Deletar

        return $data;
    }

    /**
     * Insere dados de auditoria
     * @param $data
     * @param bool $new
     * @return mixed
     */
    public function insertAuditData2($data, $new = true)
    {
        //Caso o registro seja novo
        if ($new) {
            $data['criacao'] = date('Y-m-d H:i:s');      //Data e Hora de criação não se altera com Atualiza/Deletar
            $data['fk_criador'] = $this->userLogged->id;     //Id do usuário que criou não se altera com Atualiza/Deletar
            $data['status'] = 1;                                //Alteravel somente no caso de DELTE
        }

        //Utilizado tanto em novo, atualização e delte
        $data['atualizacao'] = date('Y-m-d H:i:s');     //Data e Hora de criação sempre se altera com Atualiza/Deletar
        $data['fk_atualizador'] = $this->userLogged->id;     //Id do usuário que criou, alterou ou excluiu sempre altera Atualiza/Deletar

        return $data;
    }

    /**
     * Obter o menu habilitado para o usuario
     * @param $idPrefil
     * @return ViewUsuariosMenus[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getMenus($idPrefil)
    {
        //Chama view que carrega o menu conforme o perfil do usuário
        $data = ViewUsuariosMenus::all()->where('fk_perfil_id', $idPrefil);
        return $data;
    }

    /**
     * @param $controller
     * @param $idPrefil
     * @param $action
     * @return array
     */
    public function getModuloPermissoes($controller, $idPrefil, $action)
    {

        //Verifica se o módulo é de configurações
        if ($controller != 'ConfiguracoesController') {
            //Carrega as permissões para aquele determinado perfil no controller correspondente
            $data = ViewPerfilModulosAcoes::all()
                ->where('controller', '=', $controller)
                ->where('fk_perfil_id', '=', $idPrefil);
        } else {
            //se for de configurações query diferenciada
            if (Route::currentRouteName() != null) {
                $moduloConfig = explode('.', Route::currentRouteName())[1];
                $data = ViewPerfilModulosAcoes::all()
                    ->where('rota', '=', $moduloConfig)
                    ->where('fk_perfil_id', '=', $idPrefil);
            } else {
                $urlData = explode('/', str_replace('/admin/', '', \Request::getRequestUri()))[0];

                $data = ViewPerfilModulosAcoes::all()
                    ->where('uri', '=', $urlData)
                    ->where('fk_perfil_id', '=', $idPrefil);
            }
        }

        $modulo = new \stdClass();
        $permissoesAcesso = [];

        $ret = false;

        foreach ($data as $permissoes) {
            //Verificando se o usuario tem acesso a rota chamada, continua no looping pois pode ser um objeto incial como final da lista
            if (trim(strtolower($permissoes->acoes)) == trim(strtolower($action)))
                $ret = true;

            //Só executa esta opção na 1ª interação do laço
            if (count($permissoesAcesso) == 0) {
                $modulo->menu = $permissoes->menu;                           //Prefixo do módulo conforme o Menu
                $modulo->modulo = $permissoes->modulo;                       //Nome do módulo
                $modulo->rota = $permissoes->rota;                           //Nome de rota no laravel Ex: ... ->name('admin.usuariosmodulos') ...
                $modulo->uri = $permissoes->uri;                             //URL da rota no laravel  Ex: Route::get('/usuarios_modulos' ...
                $modulo->view = $permissoes->caminho_view;                  //Caminho utilizado pela view no larvel Ex: return view('projeto_tipo.lista'
                $modulo->controller = $permissoes->controller;
                $this->module_view = $modulo->view;
            }

            $obj = new \stdClass();
            $obj->acao = $permissoes->acoes;            //Ação habilitada para o usuário
            $obj->elemento = $permissoes->elemento;     //Elemento que realiza a determinada ação

            $permissoesAcesso[] = $obj;
        }

        //Retorno
        //1 - Objeto para ser utilizado tanto no controller como na view
        //2 - Array de objetos para serem utilizados na view, suas respectivas ações permitidas
        if ($ret)
            return array('moduloDetalhes' => $modulo, 'permissoes' => $permissoesAcesso);

        return array('moduloDetalhes' => array(), 'permissoes' => array());
    }

    /**
     * Envio de e-mail
     * @param $emailText - Corpo do e-mail
     * @param $sendFrom - Email que está enviando + nome (array)
     * Ex:
     * $sendFrom[0] - Email
     * $sendFrom[1] - Nome
     * @param $subject - Assunto
     * @param $arrayTo - Enviando para: Email + Nome (arrary)
     * Ex:
     * $arrayTo[0][0] - Email
     * $arrayTo[0][0] - Nome
     * @param $arrayCC - Enviando cópia para: Email + Nome (arrary)
     * Ex:
     * $arrayCC[0][0] - Email
     * $arrayCC[0][0] - Nome
     * @param $arrayCCO - Enviando cópia oculta para: Email + Nome (arrary)
     * Ex:
     * $arrayCC[0][0] - Email
     * $arrayCC[0][0] - Nome
     * @return bool
     */
    public function sendEmail($emailText, $sendFrom, $subject, $arrayTo, $arrayCC, $arrayCCO)
    {
        $mail = new PHPMailer;
        $mail->isSMTP();

        $mail->SMTPDebug = 1;
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = "fcampossistemas@gmail.com";
        $mail->Password = "Skc240861@";

        $mail->setFrom($sendFrom[0], $sendFrom[1]);

        foreach ($arrayTo as $to) {
            $mail->addAddress($to[0], $to[1]);
        }

        foreach ($arrayCC as $toCC) {
            $mail->AddCC($toCC[0], $toCC[1]);
        }

        foreach ($arrayCCO as $toCCO) {
            $mail->addBCC($toCCO[0], $toCCO[1]);
        }

        $mail->Subject = $subject;

        $mail->msgHTML($emailText);

        $mail->AltBody = $subject;

        if (!$mail->send()) {
            var_dump('ERRO!');
            die();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Transforma a data no padrão banco
     * @param $data
     * @return string
     */
    public function transformaDataSql($data)
    {
        return implode('-', array_reverse(explode('/', $data)));
    }

    /**
     * Transforma o valor no padrão do banci=o
     * @param $data
     * @return mixed
     */
    public function tranformaValorSql($data)
    {
        $data = str_replace('%', '', $data);
        $data = str_replace('$', '', $data);
        $data = str_replace('R$', '', $data);
        $data = str_replace('.', '', $data);
        $data = str_replace(',', '.', $data);
        return $data;
    }

    /**
     * Transforma o valor no padrão do banci=o
     * @param $data
     * @return mixed
     */
    public function tranformaValorSqlShare($data)
    {
        $data = str_replace('%', '', $data);
        $data = str_replace(',', '.', $data);
        return $data;
    }

    /**
     * Remove as mascaras sem utilização (telefones)
     * @param $data
     * @return mixed
     */
    public function removerMascaraSemUtilizacao($data) {
        return str_replace('_', '', $data);
    }

    /**
     * Lista todas as rortas do sistema
     */
    protected function listarTodasASRotas()
    {
        $r = app('router')->getRoutes();
        foreach ($r as $value) {
            echo($value->uri() . '<br/>');
        }
    }

    /**
     * Gerador de senha do sistema
     * @param $tamanho
     * @param $maiusculas
     * @param $minusculas
     * @param $numeros
     * @param $simbolos
     * @return bool|string
     */
    public function gerarSenha($tamanho, $maiusculas, $minusculas, $numeros, $simbolos)
    {
        $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ";
        $mi = "abcdefghijklmnopqrstuvyxwz";
        $nu = "0123456789";
        $si = "!@#$%¨&*()_+=";
        $senha = '';

        if ($maiusculas)
            $senha .= str_shuffle($ma);

        if ($minusculas)
            $senha .= str_shuffle($mi);

        if ($numeros)
            $senha .= str_shuffle($nu);

        if ($simbolos)
            $senha .= str_shuffle($si);

        return substr(str_shuffle($senha), 0, $tamanho);
    }

    /**
     * Transforma somente em números
     * @param $str
     * @return string|string[]|null
     */
    public function soNumero($str)
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    public function tirarAcentos($string){
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"), str_replace(" ", "", $string));
    }

    /**
     * @param string $view
     * @return Application|Factory|View
     */
    public function renderView(string $view) {
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . $view, $this->arrayViewData);
    }
}
