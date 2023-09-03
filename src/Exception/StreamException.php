<?php

namespace rcsofttech85\FileHandler\Exception;

use Exception;
use Throwable;

class StreamException extends Exception
{
    public function __construct($message = "could not stream file", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
