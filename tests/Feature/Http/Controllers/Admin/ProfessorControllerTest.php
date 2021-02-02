<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfessorControllerTest extends TestCase {

    public function get_user() {
        $user = Usuario::find(1);
        $this->be($user, 'admin');
        return $user;
    }

    /**
     * @test
     */
    public function index_redirect() {
        $uri = '/admin/professor/index';
        $response = $this->get($uri);
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function index_authenticated() {
        $uri = '/admin/professor/index';

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

        $this->actingAs($user)->from('/admin/professor/incluir');
        $response = $this->actingAs($user)->post('/admin/professor/salvar');

        $response->assertRedirect('/admin/professor/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('nome')[0], "O campo nome é obrigatório.");
        $this->assertEquals($errors->get('email')[0], "O campo email é obrigatório.");

    }

    /**
     * @test
     */
    public function salvar_professor() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/professor/incluir');

        $response = $this->actingAs($user)->post('/admin/professor/salvar', [
            'nome' => 'Gabriel Resende',
            'email' => 'gabriel.resende06@gmail.com'
        ]);

        $response->assertRedirect('/admin/professor/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('email')[0], "email já está em uso.");

        $faker = Factory::create('pt_BR');
        $response = $this->actingAs($user)->post('/admin/professor/salvar', [
            'nome' => $faker->name,
            'email' => $faker->unique()->email,
            'mini_curriculum' => $faker->text(),
            'cpf' => $faker->cpf,
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

        $this->actingAs($user)->from('/admin/professor/64/editar');
        $faker = Factory::create('pt_BR');
        $response = $this->actingAs($user)->patch('/admin/professor/64/atualizar', [
            'nome' => 'Laura',
            'sobrenome' => 'Lutero',
            'cpf' => $faker->cpf,
            'mini_curriculum' => $faker->text(),
            'cep' => '35661119',
            'logradouro' => 'R Luiz P Oliveira',
            'numero' => '23',
            'fk_cidade_id' => '3885',
            'fk_estado_id' => '13',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
