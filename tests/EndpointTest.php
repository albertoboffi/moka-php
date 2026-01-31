<?php

require_once __DIR__ . '/../src/Endpoint.php';

use PHPUnit\Framework\TestCase;

class EndpointTest extends TestCase{

    private $configFile;

    public function setUp(): void{
        
        $this->configFile = tempnam(sys_get_temp_dir(), 'test_config');
        $ini_content = "[api]\norigin=http://test.com\n";
        file_put_contents($this->configFile, $ini_content);

        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        http_response_code(200);

    }

    public function tearDown(): void{

        if (file_exists($this->configFile)){

            unlink($this->configFile);

        }

    }

    // Contract: dispatch invokes the callback registered for the current HTTP method.
    public function testItDispatchesToRegisteredCallback(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_LENGTH'] = 0;

        $endpoint = new Endpoint($this->configFile);
        
        $called = false;
        $endpoint->get(function($headers, $params, $body) use (&$called) {

            $called = true;
            return ['status' => 'ok'];

        });

        ob_start();
        $endpoint->dispatch();
        ob_end_clean();

        $this->assertTrue($called);

    }

    // Contract: dispatch provides headers, GET params, and body to the callback.
    public function testItPassesContextToCallback(){

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_SERVER['CONTENT_LENGTH'] = 5;
        $_POST = ['foo' => 'bar'];
        $_GET = ['query' => 'param'];

        $endpoint = new Endpoint($this->configFile);

        $endpoint->post(function($headers, $params, $body){

            if ($params['query'] !== 'param') throw new Exception('Params mismatch');
            if ($body['foo'] !== 'bar') throw new Exception('Body mismatch');
            return ['result' => 'verified'];

        });

        ob_start();
        $endpoint->dispatch();
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());

    }

    // Contract: dispatch ignores the request if no callback is registered for the method.
    public function testItIgnoresRequestsWithoutRegisteredCallback(){

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['CONTENT_LENGTH'] = 0;

        $endpoint = new Endpoint($this->configFile);
        
        ob_start();
        $endpoint->dispatch();
        $output = ob_get_clean();

        $this->assertEmpty($output);

    }

    // Contract: dispatch handles exceptions by sending an error response.
    public function testItSendsErrorResponseOnException(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_LENGTH'] = 0;

        $endpoint = new Endpoint($this->configFile);

        $endpoint->get(function(){

            throw new Exception('Failure');

        });

        ob_start();
        $endpoint->dispatch();
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
        $this->assertEmpty($output);

    }

}

// Re-declare polyfill for this test file context
if (!function_exists('getallheaders')){

    function getallheaders(){

        return [];

    }

}
?>
