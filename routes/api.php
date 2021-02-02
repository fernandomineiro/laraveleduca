<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});

Route::post('login', 'ApiController@login');
Route::post('login-kroton', 'ApiController@loginKroton');
Route::post('loginAcessoRestrito', 'ApiAcessoRestritoController@loginAcessoRestrito');
Route::get('info/{token}', 'ApiController@info');
Route::post('register', 'ApiController@register');

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::post('logout', 'ApiController@logout');
    Route::post('pedido/deletar/{id}', 'API\PedidoController@deletar');

    /** retorna o chat do aluno no curso */
    Route::get('curso-duvidas/{idCurso}', 'API\TutoriaAlunoController@duvidas');
    Route::get('curso-duvidas-externo/{id}/{idCurso}', 'API\TutoriaAlunoController@duvidasAlunoKroton');
    Route::post('resposta', 'API\TutoriaAlunoController@resposta');

    Route::get('tutoria/chat/{idPergunta}', 'API\TutoriaController@chat');
    Route::post('tutoria/mensagens', 'API\TutoriaController@mensagens');

    Route::get('lista-presenca', 'API\CusroTurmaPresencasController@lista');
    Route::get('lista-presenca-students/{courseId}/{turmaId}/{agendaId}', 'API\CusroTurmaPresencasController@students');

    Route::get('tutoria/trabalhos', 'API\TutoriaController@trabalhos');
    Route::post('tutoria/trabalhos/save', 'API\TutoriaController@trabalhosSave');

//    Route::post('disparador-email', 'API\DisparadorEmailController@dispararEmail');
//    Route::get('disparador-email/lista', 'API\DisparadorEmailController@getLista');

    Route::get('agenda-cursos/{month}', 'API\CursoController@agenda_cursos');
    Route::post('tutoria/upload-tcc/{cursoId}/{id?}', 'API\TutoriaAlunoController@uploadTcc');
    Route::get('tutoria/usuario-trabalhos/{id}', 'API\TutoriaAlunoController@retornaUsuarioTrabalhos');

    Route::post('professor/salvar-dados-bancarios', 'API\ProfessorController@salvarDadosBancarios');

    // Relatórios e Gráficos do Acesso Restrito
	Route::get('relatorios/relatorio-financeiro/exportar', 'API\RelatorioFinanceiroController@export');
	Route::get('relatorios/relatorio-financeiro/carregar-filtros', 'API\RelatorioFinanceiroController@loadFilters');
	Route::get('relatorios/relatorio-financeiro', 'API\RelatorioFinanceiroController@index');

    Route::group(['middleware' => 'transform_query_string'], function () {
        Route::get('relatorios/relatorio-financeiro-detalhado/exportar', 'API\RelatorioFinanceiroDetalhadoController@export');
        Route::get('relatorios/relatorio-financeiro-detalhado/carregar-filtros', 'API\RelatorioFinanceiroDetalhadoController@loadFilters');
        Route::get('relatorios/relatorio-financeiro-detalhado', 'API\RelatorioFinanceiroDetalhadoController@index');

        Route::get('relatorios/relatorio-alunos-matriculados/exportar', 'API\RelatorioAlunosMatriculadosController@export');
        Route::get('relatorios/relatorio-alunos-matriculados/carregar-filtros', 'API\RelatorioAlunosMatriculadosController@loadFilters');
        Route::get('relatorios/relatorio-alunos-matriculados', 'API\RelatorioAlunosMatriculadosController@index');
        Route::get('relatorios/relatorio-alunos-matriculados-graficos', 'API\RelatorioAlunosMatriculadosController@graficoComparativo');
        Route::get('relatorios/relatorio-alunos-matriculados-graficos-realizadas', 'API\GraficoAlunosMatriculadosController@index');
    });

    Route::get('relatorios/relatorio-financeiro/exportar', 'API\RelatorioFinanceiroController@export');
    Route::get('relatorios/relatorio-financeiro/carregar-filtros', 'API\RelatorioFinanceiroController@loadFilters');
    Route::get('relatorios/relatorio-financeiro', 'API\RelatorioFinanceiroController@index');

    Route::get('relatorios-graficos/{mes}/{ano}/{ies?}/{export?}', 'API\RelatoriosGraficosController@index');

    Route::get('relatorios/assinaturas-status', 'API\RelatoriosGraficosController@graficoAssinaturasStatus');

    Route::get('relatorios/grafico-comparativo-faturamento', 'API\RelatoriosGraficosController@graficoComparativoFaturamento');
    Route::get('relatorios/grafico-faturamento-por-professor', 'API\RelatoriosGraficosController@graficoFaturamentoPorProfessor');
    Route::get('relatorios/grafico-faturamento-por-categoria', 'API\RelatoriosGraficosController@graficoFaturamentoPorCategoria');
    Route::get('relatorios/assinantes-ativos', 'API\RelatoriosGraficosController@totalAssinantesAtivos');
    Route::get('relatorios/assinaturas-realizadas', 'API\RelatoriosGraficosController@graficoAssinaturasRealizadas');
    Route::get('relatorios/pedidos-pagamento-reprovados', 'API\RelatoriosGraficosController@graficoPedidoPagamentoReprovado');
    Route::get('relatorios/assinaturas-canceladas', 'API\RelatoriosGraficosController@graficoAssinaturasCanceladas');
    Route::get('relatorios/assinaturas-abandonadas', 'API\RelatoriosGraficosController@graficoAssinaturasAbandonadas');

    Route::get('assinaturas/minha-assinatura', 'API\AssinaturasController@minhaAssinatura');

    Route::get('professor/{id}/usuario', 'API\ProfessorController@showProfessorByIdUsuario');

    Route::post('professor/salvarEndereco', 'API\ProfessorController@salvarEndereco');
    Route::post('professor/salvar', 'API\ProfessorController@salvarProfessor');
    Route::post('gestor/salvar', 'API\GestorController@salvarGestor');

    Route::post('relatorios/historico-escolar/get_alunos', 'API\RelatorioHistoricoEscolarController@getAlunos');
    Route::post('relatorios/historico-escolar/get_relatorio', 'API\RelatorioHistoricoEscolarController@getRelatorio');
    Route::post('/lista_faculdades', 'API\RelatorioHistoricoEscolarController@getInstituicao');

    Route::get('relatorios/historico-escolar/{nome_cpf?}', 'API\RelatorioHistoricoEscolarController@index');

    Route::get('relatorios/alunos', 'API\RelatorioAlunoController@index');
    Route::get('relatorios/grafico-faturamento-por-professor', 'API\RelatoriosGraficosController@graficoFaturamentoPorProfessor');

    Route::post('/lista_faculdades', 'API\RelatorioHistoricoEscolarController@getInstituicao');
    Route::post('relatorios/alunos_matriculados', 'API\RelatorioAlunosMatriculadosController@getAlunosMatriculados');
    Route::get('pedido_status_all', 'API\PedidoStatusController@index');
    Route::get('curso_tipo', 'API\CursoTipoController@index');


    // Usando o Resource para criar a API de Visão GERAL



});



