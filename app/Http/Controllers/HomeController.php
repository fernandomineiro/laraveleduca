<?php

namespace App\Http\Controllers;

use App\{Aluno,
    Cidade,
    Curador,
    Curso,
    CursoTurmaInscricao,
    Estado,
    Helper\CertificadoHelper,
    Libraries\Stringable,
    Parceiro,
    Pedido,
    PedidoItem,
    Produtora,
    Professor};
use App\Helper\EducazMail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Notifications\InboxMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Image;

use App\Usuario;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


	public function home() {
        return view('home');
    }


    public function index($code = null) {
        echo (new \DateTime())->format('Y-m-d H:i:s');
		exit('index');
    }

    public function atualizarEndereco($usuario) {
	    
	    $collection = collect([
	        'aluno' =>  Aluno::cursor(),
            'professor' => Professor::cursor(),
            'curador' => Curador::cursor(),
            'parceiro' => Parceiro::cursor(),
            'produtor' => Produtora::cursor()
        ]);
	    
        $this->updateEndereco($collection->get($usuario));
    }

    public function updateEndereco(\Generator $generator) {
	    
        /**
         * Get a new stringable object from the given string.
         *
         * @param  string  $string
         * @return Stringable
         */
	    Str::macro('of', function ($string) {
	        return new Stringable($string);
        });

        $quantidade = collect($generator)
            ->filter(function($usuario) {
                return !empty($usuario->fk_endereco_id);
            })->each(function ($usuario) {
                echo 'Iniciando a atualização do endereço '. $usuario->endereco->id . ' - ';
                $enderecoAtualizado = $this->buscarEndereco($usuario->endereco->cep);
                
                if (empty($enderecoAtualizado->erro) && !empty($enderecoAtualizado->cep)) {
                    $estado = Estado::where('uf_estado', '=', $enderecoAtualizado->uf)->first();
                    $cidade = Cidade::where('descricao_cidade', 'like', $enderecoAtualizado->localidade ?? $enderecoAtualizado->cidade.'%')
                        ->where('fk_estado_id', '=', $estado->id)->first();

                    if  (!empty($estado) && !empty($cidade)) {
                        $usuario->endereco()->update([
                            'fk_estado_id' => $estado->id,
                            'fk_cidade_id' => $cidade->id,
                        ]);

                        $usuario->fresh();
                        echo 'Endereço atualizado com sucesso '. $usuario->endereco->id . '<br>';
                    } else {
                        echo 'Endereço não atualizado '. $usuario->endereco->id . '<br>';
                    }
                    

                    
                } else {
                    echo 'CEP não encontrado para o endereço '. $usuario->endereco->id . '<br>';
                }

            })->count();
        
        echo $quantidade .' endereços atualizados';
    }

    public function buscarEndereco($cep) {
	    
        $url = 'http://cep.la/' . 
                Str::of($cep)
                    ->replace('.', '')
                    ->replace('-', '') ;
        
        //create new instance of Client class
        $client = new Client();
        //. '/json'
        //send get request to fetch data
        $response = $client->request('GET', $url, [ 'headers' => [ 'Accept' => 'application/json']]);
        
//http://cep.la/01111100
        return json_decode($response->getBody()->getContents());
    }
    
    public function emailCertificado() {

	    try {

	        $pessoa = new PessoasController();

            /**
             * 'titular' =>  'Gabriel',
            'documento' =>  '706.747.060-99',
            'fk_banco_id' =>  '1',
            'tipo_conta' =>  'cp',
            'agencia' =>  '0001',
            'conta_corrente' =>  '1234098',
            'digita_conta' =>  '1',
            'operacao' =>  '1'
             */
            var_dump($pessoa->updateCreateAccountBank([

            ], null));
        } catch (\Exception $error) {
	        var_dump($error);die;
        }
	    /*$helper = new CertificadoHelper();
	    echo $helper->enviaCertificadoPorEmail(113);*/
    }

    public function testEmail() {

	    $educazMail = new EducazMail(1, true);

        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');

        echo ($educazMail->confirmacaoPedido([
            'messageData' => [
                'idPedido' => '01012019-01-01',
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'linkPerfil' => 'localhost:4200/#/perfil',
                'dataPedido' => strftime('%d de %B de %Y', strtotime('2019-03-10 03:36:01')),
                'formaPagamento' => 'Boleto Bancário',
                'totalPedido' => 'R$ 1.560,56',
                'tabelaCursos' => 'Aqui deverá ser inserido a tabela de cursos do pedido'
            ] //03 de junho de 2019
        ], false));

        echo ($educazMail->cancelamentoAssinatura([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'tituloAssinatura' => 'Teste',
                'valorAssinatura' => number_format(16.70, 2, ',', '.')
            ]
        ], true));

        echo ($educazMail->recuperarSenha([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'link' => 'localhost:8000',
                'email' => 'gabriel.resende06@gmail.com',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->emailBoasVindas([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'tableCursosOnline' => 'Tabela de cursos online',
                'tableCursosPresenciais' => 'Tabela de cursos Presenciais',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->renovarAssinatura([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->certificado([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nomeCurso' => 'Concurso Público – Licitações e Contratos Administrativos',
                'nomeProfessor' => 'Sérgio Nogueira',
            ] //03 de junho de 2019
        ], true));


        echo ($educazMail->promocoes([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'tableCursosOnline' => 'Tabela de cursos online',
                'tableCursosPresenciais' => 'Tabela de cursos Presenciais',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->alunoAvisoMensagem([
            'messageData' => [
                'nome' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'resposta' => 'Essa é uma resposta dada pelo professor ao aluno',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->avisoTrabalhoProfessor([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nomeAluno' => 'Bruna',
                'nomeCurso' => 'Concurso Público – Licitações e Contratos Administrativos',
                'diaLimite' => '30/09',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->avisoCursoAprovado([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nomeCurso' => 'Concurso Público – Licitações e Contratos Administrativos',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->avisoCursoAvaliar([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nomeCurso' => 'Concurso Público – Licitações e Contratos Administrativos',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->avisoTrabalhoEnviado([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nomeCurso' => 'Concurso Público – Licitações e Contratos Administrativos',
                'nomeAluno' => 'Bruna',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->professorAvisoMensagem([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->avisoPagamento([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'valorTotal' => 'R$ 15678,00',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->opiniaoProfessor([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
            ] //03 de junho de 2019
        ], true));

        echo ($educazMail->portalRecuperarSenha([
            'messageData' => [
                'nomeProfessor' => 'Gabriel Resende',
                'email' => 'gabriel.resende06@gmail.com',
                'nova_senha' => '12349asdfkj'
            ] //03 de junho de 2019
        ], true));


    }

    public function matricularAluno() {

	   $results =  DB::select(
	        'select distinct
                            cursos.id as id_curso,
                            pedidos.fk_usuario,
                            cursos_turmas.id as id_turma
                        from pedidos
                            join pedidos_item on pedidos.id = pedidos_item.fk_pedido
                            join cursos on pedidos_item.fk_curso = cursos.id
                            join cursos_turmas on cursos_turmas.fk_curso = pedidos_item.fk_curso
                        where cursos.fk_cursos_tipo in (2, 4)
                        and pedidos_item.id not in (
                            select distinct pedidos_item.id
                                from pedidos
                                    join pedidos_item on pedidos.id = pedidos_item.fk_pedido
                                    join cursos on pedidos_item.fk_curso = cursos.id
                                    join cursos_turmas on cursos_turmas.fk_curso = pedidos_item.fk_curso
                                    join cursos_turmas_inscricao on
                                        cursos_turmas_inscricao.fk_usuario = pedidos.fk_usuario and
                                        cursos_turmas_inscricao.fk_turma = cursos_turmas.id and
                                        cursos_turmas_inscricao.fk_curso = cursos.id
                         )'
        );

	   foreach ($results as $result) {
            $inscricao = new CursoTurmaInscricao();
            $inscricao->fill([
                'fk_usuario' => $result->fk_usuario,
                'fk_turma' => $result->id_turma,
                'fk_curso' => $result->id_curso,
                'percentual_completo' => 0,
                'status' => 1
            ]);

            $inscricao->save();
       }

        dd($results);
    }

    /**
     * Função que roda a verificação de disponibilidade para todos os cursos do sistema manualmente
     * Existe uma cron task (schedule) para executar automaticamente este processo diariamente
     * @return \Illuminate\Http\JsonResponse
     */
    public function rodaVerificacaoDisponibilidade($id = null) {
        try {
            if ($id) {
                $curso = Curso::obter($id);
                Curso::verificaDisponivelVenda($curso, false);
            } else {
                $cursos = Curso::all();
                foreach ($cursos as $curso) {
                    Curso::verificaDisponivelVenda($curso, false);
                }
            }
        } catch(\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
        }
    }

    /**
     * Função executada de hora em hora que verifica se há pedidos a processar junto a kroton
     * Existe uma cron task que roda essa função, mas ela também pode ser rodada via get Request (ver web.php para saber a chamada a se fazer nesse caso)
     * Verificar issue ED2-1184 e ED2-1367 para encontrar o documento específico que explica o procedimento completo
     */
    public function matricularAlunoKroton() {
        
        try {
            $headers = [
                'x-vtex-api-appkey' => 'vtexappkey-educaz-ADQTNW', 
                'x-vtex-api-apptoken' => 'XKEPDVBLBSBRENXPILJPYPCGLLMFXCRMUWLODZVKTITQVSOXPEXVWCGIIYDSJVOYBOWHCJMJQYWQVLKTXPQTFVPSPCGEVDBXTVPHVGUGROFYGPNXOKUTEVSGZERZDHNN'
            ];
            
            $client = new Client([
                'headers' => $headers
            ]);
            
            $response = $client->request('GET', 'http://educaz.vtexcommercestable.com.br/api/oms/pvt/orders?page=1&f_status=ready-for-handling');
            $paging = null;
            $orders = null;

            if ($response->getStatusCode() == 200) {
                $result = collect(json_decode($response->getBody()))->toArray();
                $paging = collect($result['paging'])->toArray();
                $orders = $result['list'];
              
                $this->processaPedidos($client, $orders);
              
                if ($paging['pages'] > 1) { // lógica de paginação
                    // adicionar aqui lógica para buscar mais pedidos
                    for($i = 2; $i < $paging['pages']; $i++) {
                        $response = $client->request('GET', 'http://educaz.vtexcommercestable.com.br/api/oms/pvt/orders?page='.$i.'&per_page=100&f_status=ready-for-handling');
                        $paging = null;
                        $orders = null;
                        if ($response->getStatusCode() == 200) {
                            $paging = $result['paging'];
                            $this->processaPedidos($client, $orders);
                        } else {
                            dd('Erro ao realizar consulta por pedidos', $response);
                        }
                    }
                }
                 //dd('Cadastro Realizado com sucesso');
            } else {
                // dd('Erro ao realizar consulta por pedidos', $response);
            }
        } catch (\Exception $e) {
            //dd($e->getMessage());
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
        }
    }


    /**
     * Função que processa os pedidos advindos da kroton 
     * Verificar issue ED2-1184 e ED2-1367 para encontrar o documento específico que explica o procedimento completo
     * @param Client $client
     * @param $orders
     * @throws \Exception
     */
    public function processaPedidos(Client $client, $orders) {
       
        DB::beginTransaction();
        
        foreach ($orders as $order) {
        
            $order = collect($order);
           
            $response = $client->request('GET', 'http://educaz.vtexcommercestable.com.br/api/oms/pvt/orders/' . $order['orderId']);
           
            if ($response->getStatusCode() == 200) {
                $result = collect(json_decode($response->getBody()))->toArray();
                $clientProfileData = collect($result['clientProfileData'])->toArray();
                $items = collect($result['items'])->toArray();
                
                // as inserções no sistema começam desse ponto para baixo
                $newUsuario = Usuario::where('email', $clientProfileData['email'])->first();

                if (!$newUsuario) {
                    $usuario = [
                        'nome' => $clientProfileData['firstName'] . ' ' . $clientProfileData['lastName'],
                        'email' => $clientProfileData['email'],
                        'password' => bcrypt($this->gerarSenha(12, true, true, true, true)),
                        'fk_perfil' => 14,
                        'fk_faculdade_id' => 7,
                        'foto' => null,
                        'status' => 1,
                        'aluno_kroton' => 1
                    ];
                    $newUsuario = Usuario::create($usuario);

                    if (!$newUsuario->id) {
                        DB::rollBack();
                        throw new \Exception('Erro ao criar novo usuário');
                    }
                }
               
                $newAluno = Aluno::where('cpf', $clientProfileData['document'])->first();
                
                if (!$newAluno) {
                    $aluno = [
                        'nome' => $clientProfileData['firstName'],
                        'sobre_nome' => $clientProfileData['lastName'],
                        'cpf' => $clientProfileData['document'],
                        'telefone_2' => $clientProfileData['phone'],
                        'fk_usuario_id' => $newUsuario->id,
                        'fk_faculdade_id' => 7,
                        'status' => 1,
                        'matricula' => $result['marketplaceOrderId'],
                    ];

                    $newAluno = Aluno::create($aluno);

                    if (!$newAluno->id) {
                        DB::rollBack();
                        throw new \Exception('Erro ao criar novo aluno');
                    }
                }

                $newPedido = Pedido::where('pid', $result['orderId'])->where('metodo_pagamento', 'kroton')->first();

                if (!$newPedido) {
                    $pedido = [
                        'pid' => $result['orderId'],
                        'fk_faculdade' => 7,
                        'fk_usuario' => $newUsuario->id,
                        'valor_bruto' => $result['value']/100,
                        'valor_liquido' => $result['value']/100,
                        'status' => 2,
                        'metodo_pagamento' => 'kroton',
                        'data_compra_externa' => $result['creationDate'],
                    ];
                    
                    $newPedido = Pedido::create($pedido);
                    if (!$newPedido->id) {
                        DB::rollBack();
                        throw new \Exception('Erro ao criar novo pedido');
                    }
                }
                              
                foreach ($items as $item) {
                    $item = collect($item);
                    
                    $newPedidoItem = PedidoItem::where('fk_curso', $item['refId'])->where('fk_pedido', $newPedido->id)->first();
                    if (!$newPedidoItem) {
                        $pedido_item = [
                            'valor_bruto' => $item['price']/100,
                            'valor_desconto' => $item['listPrice']/100,
                            'valor_liquido' => $item['sellingPrice']/100,
                            'fk_produto_externo_id' => $item['productId'],
                            'status' => 1,
                            'fk_pedido' => $newPedido->id,
                            'fk_curso' => $item['refId']
                        ];
                        $newPedidoItem = PedidoItem::create($pedido_item);
                        if (!$newPedidoItem->id) {
                            DB::rollBack();
                            throw new \Exception('Erro ao salvar item do pedido!');
                        }
                    }
                }
                
                DB::commit();
                
                // esse if não deve ser removido pois o que vem a seguir só pode/deve ser executado em produção
                if (App::environment('production')) {
                    $response = $client->request('POST', 'http://educaz.vtexcommercestable.com.br/api/oms/pvt/orders/' . $order['orderId'] . '/start-handling');
                }
            } else {
                throw new \Exception('Erro ao realizar consulta por pedido');
            }
        }
        return;
    }
}
