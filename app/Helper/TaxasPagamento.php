<?php

namespace App\Helper;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Impostos;
use App\JurosCartao;

class TaxasPagamento {
    public function subtractTaxes($total, $payment_method, $total_users_split = 1, $number_installments = 1){
        $impostos = Impostos::first();

        $taxes = 0;
        if (isset($impostos->id)){
            $taxes = (($total / 100) * $impostos->porcentagem_iss) + $taxes;
            $taxes = (($total / 100) * $impostos->porcentagem_pis_cofins) + $taxes;
            $taxes = (($total / 100) * $impostos->porcentagem_irpj_csll) + $taxes;

            if ($payment_method == 'creditcard'){
                $taxes = (($total / 100) * $this->getTaxeCreditCard($number_installments)) + $taxes;

                $taxes = $taxes + ($impostos->valor_taxa_processamento / $total_users_split);
            } else {
                $taxes = $taxes + ($impostos->valor_taxa_boleto / $total_users_split);
            }
        }

        return number_format(($total - $taxes), 2);
    }

    public function getTaxes($total, $payment_method, $total_users_split = 1, $number_installments = 1){
        $impostos = Impostos::first();

        $taxes = 0;
        if (isset($impostos->id)){
            $taxes = (($total / 100) * $impostos->porcentagem_iss) + $taxes;
            $taxes = (($total / 100) * $impostos->porcentagem_pis_cofins) + $taxes;
            $taxes = (($total / 100) * $impostos->porcentagem_irpj_csll) + $taxes;

            if ($payment_method == 'creditcard'){
                $taxes = (($total / 100) * $this->getTaxeCreditCard($number_installments)) + $taxes;

                $taxes = $taxes + ($impostos->valor_taxa_processamento / $total_users_split);
            } else {
                $taxes = $taxes + ($impostos->valor_taxa_boleto / $total_users_split);
            }
        }

        return number_format($taxes, 2);
    }

    private function getTaxeCreditCard($number_installments){
        $juros_cartao = JurosCartao::select('percentual')->where('parcela', $number_installments)->first();

        return $juros_cartao->percentual;
    }
}