route::resource('visao-geral' , 'API\OverviewController');

Route::get('tutoria/usuario-trabalhos-cursos/{id}/{idCurso}', 'API\TutoriaAlunoController@retornaUsuarioTrabalhosEnviados');

Route::get('cursos/{id}/get-cursos-itv', 'API\CursoController@getCursosItv');

Route::get('cursos/{id}/{idUsuario}/verificar-pagamento', 'API\CursoController@checkIfCourseHasWaitingPayments');
Route::get('tutoria/{id}/nao-lidas', 'API\TutoriaController@naoLidas');

Route::get('max-value-course', 'API\CursoController@getMaxValueCourse');
Route::get('pedido', 'API\PedidoController@index');
Route::get('pedido/{id}/aluno', 'API\PedidoController@usuarioPedidos');

Route::post('pedido/create', 'API\PedidoController@create');
Route::post('cupom/validar', 'API\CupomController@validar');

Route::get('curso/retorna-todos-cursos/{idTipo?}', 'API\CursoController@retornarTodosCursos');
Route::get('curso/{id?}/{idTipo?}/{idCategoria?}/{idFaculdade?}', 'API\CursoController@index');
Route::post('curso/search/{idTipo?}', 'API\CursoController@search');
Route::post('curso/create', 'API\CursoController@create');
Route::post('curso/edit', 'API\CursoController@atualizar');
Route::get('curso-ver/{idCurso}', 'API\CursoController@show');
Route::post('curso-files', 'API\CursoController@uploadFile');
Route::get('cursos/listar-cursos-home', 'API\CursoController@listarCursosHome');
Route::get('cursos/listar-cursos-paginados/{idTipo}/{perPage?}', 'API\CursoController@listarCursosHomePaginado');
Route::get('cursos/slug/{slug_curso}/{tipo_curso_id}', 'API\CursoController@getCursoIDBySlug');
Route::get('curso/{id?}/{idTipo?}/{idCategoria?}/{idFaculdade?}', 'API\CursoController@index');

Route::get('categorias', 'API\CursoController@categorias');
Route::get('categorias_por_curso', 'API\CursoController@categorias_por_curso');
Route::get('cursos_por_categoria', 'API\CursoController@cursos_por_categoria');
Route::get('cursos-presenciais-do-aluno/{id}', 'API\CursoController@cursosPresenciaisPorAluno');
Route::get('cursos-remotos-do-aluno/{id}', 'API\CursoController@cursosRemotosPorAluno');
Route::get('cursos-hibridos-do-aluno/{id}', 'API\CursoController@cursosRemotosPorAluno');
Route::get('cursos-online-do-aluno/{id}', 'API\CursoController@cursosOnlinePorAluno');
Route::get('cursos-online-iniciados-do-aluno/{id}', 'API\CursoController@cursosOnlineIniciadosPorAluno');


