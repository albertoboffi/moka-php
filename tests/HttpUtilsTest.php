<?php

require_once __DIR__ . '/../src/HttpUtils.php';

use PHPUnit\Framework\TestCase;

// Polyfill for getallheaders if not present (CLI)
if (!function_exists('getallheaders')){

    function getallheaders(){

        return HttpUtilsTest::$headers;

    }

}

class HttpUtilsTest extends TestCase{

    public static $headers = [];

    public function setUp(): void{

        self::$headers = [];
        $_SERVER = [];
        $_POST = [];
        $_FILES = [];
        http_response_code(200);

    }

    // Contract: getMethod returns the request method from SERVER global.
    public function testItReturnsRequestMethod(){

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('GET', HttpUtils::getMethod());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', HttpUtils::getMethod());

    }

    // Contract: getHeaders returns all headers from the environment.
    public function testItReturnsAllHeaders(){

        self::$headers = ['Authorization' => 'Bearer token', 'Content-Type' => 'application/json'];
        $headers = HttpUtils::getHeaders();

        $this->assertEquals(self::$headers, $headers);

    }

    // Contract: getBody returns empty array if Content-Length is 0.
    public function testItReturnsEmptyArrayWhenContentLengthIsZero(){

        $_SERVER['CONTENT_LENGTH'] = 0;
        $body = HttpUtils::getBody([]);

        $this->assertEquals([], $body);

    }

    // Contract: getBody returns POST data for x-www-form-urlencoded content type.
    public function testItReturnsPostDataForUrlencodedContent(){

        $_SERVER['CONTENT_LENGTH'] = 10;
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_POST = ['key' => 'value'];

        $body = HttpUtils::getBody([]);

        $this->assertEquals(['key' => 'value'], $body);

    }

    // Contract: getBody returns merged POST and FILES data for multipart/form-data.
    public function testItReturnsMergedContentForMultipartFormData(){

        $_SERVER['CONTENT_LENGTH'] = 10;
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=---';
        $_POST = ['text' => 'hello'];
        $_FILES = ['file' => ['name' => 'test.txt']];

        $body = HttpUtils::getBody([]);

        $this->assertEquals(['text' => 'hello', 'file' => ['name' => 'test.txt']], $body);

    }

    // Contract: getBody returns null, sets 415 status code, and sets headers for unsupported content types.
    public function testItRejectsUnsupportedContentType(){

        $_SERVER['CONTENT_LENGTH'] = 10;
        $_SERVER['CONTENT_TYPE'] = 'text/plain';

        $body = HttpUtils::getBody(['origin' => 'http://localhost']);

        $this->assertNull($body);
        $this->assertEquals(415, http_response_code());

    }

    // Contract: sendSuccessResponse sets 200 status and outputs data body when body is provided.
    public function testItSendsSuccessResponseWithBody(){

        ob_start();
        HttpUtils::sendSuccessResponse([], ['foo' => 'bar']);
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode(['data' => ['foo' => 'bar']]), $output);

    }

    // Contract: sendSuccessResponse sets 204 status when no body is provided.
    public function testItSendsNoContentResponseWithoutBody(){

        ob_start();
        HttpUtils::sendSuccessResponse([]);
        $output = ob_get_clean();

        $this->assertEquals(204, http_response_code());
        $this->assertEmpty($output);

    }

    // Contract: sendErrorResponse handles HttpCompatibleException by using its code and body.
    public function testItHandlesHttpCompatibleException(){

        $exception = new class(403) extends HttpCompatibleException{

            public $reason = 'blocked';

        };

        ob_start();
        HttpUtils::sendErrorResponse([], $exception);
        $output = ob_get_clean();

        $this->assertEquals(403, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode(['data' => ['reason' => 'blocked']]), $output);

    }

    // Contract: sendErrorResponse handles generic exceptions by returning 500.
    public function testItHandlesGenericException(){

        $exception = new ErrorException('Fatal error');

        ob_start();
        HttpUtils::sendErrorResponse([], $exception);
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
        $this->assertEmpty($output);

    }

    // Contract: sendErrorResponse exposes error details if log_errors is enabled.
    public function testItExposesErrorIfConfigured(){

        $exception = new ErrorException('Detailed error');

        ob_start();
        HttpUtils::sendErrorResponse(['log_errors' => true], $exception);
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
        $json = json_decode($output, true);
        
        $this->assertEquals('Detailed error', $json['data']['error']);

    }

}
?>
