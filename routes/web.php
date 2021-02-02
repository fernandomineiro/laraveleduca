<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\RoutesWeb;
use App\Http\Controllers\Admin\RelatorioFinanceiroController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::post('/login', 'Auth\LoginController@login')->name('home.login.submit');
Route::get('/logout', 'Auth\LoginController@userLogout')->name('home.logout');
Route::get('/home', 'HomeController@home')->name('home');
Route::get('/atualizar-endereco/{usuario}', 'HomeController@atualizarEndereco');
Route::get('/testEmail', 'HomeController@testEmail')->name('testEmail');
Route::get('/matricular-aluno', 'HomeController@matricularAluno');
Route::get('/matricular-aluno-kroton', 'HomeController@matricularAlunoKroton');
Route::get('/verificacao-disponibilidade/{id?}', 'HomeController@rodaVerificacaoDisponibilidade');
Route::get('/', 'HomeController@index')->name('home.index');
Route::get('/gerar-css/{id}', 'Admin\DynamicScssPhpController@index');
Route::post('/enviar_senha', 'Auth\LoginController@enviar_senha')->name('home.enviar_senha.submit');
//Route::get('files/{query}', 'API\FilesController@index')->where('query', '.+');

Route::get('/teste-certificado', 'HomeController@emailCertificado');


