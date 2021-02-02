<?php

namespace Tests\Unit;

use App\Helper\CertificadoHelper;
use App\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificadoTest extends TestCase {
    
    use DatabaseTransactions;
    private $certificado;
    
    protected function setUp(): void {
        parent::setUp(); 
        $this->certificado = new CertificadoHelper();
    }

    public function testDisponibilidadeCertificadoCursoNaoExistente() {
        $response = $this->certificado->disponibilidadeCertificado(Usuario::find(16150), null);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Curso não existe', $response['error']);
    }

    public function testSeExisteCertificado() {
        $response = $this->certificado->getCertificado(16150, 2);
        
        $this->assertCount(0, $response);
    }
    
    public function testVerificaCriteriosParaConclusaoCursoFaculdade() {
        $this->withoutExceptionHandling();
        $response = $this->certificado->verificaCriteriosParaConclusaoCursoFaculdade(2, Usuario::find(16150)->fk_faculdade_id);
        $this->assertIsObject( $response);
    }

    public function testVerificaDisponibilidadeCertificado() {
        $this->markTestSkipped();
        $this->withoutExceptionHandling();
        $response = $this->certificado->disponibilidadeCertificado(Usuario::find(16150), 2);
        $this->assertTrue($response['success']);
    }

    public function testPodeEmitirCertificado() {
        $this->withoutExceptionHandling();
        
        $response = $this->certificado->emiteCertificado(16150, 2);

        $this->assertFalse($response['success']);
    }

    public function testEmitirCertificadoEstrutura() {
        $this->withoutExceptionHandling();
        $response = $this->certificado->emiteCertificadoEstruturaCurricular(16150, 19);

        $this->assertTrue($response['success']);
    }
}
