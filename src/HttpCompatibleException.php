<?php

abstract class HttpCompatibleException extends ErrorException{

    /**
     * 
     * @param int HTTP status code
     */
    public function __construct(int $code = 400){

        parent::__construct('', $code, 0);

    }

    /**
     * 
     * @return array protected declared attributes of the subclass
     */
    public function getBody(): array{
        
        return array_diff_key(

            get_object_vars($this),
            get_class_vars('ErrorException')

        );

    }   

}

?>