<?php

namespace App\Http\Controllers\Admin;

use App\Banco;
use App\Cidade;
use App\ContaBancaria;
use App\Curador;
use App\Endereco;
use App\Estado;
use App\Usuario;
use App\ViewUsuariosCuradores;
use App\Exports\CuradoresExport;
use App\Helper\WirecardHelper;
use App\Http\Controllers\PessoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CuradorController extends PessoasController
{

    /**
     * Listar Curadores
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['objLst'] = ViewUsuariosCuradores::all();
        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.lista', $this->arrayViewData);
    }

    /**
     * Formulário para incluir curador
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function incluir()
    {
        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['tipoParceiro'] = 'curadores';
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
        $this->arrayViewData['repasse_manual'] = false;
        $this->arrayViewData['lista_bancos'] = Banco::all()->where('status', 1)->pluck('titulo', 'id');

        $this->arrayViewData['lista_generos'] = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * Editar Curadores
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editar($id)
    {

        if (!$this->validateAccess(\Session::get('user.logged'))) {
            return redirect()->route($this->redirecTo);
        }

        $this->arrayViewData['tipoParceiro'] = 'curadores';
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

        $this->arrayViewData['objCurador'] = Curador::select('*')->where('id', $id)->first();
        $this->arrayViewData['objEndereco'] = Endereco::select('endereco.*')
            ->join('curadores', 'curadores.fk_endereco_id', '=', 'endereco.id')
            ->where('curadores.id', '=', $id)
            ->first();

        $this->arrayViewData['objUsuario'] = Usuario::select('usuarios.*')
            ->join('curadores', 'curadores.fk_usuario_id', '=', 'usuarios.id')
            ->where('curadores.id', '=', $id)
            ->first();

        $this->arrayViewData['objConta'] = ContaBancaria::select('conta_bancaria.*')
            ->join('curadores', 'curadores.fk_conta_bancaria_id', '=', 'conta_bancaria.id')
            ->where('curadores.id', '=', $id)
            ->first();

        $this->arrayViewData['lista_generos'] = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];

        $this->arrayViewData['repasse_manual'] = ($this->arrayViewData['objCurador']->wirecard_account_id > 0) ? null : 1;

        return view($this->arrayViewData['modulo']['moduloDetalhes']->view . '.formulario', $this->arrayViewData);
    }

    /**
     * Salvar Curador
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function salvar(Request $request)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }
        $curador = new Curador();
        $validator = $curador->_validate($request->all());
        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            if (!empty($request->get('repasse_manual')) && $request->get('repasse_manual') == 1){
                return $this->_return($this->salvarRegistro($request, new Curador($request->all())),
                        '/admin/curador/index');
            } else {
                $data_account_wirecard = $this->prepareDadosContaWirecard($request->all());
    
                $wirecard = new WirecardHelper;
                $createAccount = $wirecard->createAccount($data_account_wirecard, 'curador');
    
                if (!empty($createAccount['success']) && !empty($createAccount['wirecard_account_id'])) {
                    $request->request->add(['wirecard_account_id' => $createAccount['wirecard_account_id']]);
    
                    return $this->_return($this->salvarRegistro($request, new Curador($request->all())),
                        '/admin/curador/index');
                } elseif (!empty($createAccount['error'])) {
                    return Redirect::back()->withErrors($createAccount['error'])->withInput();
                } else {
                    return Redirect::back()->withErrors('Não foi possível criar a conta na Wirecard!')->withInput();
                }
            }
        }
    }

    /**
     * Salvar Curador
     *
     * @param int     $id
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
        $curador = new Curador();
        $validator = $curador->_validate($request->all());

        if ($validator->fails()) {
            $this->validatorMsg = $validator;
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            if (is_null($request->get('repasse_manual'))){
                $data_curador = Curador::where('id', $id)->select('wirecard_account_id')->first();

                if ($data_curador->wirecard_account_id == 0 || is_null($data_curador->wirecard_account_id)){
                    $data_account_wirecard = $this->prepareDadosContaWirecard($request->all());

                    $wirecard = new WirecardHelper;
                    $createAccount = $wirecard->createAccount($data_account_wirecard, 'curador');
        
                    if (!empty($createAccount['success']) && !empty($createAccount['wirecard_account_id'])) {
                        $request->request->add(['wirecard_account_id' => $createAccount['wirecard_account_id']]);
                    } elseif (!empty($createAccount['error'])) {
                        return Redirect::back()->withErrors($createAccount['error'])->withInput();
                    } 
                }
            }

            return $this->_return($this->atualizarRegistro($request, Curador::findOrFail($id)), '/admin/curador/index');
        }
    }

    /**
     * Deletar Curador
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletar($id)
    {
        if (!$this->validateAccess(\Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return $this->_return($this->deletaRegistro(Curador::findOrFail($id)));
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

        if (!empty($data['titular_curador'])) {
            $name = explode(' ', $data['titular_curador']);
            $data_account['name'] = (isset($name[0])) ? $name[0] : '';
            $data_account['lastname'] = end($name);
        }

        $data_explode = explode('/', $data['data_nascimento']);
        $data_nascimento = $data_explode[2] . '-' . $data_explode[1] . '-' . $data_explode[0];

        $data_account['birth_data'] = $data_nascimento;
        $data_account['cpf'] = preg_replace("/[^0-9]/", "", $data['cpf']);

        $phone_number = $this->getPhone($data);

        $data_account['phone'] = ['ddd' => $phone_number['ddd'], 'number' => $phone_number['number'], 'prefix' => '55'];

        $company_name = $data['nome_fantasia'];

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
            $data = null;
            $phone = preg_replace("/[^0-9]/", "", $number_phone);
            $data['ddd'] = substr($phone, 0, 2);
            $data['number'] = substr($phone, 2, 9);

            return $data;
        } else {
            return false;
        }
    }

    /**
     * Exportar Curados
     *
     * @param Request $request
     * @return CuradoresExport
     */
    public function exportar(Request $request)
    {
        if (!$this->validateAccess(Session::get('user.logged'), false)) {
            return redirect()->route($this->redirecTo);
        }

        return Excel::download(new CuradoresExport(), 'curadores.' . strtolower($request->get('export-to-type')) . '');

    }
	
}
