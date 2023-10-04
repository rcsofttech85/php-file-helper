<?php

namespace Rcsofttech85\FileHandler\Validator;

use Rcsofttech85\FileHandler\DependencyInjection\ServiceContainer;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * @param string $filename
     * @param string $envVariable
     * @return bool
     * @throws FileHandlerException
     */
    private function isFileSafe(string $filename, string $envVariable): bool
    {
        $safeFile = $this->getParameter($envVariable);


        if ($safeFile !== $filename) {
            return false;
        }

        return true;
    }

    /**
     * @param string $filename
     * @param string $envVariable
     * @return bool
     */
    public function isFileRestricted(string $filename, string $envVariable): bool
    {
        return $this->isFileSafe($filename, $envVariable);
    }

    /**
     * @param string $param
     * @return string
     * @throws FileHandlerException
     */
    private function getParameter(string $param): string
    {
        $container = (new ServiceContainer())->getContainerBuilder();
        return $this->getParam($container, $param);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $parameter
     * @return string
     * @throws FileHandlerException
     */
    public function getParam(ContainerBuilder $container, string $parameter): string
    {
        $param = $container->getParameter($parameter);
        if (!is_string($param)) {
            throw new FileHandlerException("{$parameter} is not string type");
        }

        return $param;
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return mixed
     * @throws FileHandlerException
     */
    public function openFileAndReturnResource(string $filename, string $mode = 'w'): mixed
    {
        $file = fopen($filename, $mode);
        if (!$file) {
            throw new FileHandlerException('file is not valid');
        }
        return $file;
    }
}
