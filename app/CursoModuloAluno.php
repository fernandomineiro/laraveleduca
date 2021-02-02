<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CursoModuloAluno extends Model
{
    public $timestamps = true;
    const CREATED_AT = 'criacao';

    protected $table = 'cursos_modulos_alunos';
    protected $fillable = [
        'fk_aluno_id',
        'fk_curso_id',
        'fk_curso_modulo_id',
        'fk_faculdade_id',
        'fk_pedido_id',
        'data_criacao',
        'flag_concluido',
    ];

    public $rules = [
        'fk_aluno_id' => 'required',
        'fk_curso_id' => 'required',
        'fk_curso_modulo_id' => 'required',
        'fk_faculdade_id' => 'required',
        'fk_pedido_id' => 'required'
    ];

    public $messages = [
        'fk_aluno_id.required' => 'Aluno obrigatório',
        'fk_curso_id.required' => 'Curso obrigatório',
        'fk_curso_modulo_id.required' => 'Móduo do Curso obrigatório',
        'fk_faculdade_id.required' => 'Projeto obrigatório',
        'fk_pedido_id.required' => 'Pedido obrigatório',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function aluno()
    {
        return $this->hasOne('App\Aluno', 'fk_aluno_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function curso()
    {
        return $this->hasOne('App\Curso', 'fk_curso_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cursoModulo()
    {
        return $this->hasOne('App\CursoModulo', 'fk_curso_modulo_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function faculdade()
    {
        return $this->hasOne('App\Faculdade', 'fk_faculdade_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pedido()
    {
        return $this->hasOne('App\Pedido', 'fk_pedido_id');
    }
}
