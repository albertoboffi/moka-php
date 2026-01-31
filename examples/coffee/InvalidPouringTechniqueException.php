<?php

use MokaPHP\HttpCompatibleException;

class InvalidPouringTechniqueException extends HttpCompatibleException{

    protected $msg;

    public function __construct(){

        $this->msg = "Boolean 'type' parameter expected";
        parent::__construct(422);
        
    }

}

?>