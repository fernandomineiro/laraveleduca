<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProdutoraControllerTest extends TestCase {

    public function get_user() {
        $user = Usuario::find(1);
        $this->be($user, 'admin');
        return $user;
    }

    /**
     * @test
     */
    public function index_redirect() {
        $uri = '/admin/produtora/index';
        $response = $this->get($uri);
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function index_authenticated() {
        $uri = '/admin/produtora/index';

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

        $this->actingAs($user)->from('/admin/produtora/incluir');
        $response = $this->actingAs($user)->post('/admin/produtora/salvar');

        $response->assertRedirect('/admin/produtora/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('nome')[0], "O campo nome é obrigatório.");
        $this->assertEquals($errors->get('email')[0], "O campo email é obrigatório.");

    }

    /**
     * @test
     */
    public function salvar_produtora() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/produtora/incluir');

//        $response = $this->actingAs($user)->post('/admin/produtora/salvar', [
//            'razao_social' => 'Gabriel Resende',
//            'email' => 'gabriel.resende06@gmail.com'
//        ]);
//
//        $response->assertRedirect('/admin/produtora/incluir');
//        $response->assertSessionHasErrors();
//
//        $errors = session('errors');
//        $this->assertEquals($errors->get('email')[0], "email já está em uso.");

        $faker = Factory::create('pt_BR');
        $response = $this->actingAs($user)->post('/admin/produtora/salvar', [
            'razao_social' => $faker->name,
            'email' => $faker->unique()->email,
            'cnpj' => $faker->cnpj,
            'cep' => '35661119',
            'logradouro' => 'R Luiz P Oliveira',
            'numero' => '23',
            'fk_cidade_id' => '3885',
            'fk_estado_id' => '13',
        ]);

        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function alterar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/produtora/2/editar');
        $response = $this->actingAs($user)->patch('/admin/produtora/2/atualizar', [
            'razao_social' => 'Suzana Mia Zamana Neto',
            'fantasia' => 'Suzana Zamana Neto',
            'cnpj' => '10.486.974/0001-80',
            'cep' => '35661119',
            'logradouro' => 'R Luiz P Oliveira',
            'numero' => '23',
            'fk_cidade_id' => '3885',
            'fk_estado_id' => '13',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', [
            'id' => 207
        ]);
    }
}
