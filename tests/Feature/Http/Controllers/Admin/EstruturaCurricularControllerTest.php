<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EstruturaCurricularControllerTest extends TestCase {

    use DatabaseTransactions;
    
    public function testSeUsuarioDeslogadoPodeAcessarAIndex() {
        $this->get('/admin/estrutura-curricular/index')
            ->assertStatus(302)
            ->assertRedirect('/admin');
    }

    public function testSeUsuarioLogadoPodeAcessarAIndex() {
        $this->withoutExceptionHandling();
        $this->actingAs(Usuario::find(1), 'admin');

        $this->get('/admin/estrutura-curricular/index')
            ->assertStatus(200);
    }

    public function testeSeUsuarioPodeVerTodasAsEstruturasCurriculares() {
        $this->actingAs(Usuario::find(1), 'admin');

        $response = $this->get('/admin/estrutura-curricular/index');
        $response->assertViewHas('estruturas_curriculares');
    }
}
