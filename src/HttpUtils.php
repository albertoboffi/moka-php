<?php

require_once __DIR__ . '/HttpCompatibleException.php';

class HttpUtils{

    /**
     * 
     * @return string HTTP method
     */
    public static function getMethod(): string{

        return $_SERVER['REQUEST_METHOD'];

    }

    /**
     * 
     * @return array Headers of the HTTP request
     */
    public static function getHeaders(): array{

        return getallheaders();

    }

    /**
     * 
     * @param array $config - API configuration
     * @return array Body of the HTTP request, if the content type is whitelisted
     */
    public static function getBody(array $config): ?array{

        $content_type = $_SERVER['CONTENT_TYPE'];

        // accepted content types

        if (str_starts_with($content_type, 'application/json'))
            
            return json_decode(

                file_get_contents('php://input'),
                true

            ) ?? [];

        if (str_starts_with($content_type, 'multipart/form-data'))

            return array_merge(
                $_POST,
                $_FILES
            );

        if (str_starts_with($content_type, 'application/x-www-form-urlencoded'))

            return $_POST;

        // if the content type is not whitelisted, send error

        self::setHeaders($config);
        http_response_code(415);

        return null;

    }

    /**
     * @param array $config - API configuration
     */
    private static function setHeaders(array $config): void{

        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, OPTIONS, HEAD');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        $extra_headers = $config['extra_headers']
            ? (', ' . $config['extra_headers'])
            : '';

        header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization' . $extra_headers);

        $config['origin'] && header('Access-Control-Allow-Origin: ' . $config['origin']);
        $config['allow_credentials'] && header('Access-Control-Allow-Credentials: true');

    }

    /**
     * 
     * @param array $body - Body of the HTTP response
     */
    private static function setBody(array $body): void{

        print_r(json_encode(

            [ 'data' => $body ]

        ));

    }

    /**
     * 
     * @param array [$body] - Body of the HTTP response
     * @param array $config - API configuration
     */
    public static function sendSuccessResponse(array $config, ?array $body = null): void{

        self::setHeaders($config);

        if (isset($body)){

            http_response_code(200);
            self::setBody($body);

            return;

        }

        http_response_code(204);

    }

    /**
     * 
     * @param Exception $exception - Exception causing the error
     * @param array $config - API configuration
     */
    public static function sendErrorResponse(array $config, Exception $exception): void{

        self::setHeaders($config);

        if ($exception instanceof HttpCompatibleException){

            http_response_code($exception->getCode());
            self::setBody($exception->getBody());

            return;

        }

        http_response_code(500);

    }

}

?>