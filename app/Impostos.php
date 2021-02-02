<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Impostos extends Model
{
    const ISS = 5.00;
    const PIS_COFINS = 3.65;
    const IRPJ_CSLL = 7.68;
    const TAXA_BOLETO = 3.50;
    const TAXA_PROCESSAMENTO = 1.00;
    const TAXA_CARTAO = 3.99;

    protected $table = 'impostos';
    protected $fillable = ['porcentagem_iss', 'porcentagem_pis_cofins', 'porcentagem_irpj_csll', 'valor_taxa_boleto', 'valor_taxa_processamento'];
    public $timestamps  = false;
}
