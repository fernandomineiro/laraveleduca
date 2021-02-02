<?php

namespace Tests\Feature\Http\Controllers\API;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WirecardControllerTest extends TestCase {

    use DatabaseTransactions;
    
    /** @test */
    public function validar_se_nao_existe_dados_basicos_para_comecar_o_pagamento() {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/wirecard/pay', []);

        $response->assertJson(
            [
                'error' => [
                    'order_id' => ['O campo order id é obrigatório.'],
                    'method' => ['O campo method é obrigatório.']
                ]
            ]
        );
    }

    /** @test */
    public function validar_dados_do_cartao_credito() {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/wirecard/pay', 
            [
                'order_id' => 31028,
                'method' => 'credit-card'
            ]
        );
        $response->assertJson(
            [
                'error' => [
                    "full_name" => [
                        "O nome completo é obrigatório."
                    ],
                    "birth_date" => [
                            "A data de nascimento é obrigatória."
                    ],
                    "credit_card_number" => [
                            "Cartão de crédito inválido"
                    ],
                    "cvv" => [
                            "O campo cvv é obrigatório."
                    ],
                    "expiry_month" => [
                            "Data de vencimento inválida"
                    ],
                    "expiry_year" => [
                            "Data de vencimento inválida"
                    ],
                    "installment" => [
                            "Número de parcelas inválida"
                    ],
                    "document" => [
                            "CPF ou CNPJ inválido"
                    ],
                    "ddd" => [
                            "O campo ddd é obrigatório."
                    ],
                    "phone" => [
                        "O campo phone é obrigatório."
                    ]
                ]
            ]
        );
    }

    /** @test */
    public function test_se_cpf_e_invalido() {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/wirecard/pay',
            [
                'order_id' => 31028,
                'method' => 'credit-card',
                'birth_date' => '2020-08-08',
                'credit_card_number' => '123098418793141234',
                'cvv' => '123',
                'document' => '010.934.883.43',
                'expiry_month' => '06',
                'expiry_year' => '22',
                'full_name' => 'Cartao Test',
                'installment' => '1',
                'phone' => '12340998134',
                'ddd' => '12',
            ]
        );
        
        $response->assertJson([ 'error' => ['CPF ou CNPJ inválido!']]);
    }
    
    /** @test */
    public function abc() {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/wirecard/pay',
            [
                'order_id' => 31028,
                'method' => 'credit-card',
                'birth_date' => '2020-08-08',
                'credit_card_number' => '5555666677778880',
                'cvv' => '123',
                'document' => '446.378.250-32',
                'expiry_month' => '06',
                'expiry_year' => '22',
                'full_name' => 'Cartao Test',
                'installment' => '1',
                'phone' => '12340998134',
                'ddd' => '12',
            ]
        );
        
        $response->assertJson([ 'error' => [
            'Aluno não encontrado!',
            'Cidade não foi preenchida',
            null
        ],
        "code" => "100720191140"]);
    }
}
