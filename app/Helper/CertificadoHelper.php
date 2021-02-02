<?php

namespace App\Helper;

use App\EstruturaCurricular;
use App\Helper\EducazMail;
use App\PedidoItem;
use App\Repositories\CursoRepository;
use App\Services\ItvService;
use GuzzleHttp\Client;
use PDF;
use App\Aluno;
use App\Usuario;
use App\Certificado;
use App\CertificadoLayout;
use App\Curso;
use App\Quiz;
use App\QuizResultado;
use App\CursoTurmaInscricao;
use App\CursoModulo;
use App\CursosTrabalhos;
use App\CursosTrabalhosUsuarios;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\CursoTipo;
use App\ConfiguracoesHomeFooterHeader;
use App\ConfiguracoesLogotipos;
use App\ConclusaoCursosFaculdades;
use App\Faculdade;
use App\ConfiguracoesEstilos;
use PHPUnit\Runner\Exception;

class CertificadoHelper{

    public function getCertificado($idUsuario, $idCurso){
        $certificado = Certificado::where('fk_usuario', $idUsuario)
            ->where('fk_curso', $idCurso)
            ->active()
            ->get();
        
        if($certificado && count($certificado) > 0){
            return $certificado;
        } else {
            return [];
        }
    }
    
    public function getCertificadoEstrutura($idUsuario, $idEstrutura){
        $certificado = Certificado::where('fk_usuario', $idUsuario)
            ->where('fk_estrutura', $idEstrutura)
            ->get();
        
        if (!$certificado || count($certificado) == 0){
            return false;   
        }

        return $certificado;
    }

    public function getCertificados($idUsuario){
        $certificados = Certificado::where('fk_usuario', $idUsuario)->get();
        if($certificados && count($certificados) > 0){
            return $certificados;
        } else {
            return [];
        }
    }

