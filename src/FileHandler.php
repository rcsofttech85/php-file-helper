<?php

namespace rcsofttech85\FileHandler;

use finfo;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use ZipArchive;

class FileHandler
{
    const ARRAY_FORMAT = 'array';

    private array $files = [];


    /**
     * @throws FileHandlerException
     */
    public function open(
        string $filename,
        string $mode = "r+",
        bool $include_path = false,
        $context = null
    ): self {
        $file = fopen($filename, $mode, $include_path, $context);

        if (!$file) {
            throw new FileHandlerException('File not found');
        }

        $this->files[] = $file;

        return $this;
    }


    /**
     * @throws FileHandlerException
     */
    public function write(string $data, ?int $length = null): void
    {
        foreach ($this->files as $file) {
            $byteWritten = fwrite($file, $data, $length);
            if (!$byteWritten) {
                throw new FileHandlerException('Error writing to file');
            }
        }
    }

    /**
     * @throws FileHandlerException
     */
    public function compress(string $filename, string $zipFilename): void
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('File to compress does not exist.');
        }

        $zip = new ZipArchive();

        if (!$zip->open($zipFilename, ZipArchive::CREATE)) {
            throw new FileHandlerException('Failed to create the ZIP archive.');
        }

        if (!$zip->addFile($filename)) {
            throw new FileHandlerException('Failed to add the file to the ZIP archive.');
        }


        $zip->close();
    }

    /**
     * @throws FileHandlerException
     */
    public function getMimeType(string $filename): string
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file does not exist.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($filename);
        if (!$mimeType) {
            throw new FileHandlerException('unknown mime type');
        }

        return $mimeType;
    }

    /**
     * @throws FileHandlerException
     */
    public function decompress(string $zipFilename, string $extractPath = "./"): void
    {
        if (!file_exists($zipFilename)) {
            throw new FileHandlerException('ZIP archive does not exist.');
        }

        $zip = new ZipArchive();

        if (!$zip->open($zipFilename)) {
            throw new FileHandlerException('Failed to open the ZIP archive.');
        }

        if (!$zip->extractTo($extractPath)) {
            throw new FileHandlerException('Failed to extract the ZIP archive.');
        }

        $zip->close();
    }

    /**
     * @throws FileHandlerException
     */
    public function close(): void
    {
        foreach ($this->files as $file) {
            if (!fclose($file)) {
                throw new FileHandlerException('file was not closed');
            }
        }
    }

    /**
     * @throws FileHandlerException
     */
    public function delete(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file does not exists');
        }
        unlink($filename);
    }


    public function ensureSingleFileProcessing(string|null $filename): mixed
    {
        if (empty($this->files)) {
            if ($filename && file_exists($filename)) {
                $this->open($filename);
                return $this->files[0];
            }

            throw new FileHandlerException("No files to process or file not found: $filename");
        }

        if (count($this->files) > 1) {
            throw new FileHandlerException("Multiple files not allowed");
        }

        return $this->files[0];
    }
}
