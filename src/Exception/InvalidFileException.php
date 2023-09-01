<?php

namespace rcsofttech85\FileHandler\Exception;

use Exception;
use Throwable;

class InvalidFileException extends Exception
{

    public function __construct($message = "invalid file format", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}