Route::prefix('admin')->group(function () {

    Route::get('cache/clear-all', 'Admin\CacheController@index');
    Route::get('/aluno/getMultiFilterSelectDataAluno', 'Admin\AlunoController@getMultiFilterSelectDataAluno')->name('getMultiFilterSelectDataAluno');
    Route::get('/professor/getMultiFilterSelectDataProfessor', 'Admin\ProfessorController@getMultiFilterSelectDataProfessor')->name('getMultiFilterSelectDataProfessor');
    Route::post('/pedido/getMultiFilterSelectDataPedido', 'Admin\PedidoController@getMultiFilterSelectDataPedido')->name('getMultiFilterSelectDataPedido');
    Route::post('/cupom/getMultiFilterSelectDataCupom', 'Admin\CupomController@getMultiFilterSelectDataCupom')->name('getMultiFilterSelectDataCupom');
    Route::post('/cupom/getAlunosForm', 'Admin\CupomController@getAlunosForm')->name('getAlunosForm');

    Route::post('relatorios/historico-escolar/get_alunos', 'Admin\RelatorioHistoricoEscolarController@getAlunos');
    Route::post('relatorios/historico-escolar/get_relatorio', 'Admin\RelatorioHistoricoEscolarController@getRelatorio');
    Route::get('relatorios/historico-escolar/get_relatorio/pdf/{hash_aluno_id}', 'Admin\RelatorioHistoricoEscolarController@gerarPDF');
    
    //View que contem todas as rotas independentes do usuário ter ou não acesso
    //Os acessos são controlados pelo ACL
    //$data = \Illuminate\Support\Facades\DB::select('SELECT * FROM vm_routes_laravel');
    $data = RoutesWeb::all();

    //Rotas fixas nao alterar

    Route::get('/', 'Admin\IndexController@index')->name('admin');
    Route::get('/', 'Admin\DashboardController@index')->name('admin');

    Route::post('/cidade', 'Admin\EnderecoController@carregaCidade');
    Route::get('/certificadosFaculdade/{id}', 'Admin\CursoController@listarCertificados');
    Route::get('/estado/{uf}', 'Admin\EnderecoController@carregaEstado')->name('estado');
    Route::get('/carrega_cidades/{id}', 'Admin\EnderecoController@carregaCidades')->name('cidades');

    Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
    Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');
    Route::get('/logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
    Route::get('/dashboard', 'Auth\AdminLoginController@dashboard')->name('admin.dashboard');
    Route::get('/scss-test', 'Admin\DynamicScssPhpController@index');
    Route::post('/tags-aluno', 'Admin\AlunoController@saveTagsAluno');
    Route::post('/tags-aluno/deletar', 'Admin\AlunoController@deletarTagAluno');
    Route::get('/tags-aluno/listar', 'Admin\AlunoController@tagsUsuario');
    Route::post('aluno/importar', 'Admin\AlunoController@importar');
    Route::get('/configuracoes_estilos/recriar-estilos', 'Admin\ConfiguracoesController@recriarEstilos');

    //Não remover!
    //Modulos rotas fixas para poder ser criado as demais rotas do sistema
    Route::get('/usuarios_modulos', 'Admin\UsuariosmodulosController@index')->name('admin.usuariosmodulos')->middleware('admin');
    Route::get('/usuarios_modulos/formulario', 'Admin\UsuariosmodulosController@index')->middleware('admin');
    Route::get('/usuarios_modulos/incluir', 'Admin\UsuariosmodulosController@incluir')->name('admin.usuariosmodulos.incluir')->middleware('admin');
    Route::post('/usuarios_modulos/salvar', 'Admin\UsuariosmodulosController@salvar')->middleware('admin');
    Route::get('/usuarios_modulos/{id}/editar', 'Admin\UsuariosmodulosController@editar')->middleware('admin');
    Route::patch('/usuarios_modulos/{id}/atualizar', 'Admin\UsuariosmodulosController@atualizar')->name('admin.usuariosmodulos.atualizar')->middleware('admin');
    Route::delete('/usuarios_modulos/{id}', 'Admin\UsuariosmodulosController@deletar')->name('admin.usuariosmodulos.deletar')->middleware('admin');
    Route::get('/usuario/{id}/recuperarcredenciais', 'Admin\UsuarioController@recuperarSenhaUsuario');
    Route::post('/aluno/{id}/criar-pedido-aluno', 'Admin\AlunoController@criarPedidoAluno')->name('criarPedidoAluno');
    Route::post('/aluno/{id}/adicionar-estrutura', 'Admin\AlunoController@adicionarEstrutura')->name('adicionarEstrutura');
    Route::delete('/aluno/{id}/deletar-estrutura', 'Admin\AlunoController@deletarEstrutura')->name('deletarEstrutura');

    //Chamadas ajax
    Route::post('/usuarios_modulos/addmxa', 'Admin\UsuariosmodulosController@addmxa')->middleware('admin');
    Route::post('/usuarios_modulos/getmxaall', 'Admin\UsuariosmodulosController@getmxaall')->middleware('admin');
    Route::post('/usuarios_modulos/removemxa', 'Admin\UsuariosmodulosController@removemxa')->middleware('admin');
    //

    Route::resource('graduation', 'Admin\GraduationController');

    Route::post('/register-transfer-manual', 'Admin\RepasseController@registerTransferManual');
    Route::post('/pedido/{id}/reenviar-boleto', 'Admin\PedidoController@reenviarBoleto'); # REENVIAR BOLETO POR E-MAIL
    Route::post('/pedido/{id}/enviar-comprovante-pagamento', 'Admin\PedidoController@enviarComprovantePagamento'); # REENVIAR BOLETO POR E-MAIL

    Route::get('/emails/{idFaculdade}/{idTipo}/variaveis', 'Admin\EmailController@variaveis')->name('admin.emails.atualizar')->middleware('admin');
    Route::post('/emails/{idEmail}/clonar', 'Admin\EmailController@clonar')->name('admin.emails.clonar')->middleware('admin');

    // Parte onde está concentrado toda a parte de planilhas

   Route::get('exports_alunos', 'Admin\AlunoController@exports_alunos')->name('admin.aluno.exports_alunos');
   Route::get('export_finances' , 'Admin\GraficosFinanceirosController@export_finances')->name('admin.exports_finances');
   Route::get('export_faculdades', 'Admin\FaculdadeController@export_faculdades')->name('admin.exports_faculdades');
   Route::get('export_trilhas' , 'Admin\TrilhaController@export_trilhas')->name('admin.exports_trilhas');
   Route::get('export_professores','Admin\ProfessorController@export_professores')->name('admin.exports.professores');

   Route::get('/getMultiFilterSelectDataAluno', 'Admin\AlunoController@getMultiFilterSelectDataAluno')->name('getMultiFilterSelectDataAluno');

    //Gerar rotas pelo banco
    foreach ($data as $dtRoutes) {
        if ($dtRoutes->route_name == 'dashboard') continue;

        switch ($dtRoutes->tipo_rota) {
            case 'DELETE':
                Route::delete('/' . $dtRoutes->route_uri . '/{id}', 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->name('admin.' . $dtRoutes->route_name . '.' . strtolower($dtRoutes->acao))->middleware('admin');
                break;
            case 'POST':
                Route::post('/' . $dtRoutes->route_uri . '/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->middleware('admin');
                break;
            case 'PATCH':
                Route::patch('/' . $dtRoutes->route_uri . '/{id}/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->name('admin.' . $dtRoutes->route_name . '.' . strtolower($dtRoutes->acao))->middleware('admin');
                break;
            case 'GET':
                if ($dtRoutes->parametro == 1) { //Rota com parmetro
                    if ($dtRoutes->middleware == 1) //Rota que utiliza name
                        Route::get('/' . $dtRoutes->route_uri . '/{id}/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->name('admin.' . $dtRoutes->route_name . '.' . strtolower($dtRoutes->acao))->middleware('admin');
                    else //Rota que não utiliza name
                        Route::get('/' . $dtRoutes->route_uri . '/{id}/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->middleware('admin');
                } else {//Rotas sem parametros
                    if ($dtRoutes->middleware == 1) //Rotas com name
                        if (strtolower($dtRoutes->acao) != 'index')
                            Route::get('/' . $dtRoutes->route_uri . '/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->name('admin.' . $dtRoutes->route_name . '.' . strtolower($dtRoutes->acao))->middleware('admin');
                        else // Rotas com name padrão index
                            Route::get('/' . $dtRoutes->route_uri . '/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->name('admin.' . $dtRoutes->route_name)->middleware('admin');
                    else//Rotas sem name
                        if (strtolower($dtRoutes->acao) != 'index')
                            Route::get('/' . $dtRoutes->route_uri . '/' . strtolower($dtRoutes->acao), 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->middleware('admin');
                        else//Rotas sem name padrão index
                            Route::get('/' . $dtRoutes->route_uri . '/', 'Admin\\' . $dtRoutes->controller . '@' . strtolower($dtRoutes->acao))->middleware('admin');
                }
                break;
        }
    }
    //die();
    //FIM Geração de rotas
});
//Rotas fixas não alterar
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');

Route::post('password/reset', 'Auth\ForgotPasswordController@reset')->name('password.request');
Route::post('password/reset/{token}', 'Auth\ForgotPasswordController@showResetForm');

Route::get('register', 'Auth\RegisterController@showRegistrationForm');
Route::post('password/reset/{token}', 'Auth\RegisterController@register');
//

// Minhas alterações e criações de Gráficos

Route::get('userchat' , 'Charts\UserChartController@index');

// Route::get('/qrcode', 'QrController@make');
//Rota para autenticar certificado pelo qrcode
Route::get('/autentica-certificado/{idCertificado}', 'Admin\CertificadosController@autentica');

// ROTA PARA VALIDACAO DE VOUCHERS
Route::match(array('GET', 'POST'), '/autentica-voucher/{code?}', 'Admin\VoucherController@index');
