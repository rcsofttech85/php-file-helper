<?php

namespace Rcsofttech85\FileHandler;

use Rcsofttech85\FileHandler\Exception\FileHandlerException;

class TempFileHandler
{
    public function renameTempFile(string $tempFilePath, string $filename): void
    {
        if (!rename($tempFilePath, $filename)) {
            throw new FileHandlerException('Failed to rename temp file');
        }
    }

    public function cleanupTempFile(string $tempFilePath): void
    {
        if (file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }
    }

    /**
     * @param string $tempFilePath
     * @param array<string> $row
     * @return void
     */
    public function writeRowToTempFile(string $tempFilePath, array $row): void
    {
        $tempFileHandle = fopen($tempFilePath, 'a');
        if ($tempFileHandle) {
            fputs($tempFileHandle, implode(',', $row) . PHP_EOL);
            fclose($tempFileHandle);
        }
    }

    /**
     * @param array<string> $headers
     * @param string|null $dirName
     * @param string|null $prefix
     * @return string|false
     */
    public function createTempFileWithHeaders(
        array $headers,
        string|null $dirName = null,
        string|null $prefix = null
    ): string|false {
        if (null === $dirName) {
            $dirName = sys_get_temp_dir();
        }
        if (null === $prefix) {
            $prefix = 'tempfile_';
        }

        $tempFilePath = $this->getTempName($dirName, $prefix);

        if (!$tempFilePath) {
            return false;
        }
        $tempFileHandle = fopen($tempFilePath, 'w');
        if ($tempFileHandle) {
            fputs($tempFileHandle, implode(',', $headers) . PHP_EOL);
            fclose($tempFileHandle);
        }


        return $tempFilePath;
    }

    public function getTempName(string $directory, string $prefix): string|false
    {
        if (!is_dir($directory)) {
            return false;
        }
        return tempnam($directory, $prefix);
    }
}
