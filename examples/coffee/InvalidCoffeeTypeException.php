<?php

use MokaPHP\HttpCompatibleException;

class InvalidCoffeeTypeException extends HttpCompatibleException{

    protected $msg;

    public function __construct(){

        $this->msg = "Wrong coffee type";
        parent::__construct(401);
        
    }

}

?>