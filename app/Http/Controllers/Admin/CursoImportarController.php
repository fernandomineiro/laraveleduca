<?php

namespace App\Http\Controllers\Admin;

use App\Curso;
use App\CursoCategoriaCurso;
use App\CursoTag;
use App\CursoValor;
use App\Http\Controllers\Controller;
use App\Imports\CursoImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\CursoSecao;
use App\CursoModulo;
use App\Quiz;
use App\QuizResposta;
use App\QuizQuestao;

class CursoImportarController extends Controller{
    
    private $ids_controle = [];
    
    public function index(){
        if (!$this->validateAccess(Session::get('user.logged'))) return redirect()->route($this->redirecTo);
        
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }
    
    public function salvar(Request $request){
        
        if (!$this->validateAccess(Session::get('user.logged'), false)) return redirect()->route($this->redirecTo);
        
        $request->validate([
            'arquivo_excel' => 'required'
        ]);
        
        try {
            
            DB::beginTransaction();
            
            $allCollections = Excel::toArray(new CursoImport(),$request->file('arquivo_excel'));
            
            // processar cursos
            foreach ($allCollections['cursos'] as $row){
                
                $curso =  Curso::create([
                    'titulo'  => $row['titulo'],
                    'descricao'  => $row['descricao'],
                    'objetivo_descricao'  => $row['objetivo_descricao'],
                    'publico_alvo'  => $row['publico_alvo'],
                    'teaser'  => $row['teaser'],
                    'fk_cursos_tipo'  => $row['fk_cursos_tipo'],
                    'fk_faculdade'  => $row['fk_faculdade'],
                    'data_criacao'  => date('Y-m-d H:i:s'),
                    'status'  => $row['status'],
                    'endereco_presencial'  => $row['endereco_presencial'],
                    'numero_maximo_alunos'  => $row['numero_maximo_alunos'],
                    'numero_minimo_alunos'  => $row['numero_minimo_alunos'],
                    'fk_professor'  => $row['fk_professor'],
                    'fk_professor_participante'  => $row['fk_professor_participante'],
                    'fk_curador'  => $row['fk_curador'],
                    'fk_conteudista'  => $row['fk_conteudista'],
                    'fk_produtora'  => $row['fk_produtora'],
                    'fk_parceiro'  => $row['fk_parceiro'],
                    'idioma'  => $row['idioma'],
                    'formato'  => $row['formato'],
                    'fk_certificado'  => $row['fk_certificado'],
                ]);
                
                $this->ids_controle['id_curso'][$row['id']] = $curso->id;
                
            }
            
            // processar valores de cursos
            foreach ($allCollections['cursos_valor'] as $row){
                
                CursoValor::create([
                    'fk_curso'  => $this->ids_controle['id_curso'][$row['id_curso']],
                    'valor_de'  => $row['valor_de'],
                    'valor'  => $row['valor'],
                    'data_inicio'  => !is_null($row['data_inicio']) ? Carbon::createFromFormat('d/m/Y', $row['data_inicio'])->format('Y-m-d') : null,
                    'data_validade'  => !is_null($row['data_validade']) ? Carbon::createFromFormat('d/m/Y', $row['data_validade'])->format('Y-m-d') : null,
                    'fk_criador_id'  => Session::get('user.logged')->id,
                    'fk_atualizador_id'  => Session::get('user.logged')->id,
                    'data_criacao'  => date('Y-m-d H:i:s'),
                    'criacao'  => date('Y-m-d H:i:s'),
                    'atualizacao'  => date('Y-m-d H:i:s'),
                    'status'  => $row['status']
                ]);
                
            }
            
            // processar tags de cursos
            foreach ($allCollections['cursos_tag'] as $row){
                
                CursoTag::create([
                    'fk_curso'  => $this->ids_controle['id_curso'][$row['id_curso']],
                    'tag'       => $row['tag']
                ]);
                
            }
            
            // processar categorias de cursos
            foreach ($allCollections['cursos_categoria'] as $row){
                
                CursoCategoriaCurso::create([
                    'fk_curso'              => $this->ids_controle['id_curso'][$row['id_curso']],
                    'fk_curso_categoria'    => $row['fk_curso_categoria']
                ]);
                
            }
            
            // processar secoes de cursos
            foreach ($allCollections['cursos_secao'] as $row){
                
                $cursoSecao = CursoSecao::create([
                    'fk_curso'  => $this->ids_controle['id_curso'][$row['id_curso']],
                    'titulo'    => $row['titulo'],
                    'ordem'     => $row['ordem'],
                    'status'    => $row['status']
                ]);
                
                $this->ids_controle['id_secao'][$row['id']] = $cursoSecao->id;
                
            }
            
            // processar módulos de cursos
            foreach ($allCollections['cursos_modulo'] as $row){
                
                CursoModulo::create([
                    'fk_curso'  => $this->ids_controle['id_curso'][$row['id_curso']],
                    'fk_curso_secao'  => $this->ids_controle['id_secao'][$row['id_secao']],
                    'titulo'    => $row['titulo'],
                    'ordem'     => $row['ordem'],
                    'status'    => $row['status'],
                    'url_arquivo'    => $row['url_arquivo'],
                    'url_video'    => $row['url_video'],
                    'carga_horaria'    => $row['carga_horaria'],
                    'tipo_modulo'    => $row['tipo_modulo']
                ]);
                
            }
            
            // processar quiz de cursos
            foreach ($allCollections['cursos_quiz'] as $row){
                
                $cursoQuiz = Quiz::create([
                    'fk_curso'  => $this->ids_controle['id_curso'][$row['id_curso']],
                    'percentual_acerto'    => $row['percentual_acerto'],
                ]);
                
                $this->ids_controle['id_quiz'][$row['id']] = $cursoQuiz->id;
                
            }
            
            // processar questões de quiz de cursos
            foreach ($allCollections['cursos_quiz_questao'] as $row){
                
                $cursoQuizQuestao = QuizQuestao::create([
                    'fk_quiz'  => $this->ids_controle['id_quiz'][$row['id_quiz']],
                    'titulo'    => $row['titulo'],
                    'resposta_correta'    => $row['resposta_correta'],
                    'status'    => $row['status'],
                ]);
                
                $this->ids_controle['id_questao'][$row['id']] = $cursoQuizQuestao->id;
                
            }
            
            // processar respostas de questões de quiz de cursos
            foreach ($allCollections['cursos_quiz_resposta'] as $row){
                
                QuizResposta::create([
                    'fk_quiz_questao'  => $this->ids_controle['id_questao'][$row['id_questao']],
                    'label'    => $row['label'],
                    'descricao'    => $row['descricao'],
                ]);
                
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            
            DB::rollback();
            
            Session::flash('mensagem_erro', $this->msgInsertErro);
            return Redirect::back()->withErrors($e->getMessage())->withInput();

        }
        
        Session::flash('mensagem_sucesso', $this->msgInsert);
        return redirect()->route('admin.curso');
        
    }
    
    
}
