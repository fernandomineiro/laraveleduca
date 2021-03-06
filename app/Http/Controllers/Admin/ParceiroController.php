<?php

namespace App\Http\Controllers\Admin;

use App\Banco;
use App\Cidade;
use App\ContaBancaria;
use App\Endereco;
use App\Estado;
use App\Parceiro;
use App\Usuario;
use App\ViewUsuariosParceiros;
use App\Exports\ParceirosExport;
use App\Helper\WirecardHelper;
use App\Http\Controllers\PessoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ParceiroController extends PessoasController
{

    /**
     * Listar Faculdades
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objLst'] = ViewUsuariosParceiros::all();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * Formulário para incluir parceiro
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['tipoParceiro'] = 'parceiros';
        $cidades = Cidade::select('descricao_cidade', 'id')->get();
        $estados = Estado::select('descricao_estado', 'id')->get();

        $this->arrayViewData['repasse_manual'] = false;

        $cidades = $cidades->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_cidade'])];
        });
        $estados = $estados->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_estado'])];
        });
        $this->arrayViewData['estados'] = $estados->toArray();
        $this->arrayViewData['cidades'] = $cidades->toArray();
        $this->arrayViewData['lista_bancos'] = Banco::all()->where('status', 1)->pluck('titulo', 'id');

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * Editar Parceiro
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editar($id)
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['tipoParceiro'] = 'parceiros';
        $cidades = Cidade::select('descricao_cidade', 'id')->get();
        $estados = Estado::select('descricao_estado', 'id')->get();

        $cidades = $cidades->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_cidade'])];
        });
        $estados = $estados->mapWithKeys(function ($item) {
            return [$item['id'] => Str::title($item['descricao_estado'])];
        });
        $this->arrayViewData['estados'] = $estados->toArray();
        $this->arrayViewData['cidades'] = $cidades->toArray();
        $this->arrayViewData['lista_bancos'] = Banco::all()->where('status', 1)->pluck('titulo', 'id');

        $this->arrayViewData['objParceiro'] = Parceiro::select('*')->where('id', $id)->first();
        $this->arrayViewData['objEndereco'] = Endereco::select('endereco.*')
            ->join('parceiro', 'parceiro.fk_endereco_id', '=', 'endereco.id')
            ->where('parceiro.id', '=', $id)
            ->first();

        $this->arrayViewData['objUsuario'] = Usuario::select('usuarios.*', 'parceiro.wirecard_account_id')
            ->join('parceiro', 'parceiro.fk_usuario_id', '=', 'usuarios.id')
            ->where('parceiro.id', '=', $id)
            ->first();

        $this->arrayViewData['repasse_manual'] = ($this->arrayViewData['objUsuario']->wirecard_account_id > 0) ? null : 1;

        $this->arrayViewData['objConta'] = ContaBancaria::select('conta_bancaria.*')
            ->join('parceiro', 'parceiro.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
            ->where('parceiro.id', '=', $id)
            ->first();

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * Salvar Parceiro
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        $parceiro = new Parceiro();
        $validator = $parceiro->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            if (!empty($request->get('repasse_manual')) && $request->get('repasse_manual') == 1){
                return $this->_return($this->salvarRegistro($request, new Parceiro($request->all())),
                        '/admin/parceiro/index');
            } else {
                $data_account_wirecard = $this->prepareDadosContaWirecard($request->all());
    
                $wirecard = new WirecardHelper;
                $createAccount = $wirecard->createAccount($data_account_wirecard, 'parceiro');

                if (!empty($createAccount['success']) && !empty($createAccount['wirecard_account_id'])) {
                    $request->request->add(['wirecard_account_id' => $createAccount['wirecard_account_id']]);
    
                    return $this->_return($this->salvarRegistro($request, new Parceiro($request->all())),
                        '/admin/parceiro/index');
                } elseif (!empty($createAccount['error'])) {
                    return Redirect::back()->withErrors($createAccount['error'])->withInput();
                } else {
                    return Redirect::back()->withErrors('Não foi possível criar a conta na Wirecard!')->withInput();
                }
            }
        }
    }

    /**
     * Atualizar Parceiro
     *
     * @param integer $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function atualizar($id, Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        if (is_null($request->get('password')) && is_null($request->get('password_confirmation'))) {
            $request->replace($request->except(['password', 'password_confirmation']));
        }

        $parceiro = new Parceiro();
        $validator = $parceiro->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            if (empty($request->get('repasse_manual'))){
                $data_parceiro = Parceiro::where('id', $id)->select('wirecard_account_id')->first();

                if ($data_parceiro->wirecard_account_id == 0 || is_null($data_parceiro->wirecard_account_id)){
                    $data_account_wirecard = $this->prepareDadosContaWirecard($request->all());
        
                    $wirecard = new WirecardHelper;
                    $createAccount = $wirecard->createAccount($data_account_wirecard, 'professor');
        
                    if (!empty($createAccount['success']) && !empty($createAccount['wirecard_account_id'])) {
                        $request->request->add(['wirecard_account_id' => $createAccount['wirecard_account_id']]);
                    } elseif (!empty($createAccount['error'])) {
                        return Redirect::back()->withErrors($createAccount['error'])->withInput();
                    }
                }
            }

            return $this->_return($this->atualizarRegistro($request, Parceiro::findOrFail($id)),
                '/admin/parceiro/index');
        }
    }

    /**
     * Deletar Parceiro
     *
     * @param integer $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id, Request $request)
    {

        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return $this->_return($this->deletaRegistro(Parceiro::findOrFail($id)));
    }

    private function prepareDadosContaWirecard($data)
    {
        $data_account = array();

        $estado = Estado::select('uf_estado')->find($data['fk_estado_id']);
        $cidade = Cidade::select('descricao_cidade')->find($data['fk_cidade_id']);

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

        if (!empty($data['responsavel'])) {
            $name = explode(' ', $data['responsavel']);
            $data_account['name'] = (isset($name[0])) ? $name[0] : '';
            $data_account['lastname'] = end($name);
        }

        $data_explode = explode('/', $data['data_nascimento']);
        $data_nascimento = $data_explode[2] . '-' . $data_explode[1] . '-' . $data_explode[0];

        $data_account['birth_data'] = $data_nascimento;
        $data_account['cpf'] = preg_replace("/[^0-9]/", "", $data['cpf']);

        $phone_number = $this->getPhone($data);

        $data_account['phone'] = ['ddd' => $phone_number['ddd'], 'number' => $phone_number['number'], 'prefix' => '55'];

        $company_name = $data['fantasia'];

        if (!empty($company_name)) {
            $data_account['company'] = [
                'name' => $company_name,
                'business_name' => $data['razao_social'],
                'type_document' => 'CNPJ'
            ];
            $data_account['company']['address'] = $data_account['address'];
            $data_account['company']['cnpj'] = preg_replace("/[^0-9]/", "", $data['cnpj']);
            $data_account['company']['phone'] = $data_account['phone'];
        }

        return $data_account;
    }

    private function getPhone($customer)
    {
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

    /**
     * Exportar Parceiros
     *
     * @param Request $request
     * @return ParceirosExport
     */
    public function exportar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new ParceirosExport(), 'parceiros.' . strtolower($request->get('export-to-type')) . '');

    }
	
}
