<?php

namespace App\Http\Requests;

use App\Rules\CustomRules;
use App\UsuariosPerfil;
use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        
        $data = $this->validationData();

        if ($this->hasPasswordToChange()) {
            $this->replace($this->except(['password', 'password_confirmation']));
        }

        return [
            'nome' => 'required',
            'sobre_nome' => 'required',
            'fk_faculdade_id' => 'required',
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable|min:8',
            'email' => [
                'required',
                'email',
                CustomRules::uniqueUserEmail('usuarios', 'email', $data)
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages() {
        return [
            'nome.required' => 'Nome é obrigatório',
            'sobre_nome.required' => 'Sobrenome é obrigatório',
            'sobrenome.required' => 'Sobrenome é obrigatório',
            'data_nascimento.required' => 'Data de nascimento é obrigatório!',
            'cep.required' => 'Cep é obrigatório!',
            'logradouro.required' => 'Logradouro é obrigatório!',
            'numero.required' => 'Número é obrigatório!',
            'fk_estado_id.required' => 'Estado é obrigatório!',
            'fk_cidade_id.required' => 'Cidade é obrigatório!',
            'bairro.required' => 'Bairro é um campo obrigatório!',
            'curso_superior.required' => 'Curso Superior é um campo obrigatório!',
            'telefone_1.required' => 'Telefone Fixo é um campo obrigatório!',
            'telefone_2.required' => 'Telefone Celular é um campo obrigatório!',
            'cpf.required' => 'CPF é obrigatório',
            'cpf.unique' => 'CPF já cadastrado no sistema!',
            'fk_usuario_id' => 'Usuário do Aluno é obrigatório',
            'fk_faculdade_id.required' => 'Projeto é obrigatório',
            'fk_endereco_id' => 'Endereço é obrigatório',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres!',
            'password.confirmed' => 'A senha e a confirmação de senha devem ser iguais!',
            'password.required' => 'A senha é obrigatória!',
            'password_confirmation.min' => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
            'password_confirmation.required' => 'A confirmação de senha é obrigatória!',
            'email.unique' => 'E-mail já cadastrado no sistema!',
            'email.required' => 'E-mail é obrigatório!',
        ];
    }

    /**
     * @return bool
     */
    public function isUserAdminOrDev(): bool {
        return in_array($this->user('admin')->fk_perfil,
            [UsuariosPerfil::ADMINISTRADOR, UsuariosPerfil::DESENVOLVEDOR]);
    }

    /**
     * @return bool
     */
    protected function hasPasswordToChange(): bool {
        return empty($this->get('password')) && empty($this->get('password_confirmation'));
    }
}
