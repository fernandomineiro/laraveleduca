<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AlunoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nome'                      =>  'required|unique:alunos,nome,,id',
            'sobre_nome'                =>  'required',
            'cpf'                       =>  'required',
            'identidade'                =>  'required',
            'data_nascimento'           =>  'required|date',
            'status'                    =>  'required',
            'telefone_1'                =>  'required',
            'telefone_2'                =>  'nullable',
            'matricula'                 =>  'required',
            'semestre'                  =>  'nullable',
            'curso_superior'            =>  'nullable',
            'universidade'              =>  'nullable',
            'curso'                     =>  'nullable',
            'universidade_outro'        =>  'nullable',
            'curso_outro'               =>  'nullable',
            'curso_especializacao'      =>  'nullable',
            'tipo_curso_especializacao' =>  'nullable',
            'curso_especializacao'      =>  'nullable',
            'especializacao_universidade'=> 'nullable',
            'genero'                    =>  'required',
        ];
    }
}
