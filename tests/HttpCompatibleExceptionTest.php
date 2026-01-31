<?php

require_once __DIR__ . '/../src/HttpCompatibleException.php';

use PHPUnit\Framework\TestCase;

class HttpCompatibleExceptionTest extends TestCase{

    // Contract: HttpCompatibleException defaults to status code 400 if not provided.
    public function testItDefaultsToCode400WhenInstantiatedWithoutCode(){

        $stub = new class() extends HttpCompatibleException{};

        $this->assertEquals(400, $stub->getCode());

    }

    // Contract: HttpCompatibleException accepts a custom status code.
    public function testItAcceptsCustomStatusCodeInConstructor(){

        $stub = new class(404) extends HttpCompatibleException{};

        $this->assertEquals(404, $stub->getCode());

    }

    // Contract: getBody returns only the public/protected properties of the subclass, excluding ErrorException internals.
    public function testItReturnsChildPropertiesOnlyInGetBody(){

        $exception = new class(418) extends HttpCompatibleException{

            public $foo = 'bar';
            protected $baz = 'qux';
            private $hidden = 'params';

            public function __construct($code){

                $this->foo = 'bar_value';
                parent::__construct($code);

            }

        };

        $body = $exception->getBody();

        $this->assertArrayHasKey('foo', $body);
        $this->assertArrayHasKey('baz', $body);
        $this->assertEquals('bar_value', $body['foo']);
        $this->assertEquals('qux', $body['baz']);
        
        $this->assertArrayNotHasKey('message', $body);
        $this->assertArrayNotHasKey('code', $body);
        $this->assertArrayNotHasKey('file', $body);
        $this->assertArrayNotHasKey('line', $body);
        $this->assertArrayNotHasKey('hidden', $body);

    }

}
?>
