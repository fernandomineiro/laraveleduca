<?php

namespace App\Http\Controllers\API;

use App\Curador;
use App\Faculdade;
use App\Parceiro;
use App\Produtora;
use App\ViewUsuarioCompleto;
use App\ViewUsuarios;
use App\Professor;
use App\ProfessorFormacao;
use App\ProfessorFormacaoTipo;
use App\Proposta;
use App\PropostaModulo;
use App\Usuario;
use App\Endereco;
use App\Estado;
use App\Cidade;
use App\ContaBancaria;
use App\Helper\EducazMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helper\WirecardHelper;

class ProfessorController extends Controller {

    public function __construct() {
        \Config::set('jwt.user', 'App\ViewUsuarios');
        \Config::set('auth.providers.users.model', ViewUsuarios::class);
    }

    /**
     * @param int $idFaculdade
     * @param null $idCategoria
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idFaculdade = 7, $idCategoria = null, Request $request)
    {
        try {

            $sql = 'select 
                        professor.id,
                        CONCAT(professor.nome, " ",  IFNULL(professor.sobrenome, ""))   as nome_professor,
                        usuarios.foto ,
                        professor.status,
                        cursos_faculdades.fk_faculdade,
                        count(pedidos_item.id) as qtd_pedidos,
                        (SELECT count(*) as qtd_cursos_publicados
                                            FROM cursos
                                                join cursos_faculdades as cf ON cf.fk_curso = cursos.id 
                                                where cursos.status = 5 AND cursos.fk_professor = professor.id
                                                AND cf.fk_faculdade = cursos_faculdades.fk_faculdade
                                            group by fk_professor, cursos_faculdades.fk_faculdade) as qtd_cursos_publicados 
                    from usuarios 
                        join professor on professor.fk_usuario_id = usuarios.id
                        join cursos on cursos.fk_professor = professor.id
                        join cursos_categoria_curso on cursos_categoria_curso.fk_curso = cursos.id
                        left join pedidos_item on pedidos_item.fk_curso = cursos.id
                        left join pedidos on pedidos.id = fk_pedido
                        join cursos_faculdades ON cursos_faculdades.fk_curso = cursos.id 
                where cursos.status = 5
                AND professor.status != 0  and usuarios.status != 0 ';

            if ($request->hasHeader('Faculdade')) {
                $idFaculdade = $request->header('Faculdade', 7);
            }
            
            if ($request->hasHeader('Faculdade')) {
                $idFaculdade = $request->header('Faculdade', 7);
            }

            if ($idFaculdade) {
                $sql .= ' AND cursos_faculdades.fk_faculdade = "' . $idFaculdade . '"';
            }

            $sql .= '                
                    group by professor.id, professor.nome, professor.sobrenome, usuarios.foto , professor.status
                    order by professor.nome asc, count(pedidos_item.id) desc';

            $professores = DB::select($sql);

            return response()->json(['items' => $professores, 'count' => count($professores)]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'menssage' => $e->getMessage()
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function promocoes() {
        try {
            $sql = 'select
                    usuarios.id,
                    usuarios.nome as nome_professor,
                    usuarios.foto ,
                    usuarios.status,
                    qtd.qtd_cursos_publicados

                    from usuarios
                        join cursos on cursos.fk_professor = usuarios.id
                        JOIN cursos_valor on cursos_valor.fk_curso = usuarios.id and cursos_valor.valor_de > cursos_valor.valor
                        join (
                            SELECT count(*) as qtd_cursos_publicados, fk_professor as id
                            FROM cursos
                            group by fk_professor
                        ) qtd ON qtd.id = usuarios.id

                    where usuarios.status != 0

                    group by usuarios.id, usuarios.nome, usuarios.foto , usuarios.status, qtd.qtd_cursos_publicados
                    order by cursos_valor.criacao desc, usuarios.nome asc';

            $professores = DB::select($sql);

            return response()->json(['items' => $professores]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentes() {
        try {
            $sql = 'select
                    usuarios.id,
                    usuarios.nome as nome_professor,
                    usuarios.foto ,
                    usuarios.status,
                    qtd.qtd_cursos_publicados

                    from usuarios
                        join cursos on cursos.fk_professor = usuarios.id
                        JOIN cursos_valor on cursos_valor.fk_curso = usuarios.id and cursos_valor.valor_de > cursos_valor.valor
                        join (
                            SELECT count(*) as qtd_cursos_publicados, fk_professor as id
                            FROM cursos
                            group by fk_professor
                        ) qtd ON qtd.id = usuarios.id

                    where usuarios.status != 0

                    group by usuarios.id, usuarios.nome, usuarios.foto , usuarios.status, qtd.qtd_cursos_publicados
                    order by cursos.criacao desc, usuarios.nome asc';

            $professores = DB::select($sql);

            return response()->json(['items' => $professores]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @param $searchTerm
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProfessor($searchTerm, Request $request) {
        try {
            $searchTerm = addslashes(mb_strtolower($searchTerm, mb_detect_encoding($searchTerm)));

            $sql = "select
                    professor.id,
                    usuarios.nome,
                    CONCAT(professor.nome, ' ', professor.sobrenome) as nome_professor,
                    usuarios.foto ,
                    professor.status,
                    (SELECT count(*) as qtd_cursos_publicados
                            FROM cursos
                                join cursos_faculdades as cf ON cf.fk_curso = cursos.id 
                                where cursos.status = 5 AND cursos.fk_professor = professor.id
                                AND cf.fk_faculdade = cursos_faculdades.fk_faculdade
                            group by fk_professor, cursos_faculdades.fk_faculdade) as qtd_cursos_publicados 
                    from usuarios 
                        join professor on professor.fk_usuario_id = usuarios.id
                        join cursos on cursos.fk_professor = professor.id
                        join cursos_categoria_curso on cursos_categoria_curso.fk_curso = cursos.id
                        left join pedidos_item on pedidos_item.fk_curso = cursos.id
                        left join pedidos on pedidos.id = fk_pedido
                        join cursos_faculdades ON cursos_faculdades.fk_curso = cursos.id 
                where cursos.status = 5
                AND professor.status != 0  and usuarios.status != 0 ";

            $sql .= " AND (lower(usuarios.nome) like '%{$searchTerm}%' 
                            OR lower(CONCAT(professor.nome, ' ', professor.sobrenome)) like '%{$searchTerm}%' 
                          )";

            if ($request->hasHeader('Faculdade')) {
                $sql .= "AND cursos_faculdades.fk_faculdade = '" . $request->header('Faculdade', 7) . "'";
            }

            $sql .= "group by usuarios.id, usuarios.nome, usuarios.foto , usuarios.status, cursos.criacao
                    order by cursos.criacao desc, usuarios.nome asc";

            $professores = DB::select($sql);

            return response()->json(['items' => $professores]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id = null) {
        try {
            $professor = Professor::where('id', $id)->with('usuario')->get()->toArray();

            return response()->json(['data' => $professor]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function showProfessorByIdUsuario($id = null) {
        try {

            $loggedUser = JWTAuth::user();
            $usuario = ViewUsuarioCompleto::where('fk_usuario_id', $loggedUser->id)->with('conta.banco')->with('endereco')->first();
            if (empty($usuario)) {
                return response()->json(['success' => false, 'items' => []]);
            }

            return response()->json(['success' => true, 'items' => $usuario->toArray()]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function salvarDadosBancarios(Request $request) {
        try {
            //#f2652a

            $conta = ContaBancaria::updateOrCreate(
                ['id' => $request->get('id')], $request->all()
            );

            $usuario = $this->_getObjectUsuario();
            $usuario->fk_conta_bancaria_id = $conta->id;
            $usuario->save();

            return response()->json(['success' => true, 'items' => $conta]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function salvarEndereco(Request $request) {
        try {

            if (empty($request->get('idUsuario'))) {
                throw new \Exception('Usuário não informado');
            }

            $endereco = Endereco::updateOrCreate(
                ['id' => $request->get('id')], $request->all()
            );

            $obj = $this->_getObjectUsuario();
            $obj->fk_endereco_id = $endereco->id;
            $obj->save();


            return response()->json(['success' => true, 'items' => $endereco]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function salvarMiniCurriculo(Request $request) {
        try {

            $professor = Professor::where('fk_usuario_id', $request->get('id'))->first();
            $professor->mini_curriculum = $request->get('mini_curriculum');
            $professor->save();

            return response()->json(['success' => true, 'items' => $request->all()]);
        }  catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    protected function _getObjectUsuario() {
        $loggedUser = JWTAuth::user();

        switch ($loggedUser->fk_perfil) {
            case 1:
                return Professor::where('fk_usuario_id', $loggedUser->id)->first();
                break;
            case 2:
                return Faculdade::where('id', $loggedUser->fk_faculdade_id)->first();
                break;
            case 4:
                return Curador::where('fk_usuario_id', $loggedUser->id)->first();
                break;
            case 5:
                return Produtora::where('fk_usuario_id', $loggedUser->id)->first();
                break;
            case 19:
                return Parceiro::where('fk_usuario_id', $loggedUser->id)->first();
                break;
        }
    }

    public function salvarProfessor(Request $request) {
        try {

            $obj = $this->_getObjectUsuario();
            $data = $request->except('id');

            if ($obj instanceof Curador) {
                $data['titular_curador'] = $data['responsavel'];
            }
            
            $obj->fill($data);
            $obj->save();

            return response()->json(['success' => true, 'items' => $obj->refresh(), 'test' => $obj instanceof Curador, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTiposFormacao()
    {
        try {
            $tipos = ProfessorFormacaoTipo::where('status', 1)->get()->toArray();

            return response()->json(['items' => $tipos]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function criarUsuarioProfessor($data) {
        $usuario = new Usuario();
        $usuario->fill($data);
        $usuario->save();
        return $usuario->refresh();
    }

    /**
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
       try {
            DB::beginTransaction();

            $professorData = $request->professor;
            $professorData['fk_estado_id'] = $professorData['estado'];
            $professorData['fk_cidade_id'] = $professorData['cidade'];

            $endereco = new Endereco();
            $endereco->fill($professorData);

            $endereco->save();

            $professor = new Professor($request->professor);

            $professor->fk_endereco_id = $endereco->id;
            // não existe funcionalidade ainda para a linha abaixo, por isso foi removida
            // $professor->fk_faculdade_id = !is_null($request->get('fk_faculdade_id')) ? $request->get('fk_faculdade_id') : null;
            $professor->nome = !is_null($request->get('professor')['nome']) ? $request->get('professor')['nome'] : null;
            $professor->sobrenome = !is_null($request->get('professor')['sobrenome']) ? $request->get('professor')['sobrenome'] : null;

            $usuario = $this->criarUsuarioProfessor([
                'nome' => $professor->getName(),
                'email' => $request->professor['email'],
                'password' => bcrypt($request->professor['senha']),
                'fk_perfil' => Professor::ID_PERFIL,
                'status' => 2
            ]);

            $professor->fk_usuario_id = $usuario->id;

            // TODO: validar status inicial
            $professor->status = 2;

            $validator = $professor->_validateApi($request->professor);

            $errors = [];

            if ($validator->fails()) {
                $errors['professor'] = $validator->messages()->all();
                throw new \InvalidArgumentException();
            }

            $data_account_wirecard = $this->prepareDadosContaWirecard($request->all()['professor'], $professorData['fk_estado_id'], $professorData['fk_cidade_id']);

            $wirecard = new WirecardHelper;
            $createAccount = $wirecard->createAccount($data_account_wirecard, 'professor');

            if (!empty($createAccount['success']) && !empty($createAccount['wirecard_account_id'])) {
                $professor->wirecard_account_id = $createAccount['wirecard_account_id'];
            } elseif (!empty($createAccount['error'])) {
                return response()->json([
                    'success' => false,
                    'messages' => $createAccount['error']
                ]);
            }

            $professor->save();

            foreach((array) $request->professor_formacao as $formacao) {

                $professorFormacao = new ProfessorFormacao($formacao);

                $validator = Validator::make($professorFormacao->toArray(), $professorFormacao->rules, $professorFormacao->messages);
                if ($validator->fails()) {
                    $errors['professor_formacao'] = $validator->messages()->all();
                    throw new \InvalidArgumentException();
                }

                $professor->formacoes()->save($professorFormacao);
            }

            if(!empty($request->proposta)) {

                $proposta = new Proposta($request->proposta);

                $proposta->fk_proposta_status = 1;

                $validator = Validator::make($proposta->toArray(), $proposta->rules, $proposta->messages);
                if ($validator->fails()) {
                    $errors['proposta'] = $validator->messages()->all();
                    throw new \InvalidArgumentException();
                }

                $professor->propostas()->save($proposta);

                if(!empty($request->proposta_modulos)) {
                    $ordem = 0;
                    foreach((array) $request->proposta_modulos as $modulo) {

                        $propostaModulo = new PropostaModulo($modulo);
                        $propostaModulo->ordem_modulo = $ordem++;

                        if (!isset($propostaModulo->status)) {
                            $propostaModulo->status = 1;
                        }

                        $validator = Validator::make($propostaModulo->toArray(), $propostaModulo->rules, $propostaModulo->messages);
                        if ($validator->fails()) {
                            $errors['proposta_modulos'] = $validator->messages()->all();
                            throw new \InvalidArgumentException();
                        }

                        $proposta->modulos()->save($propostaModulo);
                    }
                }
            }

            DB::commit();

            $this->sendEmailNovoCadastroProfessor(7);
            $this->sendEmailCadastroEmAnalise($professor->getName(), $request->professor['email'], 7);

            return response()->json([
                'success' => true,
                'data' => Professor::find($professor->id)->toArray()
            ]);

        } catch (\InvalidArgumentException $e){

            DB::rollBack();

            return response()->json([
                'success' => false,
                'messages' => 'Problemas ao cadastrar professor, existem informações obrigatórias faltando ou incorretas.',
                'validator' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

           $sendMail = new EducazMail(7);
           $sendMail->emailException($e);

            return response()->json([
                'success' => false,
                'messages' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema',
                'tracer' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }

    }

    /**
     * @param lluminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

    }

    /**
     * Retorna os cursos do professor
     *
     * @param $professorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cursos($id = null) {
        try {
            /** @var Professor $professor */
            $professor = Professor::find($id);

