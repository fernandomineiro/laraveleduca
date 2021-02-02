<?php


namespace App\Services;

use App\Helper\WirecardHelper;
use App\Professor;
use App\Repositories\CidadeRepository;
use App\Repositories\EstadoRepository;
use App\UsuariosPerfil;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class UsuarioServiceAbstract {
    
    public $file;
    protected $filePath = 'files/usuario/';
    protected $disk = 's3';
    protected $idFaculade = null;
    
    const QUANTIDADEMINTELEFONE = 1;
    const QUANTIDADEMAXTELEFONE = 3;
    
    abstract public function atualizar(array $data);
    abstract public function salvar(array $data);

    /**
     * @param mixed $file
     */
    public function setFile($file): void {
        $this->file = $file;
    }

    /**
     * @param null $idFaculade
     */
    public function setIdFaculade($idFaculade): void {
        $this->idFaculade = $idFaculade;
    }

    public function hasFile() {
        return !empty($this->file) && $this->file instanceof UploadedFile;
    }

    /**
     * @return string|null
     */
    public function uploadUserPhoto() {
        if ($this->hasFile() && $this->file->isValid()) {
            $fileName = date('YmdHis') . '_' . $this->file->getClientOriginalName();

            Storage::disk($this->disk)
                ->put(
                    $this->filePath . $fileName, 
                    file_get_contents($this->file), 
                    'public'
                );

            return $fileName;
        }

        return 'default.png';
    }

    /**
     * @param string $foto
     */
    public function deletarFotoUsuario(string $foto) {
        if (Storage::disk($this->disk)->exists($this->filePath . $foto)) {
            Storage::disk($this->disk)->delete($this->filePath . $foto);
        }
    }

    public function preparTelefones(array $data) {
        $telefones = [];
        for ($qtdTelefones = self::QUANTIDADEMINTELEFONE; $qtdTelefones <= self::QUANTIDADEMAXTELEFONE; $qtdTelefones++) {
            if (!empty($data['telefone_' . $qtdTelefones])) {
                $data['telefone_' . $qtdTelefones] = str_replace('_', '', $data['telefone_' . $qtdTelefones]);
            }
        }
        
        return $telefones;
    }

    public function criarUsuario(array $data) {
        /** @var UsuarioService $usuarioService */
        $usuarioService = app()->make(UsuarioService::class);
        $usuarioService->setFile($this->file);
        $usuarioService->setIdFaculade($this->idFaculade);
        
        return $usuarioService->salvar($this->getUsuarioData($data));
    }

    public function atualizarUsuario(array $data) {
        /** @var UsuarioService $usuarioService */
        $usuarioService = app()->make(UsuarioService::class);
        $usuarioService->setFile($this->file);
        $usuarioService->setIdFaculade($this->idFaculade);
        $usuarioService->atualizar($this->getUsuarioData($data));
    }

    public function salvarEndereco(array $data) {
        /** @var EnderecoService $enderecoService */
        $enderecoService = app()->make(EnderecoService::class);

        $data = array_filter($this->getEnderecoData($data));
        if (empty($data)) {
            return false;
        }

        return $enderecoService->salvar($data);
    }
    
    public function salvarContaBancaria(array $data) {
        /** @var ContaBancariaService $cantaBancariaService */
        $contaBancariaService = app()->make(ContaBancariaService::class);

        $data = array_filter($this->getContaBancariaData($data));
        if (empty($data)) {
            return false;
        }

        return $contaBancariaService->salvar($data);
    }

    /**
     * @param string $data
     * @return string
     */
    public function prepararData(string $data = null) {
        $datetime = \DateTime::createFromFormat('d/m/Y', $data);
        
        if (empty($datetime)) {
            $datetime = date_create($data);
        } 
        
        return !empty($datetime) ? $datetime->format('Y-m-d') : null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getUsuarioData(array $data): array {
        $nome = $data['nome'];
        $sobre_nome = !empty($data['sobrenome']) ? $data['sobrenome'] : $data['sobre_nome'];
        return array_merge(
            [
                'id' => !empty($data['fk_usuario_id']) ? $data['fk_usuario_id'] : null,
                'fk_faculdade_id' => !empty($data['fk_faculdade_id']) ? $data['fk_faculdade_id'] : null,
                'nome' => "$nome $sobre_nome",
                'email' => $data['email'],
                'password' => !empty($data['password']) ? $data['password'] : null,
                'fk_perfil' => $data['fk_perfil'],
            ],
            $this->preparTelefones($data)
        );
    }

    /**
     * @param array $data
     * @return array
     */
    private function getEnderecoData(array $data): array
    {
        return [
            'id' => !empty($data['fk_endereco_id']) ? $data['fk_endereco_id'] : null,
            'cep' => $data['cep'],
            'logradouro' => $data['logradouro'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'],
            'bairro' => $data['bairro'],
            'fk_estado_id' => $data['fk_estado_id'],
            'fk_cidade_id' => !empty($data['fk_cidade_id']) ? $data['fk_cidade_id'] : null,
        ];
    }
    
    /**
     * @param array $data
     * @return array
     */
    private function getContaBancariaData(array $data): array {
        return [
            'id' => !empty($data['fk_conta_bancaria_id']) ? $data['fk_conta_bancaria_id'] : null,
            'titular' => !empty($data['titular']) ? $data['titular'] : null,
            'documento' => !empty($data['documento']) ? $data['documento'] : null,
            'fk_banco_id' => !empty($data['fk_banco_id']) ? $data['fk_banco_id'] : null,
            'agencia' => !empty($data['agencia']) ? $data['agencia'] : null,
            'digita_agencia' => !empty($data['digita_agencia']) ? $data['digita_agencia'] : null,
            'conta_corrente' => !empty($data['conta_corrente']) ? $data['conta_corrente'] : null,
            'digita_conta' => !empty($data['digita_conta']) ? $data['digita_conta'] : null,
            'operacao' => !empty($data['operacao']) ? $data['operacao'] : null,
            'tipo_conta' => !empty($data['tipo_conta']) ? $data['tipo_conta'] : null,
        ];
    }

    /**
     * Transforma somente em números
     * @param $str
     * @return string|string[]|null
     */
    public function soNumero($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    public function criarWirecardAccount($data, Professor $professor = null) {
        if ($this->isToCreateWirecardAccount($data, $professor)) {
            
            $data_account_wirecard = $this->prepareDadosContaWirecard($data);
            
            $wirecard = new WirecardHelper();
            $createAccount = $wirecard->createAccount($data_account_wirecard, 'professor');

            if ($this->isAccountCreatedSuccessfully($createAccount)) {
                $validatorMsg = 'Não foi possível criar a conta na wirecard!';
                return [
                    'code' => -1,
                    'type' => 'mensagem_erro',
                    'message' => $validatorMsg,
                    'validatorMessage' => $validatorMsg,
                    'errors' => $createAccount['error']
                ];
            }

            return [
                'code' => 1,
                'type' => 'mensagem_sucesso',
                'message' => 'Registro atualizado com sucesso!',
                'validatorMessage' => null,
                'data' => $createAccount
            ];
        }
        
        return true;
    }

    public function prepareDadosContaWirecard($data) {
        $data_account = [];

        $estado = app()->make(EstadoRepository::class)->load($data['fk_estado_id']);
        $cidade = app()->make(CidadeRepository::class)->load($data['fk_cidade_id']);
        
        $data_account['address'] = [
            'street' => (!empty($data['logradouro'])) ? $data['logradouro'] : '',
            'number' => (!empty($data['numero'])) ? $data['numero'] : '',
            'district' => (!empty($data['bairro'])) ? $data['bairro'] : '',
            'zipcode' => $this->soNumero($data['cep']),
            'city' => (!empty($cidade->descricao_cidade)) ? $cidade->descricao_cidade : '',
            'state' => (!empty($estado->uf_estado)) ? $estado->uf_estado : '',
            'country' => 'BRA'
        ];

        $data_account['email'] = $data['email'];
        $data_account['name'] = $data['nome'];
        $data_account['lastname'] = $data['sobrenome'];
        $data_account['birth_data'] = $this->prepararData($data['data_nascimento']);
        $data_account['cpf'] = $this->soNumero($data['cpf']);

        $phone_number = $this->getPhone($data);

        if ($phone_number) {
            $data_account['phone'] = [
                'ddd' => $phone_number['ddd'],
                'number' => $phone_number['number'],
                'prefix' => '55'
            ];
        }
        
        return $data_account;
    }

    private function getPhone($customer) {
        if (!empty($customer['telefone_1'])) {
            $phoneNumber = $customer['telefone_1'];
        } elseif (!empty($customer['telefone_2'])) {
            $phoneNumber = $customer['telefone_2'];
        } elseif (!empty($customer['telefone_3'])) {
            $phoneNumber = $customer['telefone_3'];
        }

        if (empty($phoneNumber)) {
            return false;
        }

        $phone = $this->soNumero($phoneNumber);
        $data['ddd'] = substr($phone, 0, 2);
        $data['number'] = substr($phone, 2, 9);

        return $data;
    }

    /**
     * @param $createAccount
     * @return bool
     */
    private function isAccountCreatedSuccessfully($createAccount): bool {
        return empty($createAccount['success']) || empty($createAccount['wirecard_account_id']);
    }

    /**
     * @param array $data
     * @param $professor
     * @return bool
     */
    private function isToCreateWirecardAccount(array $data, Professor $professor = null): bool {
        return empty($data['repasse_manual']) && empty($professor->wirecard_account_id);
    }
}