Route::get('cursos-trilha-presenciais-do-aluno/{id}', 'API\CursoController@cursosTrilhaPresenciaisPorAluno');
Route::get('cursos-trilha-remotos-do-aluno/{id}', 'API\CursoController@cursosTrilhaRemotosPorAluno');
Route::get('cursos-trilha-hibridos-do-aluno/{id}', 'API\CursoController@cursosTrilhaRemotosPorAluno');
Route::get('cursos-trilha-online-do-aluno/{id}', 'API\CursoController@cursosTrilhaOnlinePorAluno');
Route::post('avisar-novas-turmas/create', 'API\CursoController@aviseNovasTurmas');

Route::get('aluno-estatisticas/{id}', 'API\CursoController@minhasEstatisticas');
Route::get('curso-side-bar/{idCurso}/{id?}', 'API\CursoController@sidebarCurso');
Route::get('modulos-assistidos/{idCurso}/{idUsuario}', 'API\CursoController@modulosAssistidosPorCursoAluno');
Route::post('set-modulo-assistido', 'API\CursoController@setModuloAssistido');
Route::get('cursos-por-professor/{idProfessor}/{idTipo}/rascunho', 'API\CursoController@rascunhoPorProfessor');
Route::get('cursos-por-professor/{idProfessor}/{idTipo}/enviado', 'API\CursoController@enviadoPorProfessor');
Route::get('cursos-por-professor/{idProfessor}/{idTipo}/{idFaculdade}/meus-cursos', 'API\CursoController@cursoPorProfessor'); // apenas essa é usada para buscar os cursos do professor

Route::get('status-aprovacao/{idProfessor}/{idTipo}/lista', 'API\CursoController@statusAprovacao');

Route::get('tags_por_curso/{id}', 'API\CursoController@tags_por_curso');
Route::get('comentarios_por_curso/{id}', 'API\CursoController@comentarios_por_curso');
Route::get('agendas_por_curso/{id}', 'API\CursoController@agendas_por_curso');
Route::get('get-hour-from-server', 'API\CursoController@getHoraServidor');

Route::get('modulos_por_curso/{id}', 'API\CursoController@modulos_por_curso');
Route::get('modulo/{id}', 'API\CursoController@modulo');
Route::get('modalidades', 'API\CursoController@modalidade');

Route::get('faculdade', 'API\FaculdadeController@index');
Route::get('faculdade/{id}', 'API\FaculdadeController@index');
Route::get('faculdade/{id}/cursos', 'API\FaculdadeController@cursos');
Route::get('certificado/{idFaculdade}/lista', 'API\CertificadoController@index');
Route::post('certificados-faculdades/lista', 'API\CertificadoController@retornaCertificadosFaculdades');
Route::get('certificado/{id}/ver', 'API\CertificadoController@show');
Route::get('certificado/disponiveis-download/{idUsuario}', 'API\CertificadoController@retornaCertificadosUsuario');
Route::post('certificado/envia-certificado-email', 'API\CertificadoController@enviaCertificadoPorEmail');
Route::get('certificado/getCertificadosAluno/{idUsuario}', 'API\CertificadoController@getCertificadosEmitidosAluno');
Route::get('certificado/emiteCertificado/{idUsuario}/{idCurso}', 'API\CertificadoController@emiteCertificado');
Route::get('certificado/autenticaCertificado/{codigo}', 'API\CertificadoController@autentica');

Route::get('certificado/getProgressoConclusao/{idUsuario}/{idCurso}', 'API\CertificadoController@getProgressoConclusao');

Route::get('curadores', 'API\CuradorController@index');
Route::get('curador/{id}', 'API\CuradorController@show');
Route::get('produtoras', 'API\ProdutoraController@index');
Route::get('produtora/{id}', 'API\ProdutoraController@show');

Route::get('trilha', 'API\TrilhaController@index');
Route::post('trilhas/lista', 'API\TrilhaController@retornaTrilhasDash');
Route::get('trilha/{id}', 'API\TrilhaController@index');
Route::get('trilha_cursos/{id}', 'API\TrilhaController@cursos');
Route::get('assinatura-cursos/{id}', 'API\AssinaturasController@cursos');
Route::get('estrutura-curricular-cursos/{id}', 'API\EstruturaCurricularController@cursos');
Route::get('estrutura-curricular-cursos/cursosCategoria/{id}', 'API\EstruturaCurricularController@cursosCategoria');
Route::get('estrutura-curricular-cursos/cursosPorCategoria/{id}', 'API\EstruturaCurricularController@cursosPorCategoria');
Route::get('trilha/change-status/{idTrilha}/{status}', 'API\TrilhaController@trocaStatus');
Route::get('trilha_detalhes/{id}', 'API\TrilhaController@detalhes');
Route::post('trilha/search', 'API\TrilhaController@cardSearch');
Route::get('trilha/{id}/deletar', 'API\TrilhaController@deletar');
Route::get('trilha/slug/{slug_trilha}', 'API\TrilhaController@getTrilhaIDBySlug');

Route::post('trilha-favoritar', 'API\TrilhaController@favoritar');
Route::get('trilha-favoritos/{id}', 'API\TrilhaController@favoritas');
Route::post('trilha-desfavoritar', 'API\TrilhaController@desfavoritar');

Route::get('trilhas_categoria/{id}', 'API\TrilhaController@categoria');
Route::get('trilhas_card/{id}', 'API\TrilhaController@montaCardTrilhaPorCategoria');
Route::get('trilhas_card', 'API\TrilhaController@montaCardTrilha');

