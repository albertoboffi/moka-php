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

        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

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

        $extra_request_headers = !empty($config['extra_request_headers'])
            ? (', ' . $config['extra_request_headers'])
            : '';

        $csp_header = !empty($config['csp_report_only'])
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $csp_directives = !empty($config['csp_directives'])
            ? $config['csp_directives']
            : implode('; ', [

                "default-src 'self'",
                "script-src 'self'",
                "font-src 'self' https: data:",
                "connect-src 'self'",
                "media-src 'self'",
                "object-src 'none'",
                "frame-src 'self'",
                "frame-ancestors 'none'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' https: data: blob:",
                "upgrade-insecure-requests",
                "worker-src 'self'"

            ]);

        header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization' . $extra_request_headers);
        header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE, OPTIONS, HEAD');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: application/json');
        header('X-Frame-Options: DENY');

        ($config['origin'] ?? false) && header('Access-Control-Allow-Origin: ' . $config['origin']);
        ($config['allow_credentials'] ?? false) && header('Access-Control-Allow-Credentials: true');

        header($csp_header . ': ' . $csp_directives);

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