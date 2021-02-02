<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CuradorControllerTest extends TestCase {

    public function get_user() {
        $user = Usuario::find(1);
        $this->be($user, 'admin');
        return $user;
    }

    /**
     * @test
     */
    public function index_redirect() {
        $uri = '/admin/curador/index';
        $response = $this->get($uri);
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function index_authenticated() {
        $uri = '/admin/curador/index';

        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $response = $this->actingAs($user)->get($uri);
        $response->assertStatus(200);
        $response->assertViewHas('objLst');
    }

    /**
     * @test
     */
    public function salvar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/curador/incluir');
        $response = $this->actingAs($user)->post('/admin/curador/salvar');

        $response->assertRedirect('/admin/curador/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('nome')[0], "O campo nome é obrigatório.");
        $this->assertEquals($errors->get('email')[0], "O campo email é obrigatório.");

    }

    /**
     * @test
     */
    public function salvar_curador() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/curador/incluir');

        $response = $this->actingAs($user)->post('/admin/curador/salvar', [
            'razao_social' => 'Gabriel Resende',
            'email' => 'gabriel.resende06@gmail.com'
        ]);

        $response->assertRedirect('/admin/curador/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('email')[0], "email já está em uso.");

        $faker = Factory::create('pt_BR');
        $this->actingAs($user)->from('/admin/curador/incluir');
        $response = $this->actingAs($user)->post('/admin/curador/salvar', [
            'razao_social' => $faker->name,
            'email' => $faker->unique()->email,
            'cnpj' => $faker->cnpj,
            'cep' => '35661119',
            'logradouro' => 'R Luiz P Oliveira',
            'numero' => '23',
            'fk_cidade_id' => '3885',
            'fk_estado_id' => '13',
            'titular' => $faker->name,
            'fk_banco_id' => 6,
            'agencia' => '1234',
            'conta_corrente' => '0000115324',
            'operacao' => '013',
            'documento' => $faker->cnpj
        ]);

        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function alterar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/curador/4/editar');
        $response = $this->actingAs($user)->patch('/admin/curador/4/atualizar', [
            'razao_social' => 'Ana Giovana Branco Neto',
            'nome_fantasia' => 'Ana Giovana Branco Neto ',
            'cep' => '35661119',
            'cnpj' => '58.418.840/0001-59',
            'logradouro' => 'R Luiz P Oliveira',
            'numero' => '23',
            'fk_cidade_id' => '3885',
            'fk_estado_id' => '13',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