Route::get('categorias/{id}', 'API\CursoController@categorias');
Route::get('categorias/trilhas/{id?}', 'API\TrilhaController@categorias');
Route::get('faculdades', 'API\CursoController@faculdades');

Route::post('progressoCurso/{idCurso}/adicionaModulosPorCurso', 'API\CursoModuloConclusaoController@adicionarModulosPorCurso');
Route::post('progressoCurso/{idCurso}/mudarStatusModulo', 'API\CursoModuloConclusaoController@mudarStatusModulo');
Route::post('progressoCurso/{idCurso}/verificarModulo', 'API\CursoModuloConclusaoController@verificarModulo');
Route::post('progressoCurso/{idCurso}/verificarPercentualConclusao', 'API\CursoModuloConclusaoController@verificarPercentualConclusao');
Route::post('progressoCurso/{idCurso}/resumo', 'API\CursoModuloConclusaoController@resumo');

Route::get('professores/{idFaculdade?}/{idCategoria?}', 'API\ProfessorController@index');
Route::get('professores-criar-curso', 'API\ProfessorController@getProfessoresCriarCurso');
Route::get('professores/promocoes', 'API\ProfessorController@promocoes');
Route::get('professores/{searchTerm}/search', 'API\ProfessorController@buscarProfessor');
Route::get('professores/recentes', 'API\ProfessorController@recentes');
Route::get('professores-busca/{searchTerm}/search', 'API\ProfessorController@buscarProfessor');
Route::get('professor/{id}', 'API\ProfessorController@show');

Route::get('professor/{id}/cursos', 'API\ProfessorController@cursos');
Route::get('professor/{professor_id}/{faculdade_id}/cursos', 'API\ProfessorController@cursosByProfessorIDAndFaculdadeID');
Route::post('professor/salvar-minicurriculo', 'API\ProfessorController@salvarMiniCurriculo');


Route::get('professor-tipos-formacao', 'API\ProfessorController@getTiposFormacao');
Route::post('professor', 'API\ProfessorController@create');

Route::post('aluno/create', 'API\AlunoController@create');
Route::post('aluno/redefinir-senha', 'API\AlunoController@redefinirSenha');
Route::get('aluno/', 'API\AlunoController@index');
Route::get('aluno/verifica-cadastro-completo/{idUsuario}', 'API\AlunoController@checkCadastroCompleto');


Route::post('aluno/{id}/upload_foto', 'API\AlunoController@upload_foto');
Route::post('aluno/{id}/update', 'API\AlunoController@update');

Route::get('aluno/{id}', 'API\AlunoController@show');

Route::get('categorias', 'API\CursoCategoriaController@index');
Route::get('categoria/{id}', 'API\CursoCategoriaController@show');
Route::get('categoria/slug/{slug_categoria}', 'API\CursoCategoriaController@getCategoryIDBySlug');
Route::get('categoria/{id}/professores', 'API\CursoCategoriaController@professores');

Route::get('evento/{id?}', 'API\EventoController@index');
Route::get('eventos/{idEvento?}', 'API\EventoController@retornaPorId');
Route::post('evento/search', 'API\EventoController@search');
Route::post('evento/create', 'API\EventoController@create');
Route::get('evento/{idFaculdade?}/meus-eventos', 'API\EventoController@eventosPorProfessor');

Route::post('tutoria/pergunta', 'API\TutoriaController@pergunta');
Route::post('tutoria/resposta', 'API\TutoriaController@resposta');
Route::get('perguntas', 'API\TutoriaController@perguntas');
Route::get('pergunta/{id}', 'API\TutoriaController@show');
Route::get('pergunta/{id}/respostas', 'API\TutoriaController@respostas');
Route::post('pergunta', 'API\TutoriaController@pergunta');

Route::get('curso-sugestoes', 'API\CursoSugestaoController@index');
Route::get('curso-sugestao/{id}', 'API\CursoSugestaoController@show');
Route::post('curso-sugestao', 'API\CursoSugestaoController@create');


Route::post('curso-favoritar', 'API\CursoController@favoritar');
Route::post('curso-desfavoritar', 'API\CursoController@desfavoritar');
Route::get('cursos-favoritos/{id}', 'API\CursoController@favoritos');

Route::get('configuracao/{id}', 'API\ConfiguracaoController@configuracoesFaculdade');
Route::get('configuracao/{id}/index', 'API\ConfiguracaoController@index');
Route::get('configuracao/{id}/emails', 'API\ConfiguracaoController@emails');

Route::get('configuracao/{id}/{pagina}/banners', 'API\ConfiguracaoController@banners');
Route::get('configuracao/{id}/bannersSecundarios', 'API\ConfiguracaoController@bannersSecundarios');
Route::get('configuracao/{idFaculdade}/{pagina}/{idCategoria}/banners', 'API\ConfiguracaoController@banners');

Route::get('configuracao/{id}/estilos', 'API\ConfiguracaoController@estilos');
Route::get('configuracao/{id}/pixels', 'API\ConfiguracaoController@pixels');
Route::get('configuracao/{id}/parceiros', 'API\ConfiguracaoController@parceiros');

