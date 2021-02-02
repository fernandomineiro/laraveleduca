<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsuarioControllerTest extends TestCase {

    public function get_user() {
        $user = Usuario::find(1);
        $this->be($user, 'admin');
        return $user;
    }

    /**
     * @test
     */
    public function salvar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/usuario/incluir');
        $response = $this->actingAs($user)->post('/admin/usuario/salvar');

        $response->assertRedirect('/admin/usuario/incluir');
        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('nome')[0], "O campo nome é obrigatório.");
        $this->assertEquals($errors->get('email')[0], "O campo email é obrigatório.");

        $this->actingAs($user)->from('/admin/usuario/incluir');

        $faker = Factory::create();

        $email = $faker->unique()->safeEmail;
        $response = $this->actingAs($user)->post('/admin/usuario/salvar', [
            'nome' => $faker->name,
            'email' => 'gabriel.resende06@gmail.com'
        ]);

        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals($errors->get('email')[0], "email já está em uso.");
        $this->assertEquals($errors->get('fk_perfil')[0], "O campo fk perfil é obrigatório.");

        $this->actingAs($user)->from('/admin/usuario/incluir');
        $response = $this->actingAs($user)->post('/admin/usuario/salvar', [
            'nome' => $faker->name,
            'email' => $email,
            'fk_perfil' => 2
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', [
            'email' => $email
        ]);
    }

    /**
     * @test
     */
    public function alterar() {
        $user = $this->get_user();
        $this->assertAuthenticatedAs($user, 'admin');

        $this->actingAs($user)->from('/admin/usuario/202/editar');
        $response = $this->actingAs($user)->patch('/admin/usuario/202/atualizar', [
            'nome' => 'Gabriel D Resende',
            'email' => 'gabriel.resende06@gabriel.test.de',
            'fk_perfil' => 2
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', [
            'id' => 202
        ]);
    }

}
