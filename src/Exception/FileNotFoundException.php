<?php

namespace rcsofttech85\FileHandler\Exception;

class FileNotFoundException extends \Exception
{

    public function __construct($message = "File not found", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}