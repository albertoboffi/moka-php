<?php

use MokaPHP\HttpCompatibleException;

class InvalidCoffeeStructureException extends HttpCompatibleException{

    public function __construct(){

        parent::__construct(422);
        
    }

}

?>