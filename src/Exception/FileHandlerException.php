<?php

namespace rcsofttech85\FileHandler\Exception;

use Exception;
use Throwable;

class FileHandlerException extends Exception
{
    public function __construct($message = "There was an error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
