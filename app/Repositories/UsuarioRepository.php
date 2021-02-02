<?php

namespace App\Repositories;

use App\Usuario;

class UsuarioRepository extends RepositoryAbstract {

    public function __construct(Usuario $model) {
        $this->model = $model;
    }
    
    /**
     * @param string $email
     * @param null $idFaculdade
     * @return mixed
     */
    public function getUsuarioFaculdadeByEmail(string $email, $idFaculdade = null, $idPerfil = null) {
        $verificaUsuarioExiste = 
            $this->model
                ->select('*')
                ->where('usuarios.email', '=', $email)
                ->where('usuarios.status', '=', '1');

        if (!empty($idFaculdade)) {
            $verificaUsuarioExiste->where('usuarios.fk_faculdade_id', '=', $idFaculdade);
        }
        
        if (!empty($idPerfil)) {
            $verificaUsuarioExiste->where('usuarios.fk_perfil', '=', $idPerfil);
        }
 
        return $verificaUsuarioExiste->first();
    }
    
}
