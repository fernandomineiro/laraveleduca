<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlunoControllerTest extends TestCase {

    public function get_user() {
        $user = Usuario::find(1);
        $this->be($user, 'admin');
        return $user;
    }

    /**
     * @test
     */
    public function index_redirect() {
        $uri = '/admin/aluno/index';
        $response = $this->get($uri);
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function index_authenticated() {
        $uri = '/admin/aluno/index';

        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $response = $this->actingAs($user)->get($uri);
        $response->assertStatus(200);
        $response->assertViewHas('objLst');
    }

    /**
     * @test
     */
    public function incluir() {
        $uri = '/admin/aluno/incluir';
        $response = $this->get($uri);
        $response->assertStatus(302);

        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $response = $this->actingAs($user)->get($uri);
        $response->assertStatus(200);
        $response->assertViewHas('cidades');
        $response->assertViewHas('estados');
        $response->assertViewHas('faculdades');
        $response->assertViewHas('cursos');
        $response->assertViewHas('semestres');
    }

    /**
     * @test
     */
    public function salvar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/aluno/incluir');
        $response = $this->actingAs($user)->post('/admin/aluno/salvar');

        $response->assertRedirect('/admin/aluno/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('nome')[0], "O campo nome é obrigatório.");
        $this->assertEquals($errors->get('email')[0], "O campo email é obrigatório.");

    }

    /**
     * @test
     */
    public function salvar_usuario() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/aluno/incluir');

        $response = $this->actingAs($user)->post('/admin/aluno/salvar', [
            'nome' => 'Gabriel Resende',
            'email' => 'gabriel.resende06@gmail.com'
        ]);

        $response->assertRedirect('/admin/aluno/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('email')[0], "email já está em uso.");

        $faker = Factory::create('pt_BR');
        $response = $this->actingAs($user)->post('/admin/aluno/salvar', [
            'nome' => $faker->name,
            'email' => $faker->unique()->email,
            'cpf' => $faker->cpf,
            'identidade' => $faker->rg,
            'data_nascimento' => $faker->dateTimeThisCentury(),
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

        $this->actingAs($user)->from('/admin/aluno/68/editar');
        $response = $this->actingAs($user)->patch('/admin/aluno/68/atualizar', [
            'nome' => 'Isabella',
            'sobre_nome' => 'Paes Mascarenhas',
            'cpf' => '747.156.845-35',
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
