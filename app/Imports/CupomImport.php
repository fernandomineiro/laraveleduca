<?php

namespace App\Imports;

use App\Cupom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CupomImport implements ToModel, WithHeadingRow{

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row){

        if(!empty($row['titulo']) && !empty($row['titulo']) && !empty($row['valor'])){

            if (gettype($row['validade_inicial']) == 'integer' || gettype($row['validade_inicial']) == 'double' || gettype($row['validade_inicial']) == 'float') {
                $row['validade_inicial'] = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['validade_inicial']))->format('Y-m-d');
            } else {
                $row['validade_inicial'] = Carbon::createFromFormat('d/m/Y', trim($row['validade_inicial']))->format('Y-m-d');
            }

            if (gettype($row['validade_final']) == 'integer' || gettype($row['validade_final']) == 'double' || gettype($row['validade_final']) == 'float') {
                $row['validade_final'] = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['validade_final']))->format('Y-m-d');
            } else {
                $row['validade_final'] = Carbon::createFromFormat('d/m/Y', trim($row['validade_final']))->format('Y-m-d');
            }

            return new Cupom([
                'status'  => $row['status'],
                'titulo'  => $row['titulo'],
                'codigo_cupom'  => $row['codigo'],
                'descricao'  => $row['descricao'],
                'fk_faculdade'  => $row['faculdade'],
                'data_validade_inicial'  => $row['validade_inicial'],
                'data_validade_final'  => $row['validade_final'],
                'numero_maximo_usos' => $row['numero_maximo_usos'],
                'numero_maximo_produtos' => $row['numero_maximo_produtos'],
                'tipo_cupom_desconto'  => $row['tipo'],
                'valor'  => $row['valor'],
                'fk_criador_id'  => Session::get('user.logged')->id,
                'fk_atualizador_id'  => Session::get('user.logged')->id,
                'data_criacao'  => date('Y-m-d H:i:s'),
                'criacao'  => date('Y-m-d H:i:s'),
                'atualizacao'  => date('Y-m-d H:i:s'),
            ]);

        }

    }

}