Route::get('configuracao/{id}/redessociais', 'API\ConfiguracaoController@redesSociais');
Route::get('configuracao/{id}/logotipos', 'API\ConfiguracaoController@logotipos');
Route::get('configuracao/{id}/sac', 'API\ConfiguracaoController@sac');
Route::get('configuracao/{id}/politica', 'API\ConfiguracaoController@politica');
Route::get('configuracao/{id}/paginas', 'API\ConfiguracaoController@paginas');
Route::get('configuracao/{id}/termo', 'API\ConfiguracaoController@termo');
Route::get('configuracao/{id}/homeandfotter', 'API\ConfiguracaoController@homeAndFotter');

Route::get('lista-presenca/{turmaId}', 'API\CusroTurmaPresencasController@agenda');
Route::get('lista-presenca/{agendaId}/presencas', 'API\CusroTurmaPresencasController@presencas');
Route::post('lista-presenca', 'API\CusroTurmaPresencasController@atualizar');

Route::post('curso-agenda', 'API\CusroTurmaAgendaController@agenda');

Route::get('assinaturas', 'API\AssinaturasController@index');
Route::get('assinaturas/salvar', 'API\AssinaturasController@salvar');
Route::get('assinaturas/deletar', 'API\AssinaturasController@deletar');
Route::get('assinaturas/retornar-assinatura-usuario/{idUsuario}', 'API\AssinaturasController@retornaAssinaturaUsuario');
Route::get('assinatura-ver/{idAssinatura}', 'API\AssinaturasController@show');

Route::get('cursos-online-professor/{id}', 'API\CursoProfessorController@online');
Route::get('cursos-remotos-professor/{id}', 'API\CursoProfessorController@remotos');
Route::get('cursos-hibridos-professor/{id}', 'API\CursoProfessorController@remotos');
Route::get('cursos-presenciais-professor/{id}', 'API\CursoProfessorController@presenciais');

Route::get('quiz-por-curso/{idCurso}/{idQuiz}/{idUsuario}', 'API\QuizQuestaosRespostasController@index');
Route::get('quiz-por-curso/situacao/{idCurso}/{idQuiz}/{idUsuario}', 'API\QuizQuestaosRespostasController@getSituacao');
Route::post('quiz-por-curso/enviar', 'API\QuizQuestaosRespostasController@enviar');
Route::post('quiz-por-curso/gabarito', 'API\QuizQuestaosRespostasController@gabarito');

Route::get('estados', 'API\EstadoController@index');
Route::get('cidades/{idEstado}', 'API\CidadeController@index');
Route::post('cidades-por-localidade', 'API\CidadeController@getCidadeByName');
Route::post('aluno/saveEndereco', 'API\AlunoController@saveAddress');
Route::post('aluno/{id}/saveCredentials', 'API\AlunoController@saveCredentials');
Route::get('aluno/buscar-cep/{cep}', 'API\AlunoController@buscarEnderecoCep');
Route::get('banco', 'API\BancoController@index');

# WIRECARD
Route::prefix('wirecard')->group(function () {
    Route::post('pay', 'API\WirecardController@pay'); # GERA BOLETO
    Route::post('callback', 'API\WirecardController@callback'); # RETORNOS WIRECARD
    Route::post('preference-notification', 'API\WirecardController@createPreferenceNotification'); # RETORNOS WIRECARD
    Route::post('create-account', 'API\WirecardController@createAccount'); # METODO PARA CRIACAO DE SUBCONTAS WIRECARD
    Route::post('add-bank-account', 'API\WirecardController@addBankAccount'); # METODO PARA ADICAO DE CONTA BANCARIA A SUBCONTAS WIRECARD
    Route::get('installment-fee', 'API\WirecardController@getInstallmentFee'); # LISTA PERCENTUAL DE JUROS PARA CADA PARCELAMENTO
});

# WIRECARD TRANSFER
Route::prefix('wirecard-transfer')->group(function () {
    Route::get('sub-accounts', 'API\WirecardTransferController@listSubAccounts'); # LISTA TODAS AS SUB CONTAS WIRECARD QUE RECEBERAM REPASSES AUTOMATICOS
    Route::post('execute', 'API\WirecardTransferController@transfer');
});

# WIRECARD ASSINATURAS
Route::prefix('wirecard-signature')->group(function () {
    Route::get('plans', 'API\WirecardSignatureController@plans'); # RETORNOS WIRECARD
    Route::post('create-plan', 'API\WirecardSignatureController@createPlan'); # RETORNOS WIRECARD
    Route::post('create', 'API\WirecardSignatureController@create'); # CRIAR ASSINATURA
    Route::put('cancel-signature/{code}', 'API\WirecardSignatureController@cancelSignature'); # CANCELAR ASSINATURA
    Route::post('callback', 'API\WirecardSignatureController@webhook'); # RETORNO AUTOMATICO
    Route::get('subscriber-info/{idUsuario}', 'API\WirecardSignatureController@getSubscriberInfo'); # RETORNO AUTOMATICO
    Route::get('check-status-subscriber/{idUsuario}', 'API\WirecardSignatureController@checkStatusSubscriber'); # VERIFICA SE USUARIO POSSUI ASSINATURA ATIVA

    # CANCELAR ASSINATURA AGENDADAS
    # 0 * * * * wget --quiet -o /dev/null >/dev/null http://ec2-3-81-68-4.compute-1.amazonaws.com/api/wirecard-signature/scheduled-cancellations
    Route::get('scheduled-cancellations', 'API\WirecardSignatureController@scheduledCancellations');
});

