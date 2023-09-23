<?php

namespace Rcsofttech85\FileHandler\Validator;

use Rcsofttech85\FileHandler\Exception\FileHandlerException;

class FileValidator
{

    /**
     * @param string $filename
     * @param string|null $path
     * @return string
     * @throws FileHandlerException
     */
    public static function validateFileName(string $filename, string|null $path = null): string
    {
        $pattern = '/^[a-zA-Z0-9_.-]+$/';
        if (!preg_match($pattern, $filename)) {
            throw new FileHandlerException('file not found');
        }


        if ($path) {
            $absolutePath = realpath($path);
            $absolutePath ?:
                throw new FileHandlerException("path {$path} is not valid')");
            $filename = $absolutePath . DIRECTORY_SEPARATOR . $filename;
        }


        if (!file_exists($filename)) {
            throw new FileHandlerException('file not found');
        }
        return $filename;
    }
}
