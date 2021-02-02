<?php


namespace App\Imports;


use App\Cupom;
use App\CupomAlunoSemRegistro;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PreCadastroImport implements ToModel, WithHeadingRow{

    private $id = null;
    private $faculdade = null;

    public function __construct($id, $faculdade){
        $this->id = $id;
        $this->faculdade = $faculdade;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row){

        if(!empty($row['email'])){

            return new CupomAlunoSemRegistro([
                'email'  => $row['email'],
                'cpf'  => $row['cpf'],
                'ra'  => $row['ra'],
                'nome'  => $row['nome'],
                'fk_cupom'  => $this->id,
                'fk_faculdade'  => $this->faculdade,
                'numero_usos'  => 1,
            ]);

        }

    }

}
