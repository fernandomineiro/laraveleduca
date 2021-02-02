<?php

namespace Tests\Unit;

use App\Faculdade;
use App\Http\Controllers\API\WirecardSignatureController;
use Tests\TestCase;

class WirecardSignatureControllerTest extends TestCase {

    /** @var WirecardSignatureController */
    protected $controller;
    
    protected $faculdadeMocked;
    
    protected function setUp(): void {
        parent::setUp();
        $this->controller = new WirecardSignatureController();
        $this->faculdadeMocked = \Mockery::mock(Faculdade::class)->makePartial();
    }
    
    protected function tearDown(): void {
        parent::tearDown(); 
        unset($this->controller);
        \Mockery::close();
    }

    public function testSeRetornoDaUrlEVazioSeFaculdadeNaoExistir() {
        $this->markTestSkipped();
        $this->faculdadeMocked->shouldReceive('url')->andReturnNull();
        
        $response = $this->controller->getURLFront($this->faculdadeMocked);
        
        $this->assertEmpty($response);
    }

    public function testSeRetornoDaUrlDaFaculdade() {
        $this->markTestSkipped();
        $expectedUrl = 'http://educaz.com.br';
        $this->faculdadeMocked->shouldReceive('url')->andReturn($expectedUrl);
        $this->app->instance(Faculdade::class, $this->faculdadeMocked);
        $this->faculdadeMocked->url = $expectedUrl;
        
        $response = $this->controller->getURLFront($this->faculdadeMocked);

        $this->assertEquals($expectedUrl, $response);
    }
}
