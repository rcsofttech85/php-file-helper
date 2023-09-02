<?php

namespace rcsofttech85\FileHandler\Exception;

use Exception;
use Throwable;

class FileNotClosedException extends Exception
{
    public function __construct($message = "Failed to close file", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
