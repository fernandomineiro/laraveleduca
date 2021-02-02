<?php

namespace App\Rules;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CustomRules extends Rule {
    public static function uniqueUserEmail(string $table, string $column = 'NULL', array $data = []) {
        return (new Unique($table, $column))
                    ->where(function ($query) use ($data) {
                        if (!empty($data['fk_usuario_id'])) {
                            $query->where('id', '!=', $data['fk_usuario_id']);
                        }
                        
                        if (!empty($data['fk_faculdade_id'])) {
                            $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                        }
            
                        if (!empty($data['fk_perfil'])) {
                            $query->where('fk_perfil', '=', $data['fk_perfil']);
                        }

                        $query->where('email', '=', $data['email']);
                        $query->where('status', '=','1');
                        
                    });
    }

    public static function uniqueCpf(string $table, string $column = 'NULL', array $data = []) {
        return (new Unique($table, $column))
            ->where(function ($query) use ($data) {
                if (!empty($data['id'])) {
                    $query->where('id', '!=', $data['id']);
                }
                
                if (!empty($data['fk_faculdade_id'])) {
                    $query->where('fk_faculdade_id', '=', $data['fk_faculdade_id']);
                }
                
                $query->where('status', '!=', 0);
                $query->where('cpf', '=', $data['cpf']);
            });
    }
}
