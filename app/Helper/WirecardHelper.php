<?php

namespace App\Helper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\WirecardAccount;
use App\TipoPagamento;
use Moip\Moip;
use Moip\Auth\Connect;
use Moip\Auth\BasicAuth;
use Moip\Auth\OAuth;
use App\PedidoHistoricoStatus;
use App\Faculdade;
use App\Curador;
use App\Professor;
use App\Produtora;
use App\Parceiro;

class WirecardHelper extends Controller
{
    private $moip;
    private $moip_merchant;
    private $error = false;
    private $url_front;

    public function __construct(){
        $this->autenticationMerchant();

        if ($this->error){
            return response()->json($this->error);
        }
    }

    private function autentication($access_token){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $Auth = new Moip(new OAuth($access_token), Moip::ENDPOINT_PRODUCTION);
            } else {
                $Auth = new Moip(new OAuth($access_token), Moip::ENDPOINT_SANDBOX);
            }

            if (!isset($Auth) || !$Auth){
                $this->error = ['error' => 'Erro na autenticação!', 'code' => '04071409'];
            }
        } else {
            $this->error = ['error' => 'O módulo Wirecard não está habilitado!', 'code' => '04071228'];
        }

        return $Auth;
    }

    private function autenticationMerchant(){
        $setting = TipoPagamento::where('codigo', 'wirecard')->first();

        if (isset($setting->status) && $setting->status == 1){
            if ($setting->ambiente == 'producao'){
                $this->moip_merchant = new Moip(new OAuth($setting->app_producao), Moip::ENDPOINT_PRODUCTION);
            } else {
                $this->moip_merchant = new Moip(new OAuth($setting->app_teste), Moip::ENDPOINT_SANDBOX);
            }

            if (!isset($this->moip_merchant) || !$this->moip_merchant){
                $this->error = ['error' => 'Erro na autenticação!', 'code' => '04071409'];
            }
        } else {
            $this->error = ['error' => 'O módulo Wirecard não está habilitado!', 'code' => '04071228'];
        }
    }

    # METODO API PARA CRIACAO DE SUBCONTAS WIRECARD NO MOMENTO DO CADASTRO DO USUARIO #
    public function createAccount($data, $type){
        $error = false;

        $data['type_account']   = 'MERCHANT';
        $data['tos_acceptance'] = ['accepted_at' => new Datetime(), 'ip' => '', 'user_agent' => ''];

        $messages = [
            'name.required'             => 'Nome inválido!',
            'lastname.required'         => 'Sobrenome inválido!',
            'email.required'            => 'E-mail inválido!',
            'birth_data.required'       => 'Data de nascimento inválida!',
            'cpf.required'              => 'CPF inválido!',
            'phone.prefix.required'     => 'Prefixo do telefone é inválido!',
            'phone.ddd.required'        => 'DDD inválido!',
            'phone.number.required'     => 'Número de telefone inválido!',
            'address.street.required'   => 'Endereço: Rua inválida!',
            'address.number.required'   => 'Endereço: Número inválido!',
            'address.district.required' => 'Endereço: Bairro inválido!',
            'address.city.required'     => 'Endereço: Cidade inválida!',
            'address.state.required'    => 'Endereço: Estado inválido!',
            'address.zipcode.required'  => 'Endereço: CEP inválido',
            'address.country.required'  => 'Endereço: CODE ISO 3 inválido. (ex.: "BRA")',
        ];

        $validator = Validator::make($data, [
            'name'             => 'required',
            'lastname'         => 'required',
            'email'            => 'required',
            'birth_data'       => 'required',
            'cpf'              => 'required',
            'phone.prefix'     => 'required',
            'phone.ddd'        => 'required',
            'phone.number'     => 'required',
            'address.district' => 'required',
            'address.country'  => 'required',
        ], $messages);

        if ($validator->errors()){
            $errors = $validator->errors()->toArray();

            foreach ($errors as $key => $error) {
                return ['error' => $error[0]];
            }
        }

        if (isset($data['company'])){
            $messages = [
                'phone.prefix.required'     => 'Prefixo do telefone é inválido!',
                'phone.ddd.required'        => 'DDD inválido!',
                'phone.number.required'     => 'Número de telefone inválido!',
                'address.street.required'   => 'Empresa - Endereço: Rua inválida!',
                'address.number.required'   => 'Empresa - Endereço: Número inválido!',
                'address.district.required' => 'Empresa - Endereço: Bairro inválido!',
                'address.city.required'     => 'Empresa - Endereço: Cidade inválida!',
                'address.state.required'    => 'Empresa - Endereço: Estado inválido!',
                'address.zipcode.required'  => 'Empresa - Endereço: CEP inválido',
                'address.country.required'  => 'Empresa - Endereço: CODE ISO 3 inválido. (ex.: "BRA")',
            ];

            $validator = Validator::make($data, [
                'phone.prefix'     => 'required',
                'phone.ddd'        => 'required',
                'phone.number'     => 'required',
                'address.street'   => 'required',
                'address.number'   => 'required',
                'address.district' => 'required',
                'address.city'     => 'required',
                'address.state'    => 'required',
                'address.zipcode'  => 'required',
                'address.country'  => 'required',
            ], $messages);

            if ($validator->errors()){
                $errors = $validator->errors()->toArray();

                foreach ($errors as $key => $error) {
                    return ['error' => $error[0]];
                }
            }
        }

        if (!$this->error){
            try {
                $account = $this->moip_merchant->accounts()
                ->setName($data['name'])
                ->setLastName($data['lastname'])
                ->setEmail($data['email'])
                ->setBirthDate($data['birth_data'])
                ->setTaxDocument((string)$data['cpf'])
                ->setType($data['type_account'])
                ->setTransparentAccount(true)
                ->setPhone($data['phone']['ddd'], $data['phone']['number'], $data['phone']['prefix'])
                ->addAddress($data['address']['street'], $data['address']['number'], $data['address']['district'], $data['address']['city'], $data['address']['state'], $data['address']['zipcode'], '', $data['address']['country']);

                if (isset($data['company'])){
                    $account->setCompanyName($data['company']['name'], $data['company']['business_name'])
                    ->setCompanyPhone($data['company']['phone']['ddd'], $data['company']['phone']['number'], $data['company']['phone']['prefix'])
                    ->setCompanyTaxDocument((string)$data['company']['cnpj'])
                    ->setCompanyAddress($data['company']['address']['street'], $data['company']['address']['number'], $data['company']['address']['district'], $data['company']['address']['city'], $data['company']['address']['state'], $data['company']['address']['zipcode'], '', $data['company']['address']['country']);
                }

                $account->create();

                if ($account->getId()){
                    # ADICIONA DADOS DA CONTA WIRECARD NA BASE
                    $wirecard_account_id = $this->addWirecardAccount($account->getId(), $account->getAccessToken(), $account->getChannelId());

                    $this->addWireCardAccountId($wirecard_account_id, $type, time());

                    return ['success' => 'Conta criada com sucesso!', 'wirecard_account_id' => $wirecard_account_id];
                } else {
                    return ['error' => 'Não foi possível criar sua conta', 'code' => '190720191241'];
                }

                return $account;
            } catch (\Moip\Exceptions\UnautorizedException $e) {
                //StatusCode 401
                return ['error' => $e->getMessage()];
            } catch (\Moip\Exceptions\ValidationException $e) {
                //StatusCode entre 400 e 499 (exceto 401)
                return ['error' => $e->__toString()];
            } catch (\Moip\Exceptions\UnexpectedException $e) {
                //StatusCode >= 500
                return ['error' => $e->getMessage()];
            }
        }
    }

    private function addWireCardAccountId($wirecard_account_id, $type, $id){
        if ($type == 'professor' || $type == 'professor_participante'){
            Professor::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        }  elseif ($type == 'curador'){
            Curador::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'parceiro'){
            Parceiro::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'faculdade'){
            Faculdade::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        } elseif ($type == 'produtora'){
            Produtora::where('id', $id)->update(['wirecard_account_id' => $wirecard_account_id]);
        }
    }

    private function addWirecardAccount($id, $access_token, $channel_id){
        $wirecardAccount = new WirecardAccount;
        $wirecardAccount->account_id = $id;
        $wirecardAccount->access_token = $access_token;
        $wirecardAccount->channel_id = $channel_id;
        $wirecardAccount->save();

        return $wirecardAccount->id;
    }
}
