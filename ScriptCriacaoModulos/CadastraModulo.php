<?php
/**
 * Não apagar os seguintes itens
 * Diertório VENDOR
 * composer.json
 * composer.lock
 *
 * Dar permissão de acesso de leitura e escrita 755 ou 777 (só se for local)
 *
 * De forma alguma rodar composer install ou composer update na pasta este projeto está fora do LARAVEL!
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

/**
 * Check se o arquivo .env existe
 */
if (!file_exists('.env')) {
    $path = dirname(__FILE__);
    $tmpPath = explode('/', $path);
    $path = array();

    for ($i = 0; $i < (count($tmpPath) - 1); $i++) {
        $path[] = $tmpPath[$i];
    }

    $path = implode('/', $path) . '/';

    if (!file_exists($path . '.env')) {
        print('Nao foi possivel encontrar o arquivo .env em:' . $path . PHP_EOL);
        print('Verifique se o projeto contém em sua raiz o arquivo .env' . PHP_EOL);
        exit();
    }

    copy($path . '.env', dirname(__FILE__) . '/.env');
}

require_once 'vendor/autoload.php';
require_once 'Database.php';

use Dotenv\Dotenv;

/**
 *Querys SQL utilizadas tomadas como base o 1º módulo criado
 * -- SELECT * FROM usuarios_modulos WHERE id=1;
 * -- SELECT * FROM usuarios_modulos_x_acoes WHERE fk_modulo_id=1 ORDER BY  fk_acao_id;
 * -- SELECT usuarios_modulos_acoes.id,usuarios_modulos_acoes.descricao,usuarios_modulos_elementos.descricao  FROM usuarios_modulos_acoes  INNER JOIN usuarios_modulos_elementos ON usuarios_modulos_elementos.id=usuarios_modulos_acoes.fk_elemento_id WHERE usuarios_modulos_acoes.id <=9;
 * -- SELECT * FROM usuarios_modulos_x_acoes WHERE fk_modulo_id=1;
 * -- SELECT * FROM usuarios_perfil;
 * -- SELECT * FROM  usuarios_perfil_x_modulos_acoes WHERE fk_perfil_id=2 AND fk_modulo_acoes_id IN (SELECT id FROM usuarios_modulos_x_acoes WHERE fk_modulo_id=1);
 * Script com a sequencia de insert padrão das 9 ações básicas no backend caso necessário mais ações incluir pela plataforma
 */


function inserirAcoesBasicas($conn, $idModulo)
{
    //Inserindo as 9 ações básicas

//1 - Novo      Botão
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 1;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
//$data['middleware'] = '';
//$data['tipo_rota'] = '';
    $conn->insert('usuarios_modulos_x_acoes', $data);

//2 - Editar    Botão
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 2;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
//$data['middleware'] = '';
//$data['tipo_rota'] = '';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//3 - Deletar   Botão
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 3;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
//$data['middleware'] = '';
//$data['tipo_rota'] = '';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//4 - Index     Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 4;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
    $data['middleware'] = '1';
    $data['tipo_rota'] = 'GET';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//5 - Incluir   Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 5;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
    $data['middleware'] = '1';
    $data['tipo_rota'] = 'GET';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//6 - Editar    Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 6;
    $data['parametro'] = '1';
    $data['sufixo_acao'] = '1';
//$data['middleware'] = '';
    $data['tipo_rota'] = 'GET';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//7 - Salva     Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 7;
//$data['parametro'] = '';
//$data['sufixo_acao'] = '';
//$data['middleware'] = '';
    $data['tipo_rota'] = 'POST';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//8 - Atualizar Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 8;
    $data['parametro'] = '1';
    $data['sufixo_acao'] = '1';
    $data['middleware'] = '1';
    $data['tipo_rota'] = 'PATCH';
    $conn->insert('usuarios_modulos_x_acoes', $data);
//9 - Deletar   Rota
    $data = array();
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['fk_modulo_id'] = $idModulo;
    $data['fk_acao_id'] = 9;
    $data['parametro'] = '1';
//$data['sufixo_acao'] = '';
    $data['middleware'] = '1';
    $data['tipo_rota'] = 'DELETE';
    $conn->insert('usuarios_modulos_x_acoes', $data);
}

