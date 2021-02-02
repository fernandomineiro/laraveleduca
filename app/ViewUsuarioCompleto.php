<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ViewUsuarioCompleto extends Authenticatable implements JWTSubject {
    protected $table = 'vw_usuarios_completo';

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Retorna a classe Endereço associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endereco()
    {
        return $this->HasOne('\App\Endereco', 'id', 'fk_endereco_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function usuario()
    {
        return $this->HasOne('\App\Usuario', 'fk_usuario_id');
    }

    /**
     * Retorna a classe Usuario associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function conta()
    {
        return $this->HasOne('\App\ContaBancaria', 'id', 'fk_conta_bancaria_id');
    }

    /**
     * Retorna a classe Curso associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cursos()
    {
        return $this->hasMany('App\Curso', 'fk_professor', 'id');
    }

    /**
     * Retorna a classe ProfessorFormacao associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formacoes()
    {
        return $this->hasMany('App\ProfessorFormacao', 'fk_professor_id');
    }

    /**
     * Retorna a classe Proposta associada
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function propostas()
    {
        return $this->hasMany('App\Proposta', 'fk_professor');
    }
}
