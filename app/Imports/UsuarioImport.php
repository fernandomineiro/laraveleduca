<?php

namespace App\Imports;

use App\Usuario;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsuarioImport implements ToModel, WithHeadingRow{
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row){
        
        if(!empty($row['nome']) && !empty($row['email'])){
        
            return new Usuario([
                'status'  => $row['status'],
                'nome'  => $row['nome'],
                'email'  => $row['email'],
                'password'  => bcrypt($row['password']),
                'fk_perfil'  => $row['perfil'],
            ]);
        
        }
        
    }
    
}