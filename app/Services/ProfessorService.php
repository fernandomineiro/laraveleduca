<?php

namespace App\Services;

use App\Helper\WirecardHelper;
use App\Professor;
use App\Repositories\ProfessorRepository;
use App\UsuariosPerfil;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Yajra\DataTables\Facades\DataTables;

class ProfessorService extends UsuarioServiceAbstract { 

    /** @var ProfessorRepository  */
    protected $professorRepository;
    /** @var DatabaseManager  */
    protected $databaseManeger;
    
    public function __construct(ProfessorRepository $professorRepository, DatabaseManager $databaseManager) {
        $this->professorRepository = $professorRepository;
        $this->databaseManeger = $databaseManager;
    }

    public function listaProfessoresDataTable() {
        return DataTables::of($this->professorRepository->listarVwProfessores())
            ->editColumn('usuario_ativo', function ($model) {
                if($model->usuario_ativo === 1) {
                    return 'ATIVO';
                }
                
                return 'DESATIVADO';
            })->editColumn('status', function ($model) {
                if($model->usuario_ativo === 1) {
                    return 'ATIVO';
                }
                
                return 'EM AVALIAÇÃO';
            })->editColumn('nome', function ($model) {
                return $model->nome . ' ' . $model->sobrenome;
            })->editColumn('registro', function ($model) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $model->registro)->format('d/m/Y H:i:s');
            })->make(true);
    }

    public function getProfessor($idProfessor) {
        return $this->professorRepository->load($idProfessor);
    }

    public function isRepasseManual($wireCardAccountId) {
        return empty($wireCardAccountId) ? null : 1;
    }

    public function atualizar(array $data) {
        try {
            $this->databaseManeger->beginTransaction();
            $professor = $this->professorRepository->load($data['id']);
            
            $data['fk_perfil'] = UsuariosPerfil::PROFESSOR;
            $data = $this->prepararDadosProfessor($data, $professor);
            if ($this->hasError($data)) {
                return $data;
            }
            
            $this->atualizarUsuario($data);
            if ($this->hasError($data)) {
                return $data;
            }
            
            $this->professorRepository->save($data);
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
            return [
                'code' => -1, 
                'type' => 'mensagem_erro', 
                'message' => $validatorMsg, 
                'validatorMessage' => $validatorMsg
            ];
        }
    }

    public function salvar(array $data) {

        try {
            $this->databaseManeger->beginTransaction();
            
            $data['fk_perfil'] = UsuariosPerfil::PROFESSOR;
            
            $usuario = $this->criarUsuario($data);
            $data['fk_usuario_id'] = $usuario->id;
            
            $data = $this->prepararDadosProfessor($data);
            $this->atualizarUsuario($data);
            if ($this->hasError($data)) {
                return $data;
            }

            $this->professorRepository->save($data);
            $this->databaseManeger->commit();

            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro inserido com sucesso!',
                'validatorMessage' => null
            ];
        } catch (\Exception $exception) {
            $this->databaseManeger->rollBack();
            $validatorMsg = 'Não foi possível atualizar o registro! ' . $exception->getMessage();
            
            return [
                'code' => -1,
                'type' => 'mensagem_erro',
                'message' => $validatorMsg,
                'validatorMessage' => $validatorMsg
            ];
        }
    }

    /**
     * @param array $data
     * @param Professor|null $professor
     * @return array|bool
     */
    private function prepararDadosProfessor(array $data, Professor $professor = null) {
        $data['fk_perfil'] = UsuariosPerfil::PROFESSOR;
        $data['cpf'] = $this->tratarCpf($data['cpf']);
        $data['data_nascimento'] = $this->prepararData($data['data_nascimento']);
        
        $wirecardAccount = $this->criarWirecardAccount($data, $professor);
        
        if (!empty($wirecardAccount['type']) && $wirecardAccount['type'] == 'mensagem_erro') {
            return $wirecardAccount;
        } else if (!empty($wirecardAccount['data']['wirecard_account_id'])) {
            $data['wirecard_account_id'] = $wirecardAccount['data']['wirecard_account_id'];
        }

        $endereco = $this->salvarEndereco($data);
        $data['fk_endereco_id'] = !empty($endereco->id) ? $endereco->id : null;

        $contaBancaria = $this->salvarContaBancaria($data);
        $data['fk_conta_bancaria_id'] = !empty($contaBancaria->id) ? $contaBancaria->id : null;
        
        return $data;
    }
    
    /**
     * @param string $cpf
     * @return string
     */
    private function tratarCpf($cpf = null): ?string {
        if (!empty($cpf)) {
            $cpf = $this->soNumero($cpf);
        }
        
        return $cpf;
    }

    /**
     * @param $data
     * @return bool
     */
    private function hasError($data): bool {
        return !empty($data['type']) && $data['type'] == 'mensagem_erro';
    }
}