    public function getConclusaoQuestionario($idUsuario, $idCurso){
        $usuario = Usuario::find($idUsuario);          
        $faculdade_id = $usuario->fk_faculdade_id;  
        
        $criterios_conclusao = ConclusaoCursosFaculdades::where([
            ['fk_curso', $idCurso], 
            ['fk_faculdade', $faculdade_id]
        ])->first();
        
        
        //caso nota de corte seja 0, automaticamente considera-se como aprovado no questionário
        //if($criterios_conclusao && $criterios_conclusao['nota_quiz'] == 0){
        //    return true;
        //}

        //caso o usuário tenha desistido, conta como concluída a etapa
        $quiz = Quiz::where("fk_curso", $idCurso)->first();
        if($quiz){
            $quiz_resultados = QuizResultado::where('fk_quiz', $quiz->id)
            ->where('fk_usuario', $idUsuario)->get();

            $quiz_desistencia = $quiz_resultados->pluck('solicitou_gabarito');
            if ($quiz_desistencia) {
                $desistencia = $quiz_desistencia->contains(1);
                if ($desistencia) {
                    return true;
                }
            }

            config()->set('database.connections.mysql.strict', false);
            \DB::reconnect();

            $entregasQuiz = Quiz::select(
                DB::raw("quiz.id AS quizId, 
            quiz_resultado.id as quizResultadoId, 
            cursos.id AS cursoId, 
            cursos.titulo, 
            quiz_resultado.fk_usuario,
            conclusao_cursos_faculdades.nota_quiz AS nota_quiz,
            MAX(qtd_acertos/(qtd_acertos+qtd_erros)*100) AS percentual,
            CASE WHEN conclusao_cursos_faculdades.nota_quiz <= MAX(qtd_acertos/(qtd_acertos+qtd_erros)*100) THEN true ELSE false
            END AS mediaAtingida
           "))
                ->leftJoin('quiz_resultado', 'quiz_resultado.fk_quiz', '=', 'quiz.id')
                ->join('cursos', 'quiz.fk_curso', '=', 'cursos.id')
                ->leftJoin('conclusao_cursos_faculdades', 'conclusao_cursos_faculdades.fk_curso', '=', 'cursos.id')
                ->where('cursos.id', $idCurso)
                ->where(function($query) use ($idUsuario){
                    $query->where('fk_usuario', '=', $idUsuario);
                    $query->orWhereNull('fk_usuario');
                })
                ->where(function($query) use ($faculdade_id){
                    $query->where('conclusao_cursos_faculdades.fk_faculdade', '=', $faculdade_id);
                    $query->orWhereNull('conclusao_cursos_faculdades.fk_faculdade');
                })
                ->where('quiz.id', $quiz->id)
                ->groupBy('quiz_resultado.fk_quiz')
                ->orderBy('percentual', 'DESC')
                ->get();


                config()->set('database.connections.mysql.strict', true);
                \DB::reconnect();
                
                if($entregasQuiz && count($entregasQuiz) > 0){
                    foreach ($entregasQuiz as $entrega){
                        if($entrega['mediaAtingida']== 0){
                            //$resposta['error'] = "Média não atingida em um dos questionários obrigatórios";                    
                            return false;
                        }
                    }
                    return true;
                }
                return false;
        } else {
            return true;
        }
        
    }

    public function getConclusaoTrabalho($idUsuario, $idCurso){
        $usuario = Usuario::find($idUsuario);          
        $faculdade_id = $usuario->fk_faculdade_id; 
        
        $criterios_conclusao = ConclusaoCursosFaculdades::where([
            ['fk_curso', $idCurso], 
            ['fk_faculdade', $faculdade_id]
        ])->first();
        
        if($criterios_conclusao && !isset($criterios_conclusao['nota_trabalho'])){
            return true;
        }

        $cursoTrabalho = CursosTrabalhos::select('id')->where('fk_cursos', '=', $idCurso)->first();  
        if($cursoTrabalho){
            $trabalhosEntregues = CursosTrabalhosUsuarios::where([
                ['fk_usuario', $idUsuario], 
                ['fk_cursos_trabalhos', $cursoTrabalho->id]
            ])->get();
            if(!$trabalhosEntregues){                
                return false;
            }    
            $nota_trabalho = 0;
            foreach($trabalhosEntregues as $trabalho){
                if($trabalho['nota'] > $nota_trabalho) $nota_trabalho = $trabalho['nota'];    
            }   
            if($criterios_conclusao['nota_trabalho'] > ($nota_trabalho * 10)){                
                return false;
            } 
            return true; 
        } else {            
            return false;
        }
    }

    public function disponibilidadeCertificado(Usuario $usuario, $idCurso) {

        $curso = $this->verificaSeCursoExiste($idCurso);
        if (!($curso instanceof Curso)) {
            return $curso;
        }
        
        $certificado = $this->getCertificado($usuario->getId(), $idCurso);
        if($certificado && count($certificado) > 0){
            return ['success' => false, 'error' => 'Certificado já existe', 'certificado' => $certificado];
        }
        
        if(!$criterios_conclusao = $this->verificaCriteriosParaConclusaoCursoFaculdade($idCurso, $usuario->fk_faculdade_id)) {
            return ['success' => false, 'error' => 'Critérios para conclusão não encontrados'];
        }
        
        if(!$this->getConclusaoQuestionario($usuario->id, $idCurso)) {
            return ['success' => false, 'error' => 'Média não atingida em um dos questionários obrigatórios'];
        }
        
        if (!$this->getConclusaoTrabalho($usuario->id, $idCurso)) {
            return ['success' => false, 'error' => 'Média não atingida em um dos trabalhos obrigatórios'];
        }        
        
        if ($this->eNecessarioVerificarPercentualDeModulosAssistidos($curso)) {            
            $percentualOnline = $this->percentualOnline($usuario->id, $idCurso);
            if ($percentualOnline < 100) {
                return ['success' => false, 'error' => 'Percentual de módulos online assistidos não atingido'];
            }         
        }
        //verifica percentual de conclusao - Presencial (Tipo 2 - Presencial ou Tipo 4 - Remoto)
        if ($curso->fk_cursos_tipo == Curso::PRESENCIAL || $curso->fk_cursos_tipo == Curso::REMOTO) {

            //verifica se todas as agendas já passaram
            $agendas = Curso::agendaPorCurso($idCurso);
            $count_agendas_p = 0;        
            foreach ($agendas as $agenda) {
                $start = strtotime($agenda->data_inicio. ' ' . $agenda->hora_inicio);
                $end = strtotime('now');
                if ($start  < $end) {
                    $count_agendas_p +=1;
                }
            } 
            if (!(count($agendas) == $count_agendas_p)) {
                return ['success' => false, 'error' => 'Data da última agenda ainda não passou'];
            };

            //verifica se possui o percentual de frequência necessário
            $percentualPresencial = $this->percentualPresencial($usuario->id, $idCurso);
            if($percentualPresencial < $criterios_conclusao['freq_minima']){
                return ['success' => false, 'error' => 'Percentual de modulos presenciais assistidos não atingido'];
            } 
        }  
        
        return ['success' => true];
    }

    public function percentualOnline($idUsuario, $idCurso) {        
        $modulosAssistidos = CursoModulo::select(DB::raw('DISTINCT fk_modulo, fk_usuario, cursos_modulos.fk_curso'))
            ->join('modulos_usuarios', 'fk_modulo', '=', 'cursos_modulos.id')
            ->where('fk_usuario', '=', $idUsuario)
            ->where('cursos_modulos.fk_curso', '=', $idCurso)
            ->where('cursos_modulos.status', 1)
            ->get();     
        
        $total_modulos = CursoModulo::select(DB::raw("COUNT(1) as total_modulos"))
            ->join('cursos_secao', 'cursos_secao.id', '=', 'cursos_modulos.fk_curso_secao')
            ->where('cursos_secao.fk_curso', $idCurso)
            ->where('cursos_secao.status', 1)
            ->where('cursos_modulos.status', 1)
            ->get()
            ->first();        
        
        $progresso = 0;
        if(count($modulosAssistidos) != 0 && $total_modulos['total_modulos'] != 0) {
            $progresso = (count($modulosAssistidos)/$total_modulos['total_modulos'])*100;
        } 
            
        return $progresso;
    }

    public function percentualPresencial($idUsuario, $idCurso){
        $cursoTurmaInscricao = CursoTurmaInscricao::where('fk_usuario', $idUsuario)->where('fk_curso', $idCurso)->first();
        if ($cursoTurmaInscricao) {
            return $cursoTurmaInscricao['percentual_completo'];
        }            
            
        return 0;
    }

    public function emiteCertificado($idUsuario, $idCurso) {

        $usuario = $this->verificaSeUsuarioExiste($idUsuario);
        if (!($usuario instanceof Usuario)) {
            return $usuario;
        }
        
        if (
            Curso::isLayoutEstruturaCurricular($usuario->fk_faculdade_id) && 
            $this->estruturaCursoPossuiCertificado($usuario, $idCurso)
        ) {
            $estruturasCertificadoAptas = $this->retornarEstruturasAptasCertificacao($usuario);
            if (empty($estruturasCertificadoAptas)) {
                return ['success' => false, 'error' => 'Nenhuma estrutura está apta a certificação'];
            }

            foreach ($estruturasCertificadoAptas as $estrutura) {
                $this->emiteCertificadoEstruturaCurricular($idUsuario, $estrutura['id']);
            }
            return ['success' => true];
        } else {
            $disponibilidade = $this->disponibilidadecertificado($usuario, $idCurso);
            if (!$disponibilidade['success']) {
                return $disponibilidade;
            }

            if (!$criterios_conclusao = $this->verificaCriteriosParaConclusaoCursoFaculdade($idCurso, $usuario->fk_faculdade_id)) {
                return ['success' => false, 'error' => 'Critérios para conclusão não encontrados'];
            }

            if (!isset($criterios_conclusao['fk_certificado'])) {
                return ['success' => false, 'error' => 'Curso não emite certificado'];
            } else if ($criterios_conclusao['fk_certificado'] == 0) {
                return $this->emiteCertificadoPadrao($idUsuario, $idCurso);
            } else {
                return $this->emiteCertificadoPersonalizado($idUsuario, $idCurso, $criterios_conclusao['fk_certificado']);
            }
        }
    }

    public function emiteCertificadoEstruturaCurricular($idUsuario, $idEstrutura) {
        $usuario = Usuario::find($idUsuario);
        $estrutura = EstruturaCurricular::find($idEstrutura);
        $aluno = Aluno::where('fk_usuario_id', $usuario->id)->first();

        $this->setDefaultTimezone();
        $data = $this->formatarDataCertificado();

        $faculdadeId = $usuario->fk_faculdade_id;
        $faculdade = Faculdade::select('url', 'fantasia')->find($faculdadeId);

        $configFaculdade = $this->getConfiguracoesEstilosFaculdade($faculdadeId);
        if (!$configFaculdade) {
            return ['success' => false, 'error' => 'Configurações de cores da instituição de ensino não encontradas.'];
        }

        $configLogo = ConfiguracoesLogotipos::where('fk_faculdade_id', $faculdadeId)->first();
        if (!$configLogo) {
            return ['success' => false, 'error' => 'Configurações de logo da instituição de ensino não encontradas.'];
        }

        $certificadoLayout = CertificadoLayout::findOrFail($estrutura->fk_certificado_layout);
        if (!$certificadoLayout) {
            return ['success' => false, 'error' => 'Layout de certificado não encontrado'];
        }

        try {
            DB::beginTransaction();

            $certificado = $this->inserirCertificado(null, $idUsuario, $idEstrutura);
            if (!$certificado) {
                throw new \Exception('Não foi possível salvar o certificado.');
            }
            
            $params = [
                'usuario' => $usuario,
                'estrutura' => $estrutura,
                'url_qrcode' => $this->generateQrcodeUrl($certificado->id),
                'data' => $data,
                'code' => base64_encode( 'educaz-'.$certificado->id ),
                'cursoTipo' => '',
                'cor' => $configFaculdade->cor_principal,
                'urlLogo' => $configLogo->url_logtipo,
                'certificadoLayout' => $certificadoLayout,
                'faculdadeFantasia' => $faculdade->fantasia,
            ];

            $response = $this->gerarPdfCertificado($params, $certificado, 'certificados.emitecertificadoestrutura');
            if(!$response['success']){
                DB::rollback();
                return $response;
            }

            DB::commit();
            $this->concluirGeracaoCertificado($usuario, $estrutura, $aluno, $certificado);

            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollback();
            return ['success' => false, 'error' => 'Erro na inserção de registro no banco de dados.', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
        }
    }
    
    public function emiteCertificadoPadrao($idUsuario, $idCurso) {  
        $resposta['success'] = false;

        $usuario = Usuario::find($idUsuario);
        $curso = Curso::find($idCurso);
        $aluno = Aluno::where('fk_usuario_id', $usuario->id)->first();
        
        $this->setDefaultTimezone();
        $data = $this->formatarDataCertificado();

        $faculdadeId = $usuario->fk_faculdade_id;
        $cursoTipo = $this->getCursoTipo($curso);
        $nomeProfessor = $this->getNomeCompletoProfessor($curso->id, $faculdadeId);

        $configFaculdade = $this->getConfiguracoesEstilosFaculdade($faculdadeId);
        if (!$configFaculdade) {
            return ['success' => false, 'error' => 'Configurações de cores da instituição de ensino não encontradas.'];
        }
        
        $configLogo = ConfiguracoesLogotipos::where('fk_faculdade_id', $faculdadeId)->first();
        if (!$configLogo) {
            return ['success' => false, 'error' => 'Configurações de logo da instituição de ensino não encontradas.'];
        }
        
        try {
            DB::beginTransaction();
            
            $certificado = $this->inserirCertificado($idCurso, $idUsuario);
            if (!$certificado) {
                throw new \Exception('Não foi possível salvar o certificado.');
            }
            
            $params = [
                'usuario' => $usuario,
                'aluno' => $aluno,
                'curso' => $curso,			
                'url_qrcode' => $this->generateQrcodeUrl($certificado->id),
                'data' => $data,
                'code' => base64_encode( 'educaz-'.$certificado->id ),
                'cursoTipo' => $cursoTipo,
                'cor' => $configFaculdade->cor_principal,
                'urlLogo' => $configLogo->url_logtipo, 
                'nome_professor' => $nomeProfessor,
            ];

            $response = $this->gerarPdfCertificado($params, $certificado);
            if(!$response['success']){                       
                DB::rollback();
                return $response;
            }

            DB::commit();
            $this->concluirGeracaoCertificado($usuario, $curso, $aluno, $certificado);

            return [
                'success' => true,
                'faculdade' => $faculdadeId,
                'curso' => Curso::obter($idCurso, $faculdadeId)
            ];            
        } catch (\Exception $e) {            
            DB::rollback();
            return ['success' => false, 'error' => 'Erro na inserção de registro no banco de dados.'];
        }        
    }

    public function emiteCertificadoPersonalizado($idUsuario, $idCurso, $idCertificado){         
        $resposta['success'] = false;

        $usuario = Usuario::find($idUsuario);
        $curso = Curso::find($idCurso);
        $faculdadeId = $usuario->fk_faculdade_id;
        
        $nomeProfessor = $this->getNomeCompletoProfessor($curso->id, $faculdadeId);

        $aluno = Aluno::where('fk_usuario_id', $usuario->id)->first();
        if (!$aluno) {
            return ['success' => false, 'error' => 'Aluno não encontrado.'];
        }   
            
        $certificadoLayout = CertificadoLayout::findOrFail($idCertificado);        
        if (!$certificadoLayout) {
            return ['success' => false, 'error' => 'Layout de certificado não encontrado'];
        }
        
        $faculdade = Faculdade::select('url', 'fantasia')->find($faculdadeId);

        $this->setDefaultTimezone();
        $data = $this->formatarDataCertificado();
        
        $duracao_total = $curso->duracao_total;
        $parts = explode(':', $duracao_total);
        $duracao_em_horas = (intval($parts[1]) > 0 || intval($parts[2]) > 0)? intval($parts[0]+1) : intval($parts[0]);

        $cursoTipo = $this->getCursoTipo($curso);

        try {
            DB::beginTransaction();
            
            $certificado = $this->inserirCertificado($idCurso, $idUsuario);
            if (!$certificado) {
                throw new \Exception('Não foi possível salvar o certificado.');
            }
            
            $params = [
                'usuario' => $usuario,
                'aluno' => $aluno,
                'certificado' => $certificadoLayout,
                'curso' => $curso,			
                'url_qrcode' => $this->generateQrcodeUrl($certificado->id),
                'data' => $data,
                'certificadoLayout' => $certificadoLayout,
                'code' => base64_encode( 'educaz-'.$certificado->id ),
                'identidade' => $aluno->identidade,
                'cursoTipo' => $cursoTipo,
                'duracao_em_horas' => $duracao_em_horas,
                'faculdadeFantasia' => $faculdade->fantasia, 
                'nome_professor' => $nomeProfessor
            ];

            $response = $this->gerarPdfCertificado($params, $certificado, 'certificados.emitecertificado');
            if(!$response['success']) {
                DB::rollback();
                return $response;
            }
            
            DB::commit();

            $this->concluirGeracaoCertificado($usuario, $curso, $aluno, $certificado);
            
            return [
                'success' => true,
                'faculdade' => $faculdadeId,
                'curso' => Curso::obter($idCurso, $faculdadeId)
            ];            
        } catch (\Exception $e) {            
            DB::rollback();
            return ['success' => false, 'error' => 'Erro na inserção de registro no banco de dados.'];
        }
    }

    /**
     * Método que conclui o curso e envia para a kroton que o certificado do aluno
     * Verificar issue ED2-1184 e ED2-1367 para encontrar o documento específico que explica o procedimento complemento
     * @param Usuario $usuario
     * @param Curso $curso
     * @param Aluno $aluno
     * @param Certificado $certificado
     * @throws \Exception
     */
    public function concluiCursoKroton(Usuario $usuario, Curso $curso, Aluno $aluno, Certificado $certificado) {
        // Implementar aqui código de finalização do curso na kroton;
        $pedido_item = PedidoItem::select('pedidos_item.*', 
            'pedidos.pid', 
            'pedidos.criacao as datainiciocurso', 
            'pedidos.data_compra_externa as datacompra')
            ->join('pedidos', 'pedidos.id', '=','pedidos_item.fk_pedido')
            ->where('pedido_item.fk_curso', $curso->id)
            ->where('pedidos.fk_usuario', $usuario->id)
            ->first();

        $headers = [
            'x-vtex-api-appkey' => 'vtexappkey-educaz-ADQTNW',
            'x-vtex-api-apptoken' => 'XKEPDVBLBSBRENXPILJPYPCGLLMFXCRMUWLODZVKTITQVSOXPEXVWCGIIYDSJVOYBOWHCJMJQYWQVLKTXPQTFVPSPCGEVDBXTVPHVGUGROFYGPNXOKUTEVSGZERZDHNN'
        ];

        $client = new Client([
            'headers' => $headers
        ]);

        $carga_horaria = 0;
        $response = $client->request('GET', 'https://educaz.vtexcommercestable.com.br/api/catalog_system/pvt/products/'. $pedido_item['fk_produto_externo_id'] .'/specification');
        if ($response->getStatusCode() == 200) {
            $carga_horaria = explode(' ', $response[4]['Value'][0]);
        }
        $dadosEnvio = [
            'cpfaluno' => $aluno->cpf,
            'nomealuno' => $usuario->nome,
            'orderId' => $pedido_item['pid'],
            'datacompra' => $pedido_item['datacompra'], // alterar para buscar a data certa
            'datainiciocurso' => $pedido_item['datainiciocurso'],
            'dataconclusaocurso' => new DateTime(),
            'horascredito' => $carga_horaria,
            'seller' => 'educaz',
            'idcursoseller' => $pedido_item['fk_produto_externo_id'],
            'certificado' => Url('/') . '/files/certificado/emitidos/' . $certificado->downloadPath,
            'nomecursocatalogovtex' => $curso->titulo,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'appKey' => '3duc4z-educaz',
            'appToken' => 'J6XTyur50ajCQBLYKJVV8YnhY5juJw2m'
        ];

        $client = new Client([
            'headers' => $headers
        ]);

        $response = $client->request('POST', 'http://api-kroton.yami.com.br/api/v1/aprovacao', ['body' => $dadosEnvio]);
        if ($response->getStatusCode() == 200) {
            return;
        }
        
        throw new \Exception('Ocorreu um erro ao concluir o curso junto a kroton para o aluno: ' . $usuario->nome);
    }

    public function generateQrcodeUrl($id){		
		$id_encoding = base64_encode( 'educaz-'.$id );
		$url 		 = url('autentica-certificado/'.$id_encoding);
		return $url;
    }
    
    public function enviaCertificadoPorEmail($idCertificado) {
        /** @var Certificado $certificado */
        $certificado = Certificado::find($idCertificado);
        
        $configLogo = ConfiguracoesLogotipos::where('fk_faculdade_id', $certificado->usuario->faculdade->id)->first();
        $EducazMail = new EducazMail($certificado->usuario->faculdade->id);
    
        return $EducazMail->certificado([
            'messageData' => [
                'nome' => $certificado->usuario->nome,
                'email' => $certificado->usuario->email,
                'nomeCurso' => $certificado->estrutura()->exists() ? $certificado->estrutura->titulo : $certificado->curso->titulo,
                'nomeProfessor' => $certificado->curso()->exists() ? $certificado->curso->professor->nomecompleto : null,
                'downloadPath' => $certificado->path(),
                'linkCertificados' => $certificado->usuario->faculdade->path(),
                'urlLogo' => url('/files/logotipos/'.$configLogo->url_logtipo),
                'urlFaculdade' => $certificado->usuario->faculdade->url,
                'faculdadeFantasia' => $certificado->usuario->faculdade->fantasia
            ]
        ]);
    }

    /**
     * @param $idCurso
     * @return mixed
     */
    private function verificaSeCursoExiste($idCurso) {
        try {
            return Curso::findOrFail($idCurso);
        } catch (\Exception $error) {
            return ['success' => false, 'error' => 'Curso não existe'];
        }
    }

    /**
     * @param $idCurso
     * @param $faculdade_id
     * @return mixed
     */
    public function verificaCriteriosParaConclusaoCursoFaculdade($idCurso, $faculdade_id) {
        return ConclusaoCursosFaculdades::where('fk_curso', $idCurso)
                ->where('fk_faculdade', $faculdade_id)
                ->first();
    }

    /**
     * @param array $curso
     * @return bool
     */
    private function eNecessarioVerificarPercentualDeModulosAssistidos(Curso $curso): bool {
        return $curso->fk_cursos_tipo == Curso::ONLINE || $curso->fk_cursos_tipo == Curso::REMOTO;
    }

    /**
     * @param $idUsuario
     * @return mixed
     */
    private function verificaSeUsuarioExiste($idUsuario) {
        try {
            return Usuario::findOrFail($idUsuario);
        } catch (\Exception $error) {
            return ['success' => false, 'error' => 'Usuário não existe'];
        }
    }

    private function setDefaultTimezone(): void
    {
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');
    }

    /**
     * @return string
     */
    public function formatarDataCertificado(): string {
        return strftime('%d de %B de %Y às %H:%M', strtotime((new \DateTime())->format('Y-m-d H:i')));
    }

    /**
     * @param $idCurso
     * @param $faculdadeId
     * @return string
     */
    private function getNomeCompletoProfessor($idCurso, $faculdadeId = 7): string {
        
        $infoCurso = Curso::obter($idCurso, $faculdadeId);
        
        if (!empty($infoCurso)) {
            return $infoCurso['nome_professor'] . ' ' . $infoCurso['sobrenome_professor'];
        }
        
        return '';
    }

    /**
     * @param $curso
     * @return string
     */
    private function getCursoTipo($curso): string {
        $cursoTipo = CursoTipo::find($curso->fk_cursos_tipo)->titulo;
        if ($cursoTipo != 'Evento') {
            return 'Curso ' . $cursoTipo;
        }
        
        return '';
    }

    /**
     * @param $faculdadeId
     * @return mixed
     */
    private function getConfiguracoesEstilosFaculdade($faculdadeId) {
        $configFaculdade = ConfiguracoesEstilos::select('configuracoes_estilos_variaveis.value AS cor_principal')
            ->join('configuracoes_estilos_variaveis', 'configuracoes_estilos.id', '=',
                'configuracoes_estilos_variaveis.fk_configuracoes_estilos_id')
            ->join('configuracoes_variaveis', 'configuracoes_variaveis.id', '=',
                'configuracoes_estilos_variaveis.fk_configuracoes_variaveis_id')
            ->where('fk_faculdade_id', $faculdadeId)
            ->where('configuracoes_variaveis.nome', 'defaultColorTheme')->first();
        return $configFaculdade;
    }

    /**
     * @param $idCurso
     * @param $idUsuario
     * @return mixed
     */
    private function inserirCertificado($idCurso = null, $idUsuario, $idEstrutura = null)  {
        return Certificado::create([
            'fk_curso' => $idCurso,
            'fk_estrutura' => $idEstrutura,
            'data_conclusao' => date('Y-m-d H:i:s'),
            'fk_usuario' => $idUsuario,
            'fk_criador_id' => 1,
            'fk_atualizador_id' => 1,
            'data_criacao' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s'),
            'criacao' => date('Y-m-d H:i:s'),
            'atualizacao' => date('Y-m-d H:i:s'),
            'status' => 1
        ]);
    }

    /**
     * @param array $params
     * @param $certificado
     * @param string $type
     * @return array
     */
    private function gerarPdfCertificado(array $params, $certificado, $type = 'certificados.emitecertificadogenerico'): array {
        $pdf = PDF::setOptions([
                    'images' => true,
                ])->loadView($type, $params)->setPaper('a4', 'landscape');
        $pdf_output = $pdf->output();
        
        $fileName = date('YmdHis') . '_' . $certificado->id . '.pdf';
        if (!file_put_contents(public_path().'/files/certificado/emitidos/'.$fileName, $pdf_output)) {
            return ['success' => false, 'error' => 'Não foi possível fazer upload do certificado para o servidor.'];
        }

        $certificado->downloadPath = $fileName;
        $certificado->save();
        
        return ['success' => true];
    }

    /**
     * @param $usuario
     * @param $curso
     * @param $aluno
     * @param $certificado
     * @throws \Exception
     */
    private function concluirGeracaoCertificado($usuario, $curso, $aluno, $certificado): void
    {
        if ($usuario->aluno_kroton) {
            $this->concluiCursoKroton($usuario, $curso, $aluno, $certificado);
        } else {
            $this->enviaCertificadoPorEmail($certificado->id);
        }
    }

    public function estruturaCursoPossuiCertificado($usuario, $idCurso) {
        /** @var Illuminate\Support\Collection $cursos */
        $estruturas = (new ItvService())->retornarCursosAlunoItv($usuario);

        $estruturasFiltradas = collect($estruturas)->filter(function ($estrutura) use ($idCurso) {
            /** @var \Illuminate\Support\Collection $filter */
            $filter =  $estrutura['categorias']->filter(function ($categoria) use ($idCurso){
                return collect($categoria['cursos'])->contains('id', $idCurso);
            });
            
            return $filter->isNotEmpty() && !empty($estrutura['fk_certificado_layout']);
        });
        
        return $estruturasFiltradas->isNotEmpty();
    }

    /**
     * @param $usuario
     * @return array
     */
    private function retornarEstruturasAptasCertificacao($usuario): array {
        /** @var Illuminate\Support\Collection $cursos */
        $estruturas = (new ItvService())->retornarCursosAlunoItv($usuario);
        
        $estruturasCertificadoAptas = [];
        
        foreach ($estruturas as $estrutura) {
            $estrutura = $estrutura->all();
            if (empty($estrutura['fk_certificado_layout'])) {
                continue;
            }

            $certificado = $this->getCertificadoEstrutura($usuario->getId(), $estrutura['id']);
            
            if($certificado) {
                continue;
            }
            
            $disponibilidadeEstrutura = true;
            foreach ($estrutura['categorias'] as $categoria) {
                $categoria = $categoria->all();
                foreach ($categoria['cursos'] as $curso) {
                    $disponibilidade = $this->disponibilidadecertificado($usuario, $curso['id']);
                    if (!$disponibilidade['success']) {
                        $disponibilidadeEstrutura = false;
                    }
                }
            }
            if ($disponibilidadeEstrutura) {
                $estruturasCertificadoAptas[] = $estrutura;
            }
        }
        
        return $estruturasCertificadoAptas;
    }
}