# NFE
Route::prefix('nfe')->group(function () {
    Route::post('invoice', 'API\NfeController@issueInvoice'); # GERA NOTA FISCAL
    Route::post('webhook', 'API\NfeController@webhook'); # RETONOS NFE.IO
    Route::post('resent-invoice', 'API\NfeController@resentInvoice'); # REENVIA NOTA FISCAL POR EMAIL
    Route::get('download-invoice', 'API\NfeController@forceDownloadInvoice'); # FORCAR DOWNLOAD DA NOTA FISCAL
});

/* VOUCHER */
Route::get('voucher/{pid}/{tipo}/{pedidos_item_id}/{fk_curso?}', 'API\VoucherController@issueVoucher'); # GERAR VOUCHER
Route::get('voucher/qrcode', 'API\VoucherController@generateQRCode'); # GERAR QRCODE DO VOUCHER
Route::get('voucher-pdf/{pid}/{tipo}/{pedidos_item_id}/{fk_curso?}', 'API\VoucherController@PDF'); # EXIBIR VOUCHER E GERAR SE NAO EXISTIR
Route::get('print-voucher/{file_name}', 'API\VoucherController@printVoucher'); # PRINT VOUCHER

// Lista de Espera
Route::get('/lista-espera', 'API\CursosVencidosController@index');
Route::post('/avisar-interessados', 'API\CursosVencidosController@avisarNovasTurmas');

Route::get('relatorios/historico-escolar/pdf/{hash_aluno_id}', 'Admin\RelatorioHistoricoEscolarController@gerarPDF');

# CRON
Route::get('relatorios/assinatura-repasse', 'API\AssinaturasController@repasses');



// ROTAS EDUCAZ ESCOLAS *NÃO ALTERAR SEM NECESSIDADE*

# Escolas
Route::prefix('escolas')->group(function () {
    Route::get('', 'EducazEscolas\EscolaController@all');

    Route::post('login', 'ApiController@loginEscolas');
    Route::post('loginAcessoRestrito', 'ApiController@loginEscolasAcessoRestrito');

    Route::get('edit/{idEscola}/show', 'EducazEscolas\EscolaController@show');
    Route::delete('{id}/deletar', 'EducazEscolas\EscolaController@deletar');
    Route::post('create', 'EducazEscolas\EscolaController@create');
    Route::put('edit/{idEscola}/update', 'EducazEscolas\EscolaController@update');
    Route::get('{slug?}/diretoria', 'EducazEscolas\EscolaController@escolasDiretoria');
    Route::get('turmas/{idTurma}/{idUsuario}', 'EducazEscolas\EscolaController@turmas');
    Route::post('upload', 'EducazEscolas\MateriaController@uploadFile');

    Route::get('usuario/{idUsuario}/show', 'EducazEscolas\UsuarioController@show');
    Route::put('usuario/{idUsuario}/update', 'EducazEscolas\UsuarioController@update');
    Route::post('usuario/upload', 'EducazEscolas\UsuarioController@uploadFile');
});
Route::get('escola/{slugEscola?}/professores', 'EducazEscolas\ProfessorController@professoresEscola');
Route::get('escola/{slugEscola?}/{slugTurma?}/{slugDisciplina?}/professores', 'EducazEscolas\ProfessorController@professoresEscola');

# Materias
Route::get('materias/{idDisciplina}/{idTurma}/{idEscola}/{idUsuario}', 'EducazEscolas\MateriaController@index');
Route::get('materias/{idDisciplina}/{idTurma}/{idEscola}/{idUsuario}/professor', 'EducazEscolas\MateriaController@professor');
Route::get('materias/show/{idMateria}/professor', 'EducazEscolas\MateriaController@aulasProfessor');
Route::get('materias/show/{idMateria}/{idUsuario}', 'EducazEscolas\MateriaController@show');
Route::post('materias/criar', 'EducazEscolas\MateriaController@criar');
Route::post('materias/atualizar/{idMateria}', 'EducazEscolas\MateriaController@atualizar');
Route::get('materias-professor/{idUsuario}', 'EducazEscolas\MateriaController@materiasProfessor');
Route::get('materias/slug/{slug_materia}', 'EducazEscolas\MateriaController@getMateriaBySlug');
Route::get('materias/aulas/{idMateria}', 'EducazEscolas\MateriaController@getAulasMateria');
Route::get('materias/calendario/{idUsuario}/{idTurma}', 'EducazEscolas\MateriaController@getCalendario');
Route::get('materias/atividade-quiz/{id}/{idUsuario}', 'EducazEscolas\MateriaController@quizAtividade');
Route::get('materias/atividade-quiz-usuario/{id}/{idUsuario}', 'EducazEscolas\MateriaController@quizAtividadeUsuario');
Route::get('materias/atividade-trabalho/{idMateria}/{idAtividade}', 'EducazEscolas\MateriaController@trabalhoAtividade');
Route::post('materia/atividade/upload-tcc/{idCurso}/{idModulo?}/{idUsuario}', 'EducazEscolas\MateriaController@uploadTcc');
Route::get('materia/boletim/{idUsuario}/{idTurma}/{idMateria}', 'EducazEscolas\MateriaController@boletimMateria');

