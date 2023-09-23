<?php

namespace Rcsofttech85\FileHandler\Validator;

use Rcsofttech85\FileHandler\DependencyInjection\ServiceContainer;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;

trait FileValidatorTrait
{
    /**
     * @param string $filename
     * @param string|null $path
     * @return string
     * @throws FileHandlerException
     */
    public function validateFileName(string $filename, string|null $path = null): string
    {
        $container = (new ServiceContainer())->getContainerBuilder();

        $stored_hash_file = $container->getParameter('FILE_NAME');

        if ($filename != $stored_hash_file) {
            self::sanitize($filename);
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

    /**
     * @throws FileHandlerException
     */
    public function sanitize(string $filename): string
    {
        $pattern = '/^[a-zA-Z0-9_.-]+$/';
        if (!preg_match($pattern, $filename)) {
            throw new FileHandlerException('file not found');
        }

        return $filename;
    }
}
