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
     * @return string
     */
    public function createTempFileWithHeaders(array $headers): string|false
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile_');
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
}
