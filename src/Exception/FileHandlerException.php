<?php

namespace Rcsofttech85\FileHandler\Exception;

use Exception;
use Throwable;

class FileHandlerException extends Exception
{
    public function __construct(string $message = "There was an error", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
