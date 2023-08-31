<?php

namespace rcsofttech85\FileHandler\Exception;

class CouldNotWriteFileException extends \Exception
{
    public function __construct($message = "Error writing to file", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}