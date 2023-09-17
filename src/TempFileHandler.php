<?php

namespace rcsofttech85\FileHandler;

use rcsofttech85\FileHandler\Exception\FileHandlerException;

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

    public function writeRowToTempFile(string $tempFilePath, array $row): void
    {
        $tempFileHandle = fopen($tempFilePath, 'a');
        fputs($tempFileHandle, implode(',', $row) . PHP_EOL);
        fclose($tempFileHandle);
    }

    public function createTempFileWithHeaders(array $headers): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile_');
        $tempFileHandle = fopen($tempFilePath, 'w');
        fputs($tempFileHandle, implode(',', $headers) . PHP_EOL);
        fclose($tempFileHandle);

        return $tempFilePath;
    }
}
