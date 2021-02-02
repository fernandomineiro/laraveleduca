<?php

namespace App\Http\Requests;

use App\Rules\CustomRules;
use App\UsuariosPerfil;

class ProfessorRequest extends UsuarioRequest {
    
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

        $data['fk_perfil'] = UsuariosPerfil::PROFESSOR;
        $rules = [
            'nome' => 'required',
            'sobrenome' => 'required',
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable|min:8',
            'email' => [
                'required',
                'email',
                CustomRules::uniqueUserEmail('usuarios', 'email', $data)
            ],
        ];
        
        if ($this->isUserAdminOrDev()) {
            return $rules;
        }

        return array_merge($rules, [
            'share' => 'required',
            'cep' => 'required',
            'logradouro' => 'required',
            'numero' => 'required',
            'fk_estado_id' => 'required',
            'fk_cidade_id' => 'required',
            'profissao' => 'required',
            'telefone_2' => 'required',
            'mini_curriculum' => 'required',
            'cpf' => [
                'required',
                'cpf',
                CustomRules::uniqueCpf('professor', 'cpf', $data),
            ],
        ]);
    }
}