Route::get('materias/{slugMateria}/{slugTurma}/diario', 'EducazEscolas\MateriaController@aulasProfessorSlug');
Route::post('materias/plano-aula/pdf', 'EducazEscolas\MateriaController@generatePdfPlanoAulas');
Route::post('materias/media-turma/pdf', 'EducazEscolas\MateriaController@generatePdfMediaTurma');

Route::get('disciplinas/{idTurma}/{idEscola}/{idUsuario}', 'EducazEscolas\DisciplinasController@index');
Route::get('disciplinas/{idTurma}/{idEscola}/{idUsuario}/professor', 'EducazEscolas\DisciplinasController@disciplinasProfessor');
Route::get('all/disciplinas', 'EducazEscolas\DisciplinasController@disciplinas');


Route::get('escolas/aluno-duvidas/{idCurso}/{idUsuario}', 'EducazEscolas\MateriaController@duvidas');

# Disciplinas
Route::prefix('disciplina')->group(function () {

    Route::get('escola/{slugRegional}/{slugEscola}', 'EducazEscolas\DisciplinasController@disciplinasEscola');
    Route::get('escola/{slugRegional}/{slugEscola}/{slugTurma}', 'EducazEscolas\DisciplinasController@disciplinasTurma');
    Route::get('{id}', 'EducazEscolas\DisciplinasController@disciplinaById');
    Route::get('slug/{slug_disciplina}', 'EducazEscolas\DisciplinasController@getDisciplinaBySlug');

    Route::post('create', 'EducazEscolas\DisciplinasController@create');
    Route::put('update', 'EducazEscolas\DisciplinasController@update');
    Route::get('boletim/{idUsuario}/{idTurma}/{idDisciplina}', 'EducazEscolas\DisciplinasController@boletimDisciplina');
    Route::get('boletim-global/{idUsuario}/{idTurma}/{idEscola}', 'EducazEscolas\DisciplinasController@boletimGlobal');
    Route::delete('{id}/deletar', 'EducazEscolas\DisciplinasController@deletar');
});

# Professores
Route::get('professores-escola/{idUsuario}/{idEscola}/{idTurma}', 'EducazEscolas\ProfessorController@index');
Route::get('professores-por-materia/{idMateria}/{idEscola}/{idTurma}', 'EducazEscolas\ProfessorController@professorPorMateria');
Route::get('professores-por-disciplina/{idDisciplina}/{idEscola}/{idTurma}', 'EducazEscolas\ProfessorController@professorPorDisciplina');
Route::get('professor/calendario/{idUsuario}', 'EducazEscolas\ProfessorController@getCalendario');
Route::get('escolas/tutoria/chat/{idPergunta}/{idUsario}', 'EducazEscolas\ProfessorController@chat');
Route::post('escolas/tutoria/mensagens', 'EducazEscolas\ProfessorController@mensagens');
Route::get('professores/atividades-corrigir/{turma}/{disciplina}/{materia}/{idUsuario}', 'EducazEscolas\ProfessorController@atividadesCorrecaoDisciplina');
Route::get('professores/atividades-corrigir/trabalhos/{turma}/{disciplina}/{materia}/{idUsuario}', 'EducazEscolas\ProfessorController@trabalhosCorrecao');
Route::get('professores/atividades-corrigir/exercicios/{turma}/{disciplina}/{materia}/{idUsuario}', 'EducazEscolas\ProfessorController@exericiosCorrecao');
Route::get('professores/atividades-corrigir/presencial/{turma}/{disciplina}/{materia}/{idUsuario}', 'EducazEscolas\ProfessorController@presencialCorrecao');

# Diretoria Ensino
Route::get('diretorias-ensino', 'EducazEscolas\DiretoriaEnsinoController@index');
Route::get('diretorias-ensino/{idDiretoria}/show', 'EducazEscolas\DiretoriaEnsinoController@show');
Route::get('diretoria/{slugRegional}/turmas', 'EducazEscolas\DiretoriaEnsinoController@turmasRegional');
Route::get('diretoria/escola/{slugRegional}/{slugEscola}/turmas', 'EducazEscolas\DiretoriaEnsinoController@turmasEscola');
Route::get('diretoria/escola/{slugRegional}/{slugEscola}/{idUsuario}/turmas/professor', 'EducazEscolas\DiretoriaEnsinoController@turmasProfessor');