function inserirPermissoesPerfi($conn, $idModulo)
{
// Utilizar o perfil 2 de administrador
    $moduloAcoes = $conn->select("SELECT * FROM usuarios_modulos_x_acoes WHERE fk_modulo_id=" . $idModulo);

    foreach ($moduloAcoes as $key => $value) {
        $data = array();
        $data['fk_criador_id'] = 1;
        $data['fk_atualizador_id'] = 1;
        $data['criacao'] = '2019-01-01 00:00:00';
        $data['atualizacao'] = '2019-01-01 00:00:00';
        $data['status'] = 1;
        $data['fk_modulo_acoes_id'] = $value['id'];
        $data['fk_perfil_id'] = 2;
        $conn->insert('usuarios_perfil_x_modulos_acoes', $data);
    }
}

//Função de definição do módulo
function inserirModulo($conn)
{
//Menus ID - Descrição - Criando um novo colocar na LISTA
// -1  SEM MENU
//  1  Cursos
//  2  Eventos
//  3  Pedidos
//  4  Produtoras
//  5  Proposta
//  6  Sistema
//  7  Acessos
//  8  Dashboard
//  9  Membership
// 10  ACL
// 11  Relatórios
// 12  Tutoria
// 13  Configurações
//
//Variaveis do módulo (modelo)

//Exemplo!
//    $descricaoModulo = 'Banners';
//    $routerName = 'configuracaobanners';
//    $routerUri = 'configuracao_banners';
//    $caminhoView = 'configuracao.banners';
//    $controller = 'ConfiguracoesController';
//    $menu = 13;
//Fim exemplo!

    $descricaoModulo = 'Estilos';
    $routerName = 'configuracoesestilos';
    $routerUri = 'configuracoes_estilos';
    $caminhoView = 'configuracoes.estilos';
    $controller = 'ConfiguracoesController';
    $menu = 13;

    if ($menu == 0) {
        print('Informe o Menu' . PHP_EOL);
        exit();
    }

    if (trim($controller) == '') {
        print('Informe o Controller' . PHP_EOL);
        exit();
    }

    if (trim($descricaoModulo) == '') {
        print('Informe o nome do módulo' . PHP_EOL);
        exit();
    }

    if (trim($routerName) == '') {
        print('Informe o nome da rota' . PHP_EOL);
        exit();
    }

    if (trim($caminhoView) == '') {
        print('Informe o caminho da VIEW' . PHP_EOL);
        exit();
    }

    if (trim($routerUri) == '') {
        print('Informe a URI da rota' . PHP_EOL);
        exit();
    }


//não ALTERAR!
//Tudo aqui abaixo roda automáticamente
    $data['fk_criador_id'] = 1;
    $data['fk_atualizador_id'] = 1;
    $data['criacao'] = '2019-01-01 00:00:00';
    $data['atualizacao'] = '2019-01-01 00:00:00';
    $data['status'] = 1;
    $data['descricao'] = $descricaoModulo;
    $data['route_name'] = $routerName;
    $data['route_uri'] = $routerUri;
    $data['view_caminho'] = $caminhoView;
    $data['controller'] = $controller;
    $data['fk_menu_id'] = $menu;
//FIM Variaveis do módulo

//Inserindo módulo
    return $conn->insert('usuarios_modulos', $data);
}


$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

$appName = getenv('APP_NAME');

$DB_CONNECTION = getenv('DB_CONNECTION');
$DB_HOST = getenv('DB_HOST');
$DB_PORT = getenv('DB_PORT');
$DB_DATABASE = getenv('DB_DATABASE');
$DB_USERNAME = getenv('DB_USERNAME');
$DB_PASSWORD = getenv('DB_PASSWORD');
//
$conn = new Database($DB_CONNECTION, $DB_HOST, $DB_PORT, $DB_DATABASE, $DB_USERNAME, $DB_PASSWORD);

$idModulo = inserirModulo($conn);

//Se o módulo inserido com sucesso realiza as demais ações
if ($idModulo > 0) {
    inserirAcoesBasicas($conn, $idModulo);
    inserirPermissoesPerfi($conn, $idModulo);
}
