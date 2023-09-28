<?php

namespace Rcsofttech85\FileHandler\Validator;

use Rcsofttech85\FileHandler\DependencyInjection\ServiceContainer;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;

trait FileValidatorTrait
{
    public const STORED_HASH_FILE = 'STORED_HASH_FILE';


    /**
     * @param string $filename
     * @param string|null $path
     * @return string
     * @throws FileHandlerException
     */
    public function validateFileName(string $filename, string|null $path = null): string
    {
        if (!$this->isFileSafe($filename, self::STORED_HASH_FILE)) {
            $this->sanitize($filename);
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

    /**
     * @throws FileHandlerException
     */
    private function isFileSafe(string $filename, string $envVariable): bool
    {
        $safeFile = $this->getParameter($envVariable);

        if ($safeFile !== $filename) {
            return false;
        }
        if (!file_exists($safeFile)) {
            throw new FileHandlerException('env variable does not contain a valid file path');
        }


        return true;
    }

    /**
     * @throws FileHandlerException
     */
    public function isFileRestricted(string $filename, string $envVariable): bool
    {
        return $this->isFileSafe($filename, $envVariable);
    }

    /**
     * @throws FileHandlerException
     */
    private function getParameter(string $param): string
    {
        $container = (new ServiceContainer())->getContainerBuilder();

        $parameter = $container->getParameter($param);

        if (!is_string($parameter)) {
            throw new FileHandlerException("{$param} is expected to be string");
        }

        return $parameter;
    }
}