# Escolas
Route::prefix('turmas')->group(function () {
    Route::get('edit/{idTurma}/show', 'EducazEscolas\TurmaController@show');
    Route::post('create', 'EducazEscolas\TurmaController@create');
    Route::put('edit/{idTurma}/update', 'EducazEscolas\TurmaController@update');
    Route::delete('{id}/deletar', 'EducazEscolas\TurmaController@deletar');
});
# Recados
Route::get('recados/aluno/{idTurma}/{idEscola}/{idUsuario}', 'EducazEscolas\RecadosController@index');
Route::get('recados/professor/{idUsuario}/{slugMateria}/{slugTurma}', 'EducazEscolas\RecadosController@recadosProfessor');
Route::post('recados/novo', 'EducazEscolas\RecadosController@create');
Route::post('recados/atualizar/{id}', 'EducazEscolas\RecadosController@update');
Route::get('recados/excluir/{id}', 'EducazEscolas\RecadosController@destroy');
Route::get('recados/show/{id}', 'EducazEscolas\RecadosController@show');

# Correcao/Notas
Route::post('notas/atividade', 'EducazEscolas\CorrecaoController@notaAtividade');
Route::post('notas/materia', 'EducazEscolas\CorrecaoController@notaMateria');
Route::post('notas/disciplina', 'EducazEscolas\CorrecaoController@notaDisciplina');
Route::post('notas/questao', 'EducazEscolas\CorrecaoController@notaQuestao');
Route::post('notas/resultado-quiz/salvar', 'EducazEscolas\CorrecaoController@salvarResultadoQuiz');


# Alunos
Route::prefix('alunos')->group(function () {
    Route::get('', 'EducazEscolas\AlunosController@index');
    Route::get('{idAluno}/show', 'EducazEscolas\AlunosController@show');
    Route::get('{slugEscola?}/{slugTurma?}', 'EducazEscolas\AlunosController@index');
    Route::post('create', 'EducazEscolas\AlunosController@create');
    Route::put('update', 'EducazEscolas\AlunosController@update');
    Route::post('importar', 'EducazEscolas\AlunosController@importar');
    Route::delete('{id}/deletar', 'EducazEscolas\AlunosController@deletar');
});



# Alunos
Route::prefix('escolas/professores')->group(function () {
    Route::get('{idProfessor}/show', 'EducazEscolas\ProfessorController@show');
    Route::post('create', 'EducazEscolas\ProfessorController@create');
    Route::put('update', 'EducazEscolas\ProfessorController@update');
    Route::post('importar', 'EducazEscolas\ProfessorController@importar');
    Route::delete('{id}/deletar', 'EducazEscolas\ProfessorController@deletar');
});

# Diretores
Route::prefix('diretores')->group(function () {
    Route::get('', 'EducazEscolas\DiretoresController@index');
    Route::get('{idDiretor}/show', 'EducazEscolas\DiretoresController@show');
    Route::get('{slugEscola?}/{slugTurma?}', 'EducazEscolas\DiretoresController@index');
    Route::post('create', 'EducazEscolas\DiretoresController@create');
    Route::put('update', 'EducazEscolas\DiretoresController@update');
    Route::post('importar', 'EducazEscolas\DiretoresController@importar');
    Route::delete('{id}/deletar', 'EducazEscolas\DiretoresController@deletar');
});

# Orientadores
Route::prefix('orientadores')->group(function () {
    Route::get('', 'EducazEscolas\OrientadorController@index');
    Route::get('{idDiretor}/show', 'EducazEscolas\OrientadorController@show');
    Route::get('{slugEscola?}/{slugTurma?}', 'EducazEscolas\OrientadorController@index');
    Route::post('create', 'EducazEscolas\OrientadorController@create');
    Route::put('update', 'EducazEscolas\OrientadorController@update');
    Route::post('importar', 'EducazEscolas\OrientadorController@importar');
    Route::delete('{id}/deletar', 'EducazEscolas\OrientadorController@deletar');
});

Route::get('cursos/{id}/get-cursos-itv', 'API\CursoController@getCursosItv');
Route::get('cursos/detalhes-estrutura-curricular/{curso}/{estrutura}/{categoria}/{usuario}', 'API\CursoController@detalhesCursoCorrente');
Route::prefix('itv')->group(function () {
    Route::get('cursos/{id}/calendario', 'API\CursoController@getCalendarioCursosItv');
});

// PERENNIALS
Route::group(['namespace'=>'API\PERENNIALS\V1', 'middleware' => 'auth.jwt'], function () {
    // ROTA PARA CRUD DE MENTORIAS / CATEGORIAS DE MENTORIAS
    Route::resource('mentoria', 'MentorsController');
    Route::resource('categorias-mentoria', 'MentorCategoriesController');
  
    
    Route::group(['prefix' => 'mentoria-comentarios', 'as'=>'mentor.comments'], function () {
        Route::get('/{mentoria?}', 'MentorComments@index')->name('index'); 
        Route::get('mentoria/{mentoria?}', 'MentorComments@show')->name('show');
        Route::post('mentoria/{mentoria?}', 'MentorComments@store')->name('store');
        Route::post('altera-situacao/{mentoriaComentario?}', 'MentorComments@situation')->name('situation');
        Route::delete('/{mentoriaComentario?}', 'MentorComments@destroy')->name('destroy');
    });
    
});
