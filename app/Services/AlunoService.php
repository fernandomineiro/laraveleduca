<?php

namespace App\Services;

use App\Repositories\AlunoRepository;
use App\UsuariosPerfil;
use Illuminate\Database\DatabaseManager;
use Yajra\DataTables\Facades\DataTables;

class AlunoService extends UsuarioServiceAbstract {

    /** @var AlunoRepository  */
    protected $alunoRepository;
    /** @var DatabaseManager  */
    protected $databaseManeger;
    
    public function __construct(AlunoRepository $alunoRepository, DatabaseManager $databaseManager) {
        
        $this->alunoRepository = $alunoRepository;
        $this->databaseManeger = $databaseManager;
    }

    public function listAlunosDataTable() {
        return Datatables::of($this->alunoRepository->listarVwAlunos())
            ->editColumn('usuario_ativo', function ($model) {
                if($model->usuario_ativo === 1) {
                    return 'ATIVO';
                } else {
                    return 'DESATIVADO';
                }
            })
            ->editColumn('nome', function ($model) {
                return $model->nome . ' ' . $model->sobre_nome;
            })
            ->make(true);
    }

    public function getAluno($id) {
        return $this->alunoRepository->load($id);
    }
    
    public function salvar(array $data)  {
        try {
            $this->databaseManeger->commit();
            
            $this->setIdFaculade($data['fk_faculdade_id']);
            $data['fk_perfil'] = UsuariosPerfil::ALUNO;
            
            $data['sobrenome'] = $data['sobre_nome'];
            $usuario = $this->criarUsuario($data);
            $endereco = $this->salvarEndereco($data);
            
            $data['fk_usuario_id'] = $usuario->id;
            $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;
            
            $this->alunoRepository->save($data);
            $this->databaseManeger->commit();
            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro inserido com sucesso!',
                'validatorMessage' => null
            ];
        } catch (\Exception $exception) {
            $this->databaseManeger->rollBack();
            return [
                'code' => -1, 
                'type' => 'mensagem_erro', 
                'message' => 'Não foi possível inserir o registro! ' . $exception->getMessage(), 
                'validatorMessage' => 'Validator'
            ];
        }
    }

    public function atualizar(array $data) {
        
        try {

            $this->databaseManeger->beginTransaction();

            $data['fk_perfil'] = UsuariosPerfil::ALUNO;
            if (!empty($data['data_nascimento'])) {
                $data['data_nascimento'] = $this->prepararData($data['data_nascimento']);
            }
            $data['sobrenome'] = $data['sobre_nome'];
            $this->alunoRepository->load($data['id']);
            $this->atualizarUsuario($data);
            
            $endereco = $this->salvarEndereco($data);
            $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;

            $this->alunoRepository->save($data);
            $this->databaseManeger->commit();
            
            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro atualizado com sucesso!',
                'validatorMessage' => null
            ];

        } catch (\Exception $exception) {
            $this->databaseManeger->rollBack();
            $validatorMsg = 'Não foi possível atualizar o registro! ' . $exception->getMessage();
         
            return ['code' => -1, 'type' => 'mensagem_erro', 'message' => $validatorMsg, 'validatorMessage' => $validatorMsg];
        }
    }
}
