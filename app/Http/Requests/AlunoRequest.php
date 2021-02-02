<?php

namespace App\Http\Requests;

use App\Rules\CustomRules;
use App\UsuariosPerfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlunoRequest extends FormRequest {
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
        
        $data['fk_perfil'] = UsuariosPerfil::ALUNO;
        $rules = [
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
        
        if ($this->isUserAdminOrDev()) {
            return $rules;
        }
        
        return array_merge($rules, [
            'cpf' => [
                'required',
                'cpf',
                Rule::unique('alunos', 'cpf')->where(function ($query) use ($data) {
                    if (!empty($data['id'])) {
                        $query->where('id', '!=', $data['id']);
                    }
                    $query->where('status', '!=', 0);
                    $query->where('cpf', '=', $data['cpf']);
                    $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                }),
            ],
        ]);
    }

    /**
     * @return bool
     */
    private function isUserAdminOrDev(): bool {
        return in_array($this->user('admin')->fk_perfil,
            [UsuariosPerfil::ADMINISTRADOR, UsuariosPerfil::DESENVOLVEDOR]);
    }

    /**
     * @return bool
     */
    private function hasPasswordToChange(): bool {
        return is_null($this->get('password')) && is_null($this->get('password_confirmation'));
    }
}