            $cursos = $professor ? $professor->getCursosCards() : [];

            return response()->json(['items' => $cursos, 'count' => count($cursos)]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function sendEmailCadastroEmAnalise($nome, $email, $idFaculdade = 7){
        $EducazMail = new EducazMail($idFaculdade);

        $data = $EducazMail->cadastroDeProfessorEmAnalise([
            'messageData' => [
                'email' => $email,
                'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                'nome' => $nome,
            ]
        ]);
    }

    /**
     * Retorna os cursos de um determinado professor, em uma determinada instituição de Ensino
     *
     * @param $professor_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cursosByProfessorIDAndFaculdadeID($professor_id, $faculdade_id) {
        try {
            /** @var Professor $professor */
            $professor = Professor::find($professor_id);

            $cursos = $professor->getCursosCardsByFaculdadeID($professor_id, $faculdade_id);

            return response()->json(['items' => $cursos, 'count' => count($cursos)]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }

    public function sendEmailNovoCadastroProfessor($idFaculdade = 7){
        $EducazMail = new EducazMail($idFaculdade);

        $data = $EducazMail->avisoNovoCadastroProfessor([
            'messageData' => [
                'img_logo' => Url('/') . '/sitenovo/img/Educaz_preto.png',
                'email' => 'afukumitsu@educaz.com.br'
            ]
        ]);
    }

    private function prepareDadosContaWirecard($data, $fk_estado_id, $fk_cidade_id){
        $data_account = array();

        $estado = Estado::select('uf_estado')->find($fk_estado_id);
        $cidade = Cidade::select('descricao_cidade')->find($fk_cidade_id);

        $data_account['address'] = [
            'street' => $data['logradouro'],
            'number' => $data['numero'],
            'district' => $data['bairro'],
            'zipcode' => preg_replace("/[^0-9]/",
                "",
                $data['cep']),
            'city' => $cidade->descricao_cidade,
            'state' => $estado->uf_estado,
            'country' => 'BRA'
        ];

        $data_account['email'] = $data['email'];


        $data_account['name'] = $data['nome'];
        $data_account['lastname'] = $data['sobrenome'];

        $data_account['birth_data'] = $data['data_nascimento'];
        $data_account['cpf'] = preg_replace("/[^0-9]/", "", $data['cpf']);

        $phone_number = $this->getPhone($data);

        $data_account['phone'] = ['ddd' => $phone_number['ddd'], 'number' => $phone_number['number'], 'prefix' => '55'];

        return $data_account;
    }

    private function getPhone($customer){
        if (!empty($customer['telefone_1'])) {
            $number_phone = $customer['telefone_1'];
        } elseif (!empty($customer['telefone_2'])) {
            $number_phone = $customer['telefone_2'];
        } elseif (!empty($customer['telefone_3'])) {
            $number_phone = $customer['telefone_3'];
        }

        if (!empty($number_phone)) {
            $phone = preg_replace("/[^0-9]/", "", $number_phone);
            $data['ddd'] = substr($phone, 0, 2);
            $data['number'] = substr($phone, 2, 9);

            return $data;
        } else {
            return false;
        }
    }

    public function getProfessoresCriarCurso() {
        try {
            $professores = Professor::select(DB::raw("CONCAT(nome,' ', sobrenome) AS nome_professor"), 'id', 'fk_usuario_id')
                ->where('status', '=', 1)
                ->orderBy('nome_professor')
                ->get();

            return response()->json(['items' => $professores]);
        } catch (\Exception $e) {
            $sendMail = new EducazMail(7);
            $sendMail->emailException($e);
            return response()->json([
                'success' => false,
                'error' => 'Um erro ocorreu. O suporte já foi avisado para corrir o problema'
            ]);
        }
    }
}
