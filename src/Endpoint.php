<?php

require_once __DIR__ . '/HttpUtils.php';

set_error_handler(function($severity, $message, $file, $line) {

    throw new ErrorException($message, 0, $severity, $file, $line);

});

class Endpoint{

    private $callbacks;
    private $config;

    /**
     * 
     * @param string Path of the config file
     */
    public function __construct($config_file){

        $this->config = parse_ini_file($config_file, true)['api'];

    }

    /**
     * 
     * @param callable Function to execute on a GET request
     */
    public function get(callable $callback){

        $this->callbacks['GET'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a POST request
     */
    public function post(callable $callback){

        $this->callbacks['POST'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a PATCH request
     */
    public function patch(callable $callback){

        $this->callbacks['PATCH'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a PUT request
     */
    public function put(callable $callback){

        $this->callbacks['PUT'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a DELETE request
     */
    public function delete(callable $callback){

        $this->callbacks['DELETE'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a HEAD request
     */
    public function head(callable $callback){

        $this->callbacks['HEAD'] = $callback;

    }

    /**
     * 
     * @param callable Function to execute on a OPTIONS request
     */
    public function options(callable $callback){

        $this->callbacks['OPTIONS'] = $callback;

    }

    public function dispatch(){

        try{

            $method = HttpUtils::getMethod();
            $headers = HttpUtils::getHeaders();
            $body = HttpUtils::getBody($this->config);

            if (is_null($body)) return;

            $callback = $this->callbacks[$method];

            if (!$callback) return;

            $response_body = $callback($headers, $_GET, $body);
            HttpUtils::sendSuccessResponse($this->config, $response_body);

        }

        catch(ErrorException $exception){

            HttpUtils::sendErrorResponse($this->config, $exception);

        }

    }

}

?>