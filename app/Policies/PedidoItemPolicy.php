<?php

namespace App\Policies;

use App\Usuario;
use App\UsuariosPerfil;
use Illuminate\Auth\Access\HandlesAuthorization;

class PedidoItemPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the given PedidoItem can be list by the user.
     *
     * @param  \App\Usuario  $usuario
     * @return bool
     */
    public function index(Usuario $usuario)
    {
        return $usuario->fk_perfil === UsuariosPerfil::ADMINISTRADOR || $usuario->fk_perfil === UsuariosPerfil::PROFESSOR 
            || $usuario->fk_perfil === UsuariosPerfil::MARKETING_IES || $usuario->fk_perfil === UsuariosPerfil::FINANCEIRO_IES 
            || $usuario->fk_perfil === UsuariosPerfil::GESTOR_IES;
    }

    /**
     * Determine if the given PedidoItem can be export by the user.
     *
     * @param  \App\Usuario  $usuario
     * @return bool
     */
    public function export(Usuario $usuario)
    {
        return $usuario->fk_perfil === UsuariosPerfil::ADMINISTRADOR || $usuario->fk_perfil === UsuariosPerfil::PROFESSOR 
            || $usuario->fk_perfil === UsuariosPerfil::MARKETING_IES || $usuario->fk_perfil === UsuariosPerfil::FINANCEIRO_IES 
            || $usuario->fk_perfil === UsuariosPerfil::GESTOR_IES;
    }
}
