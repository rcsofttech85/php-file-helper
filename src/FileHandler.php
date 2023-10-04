<?php

namespace Rcsofttech85\FileHandler;

use finfo;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;
use ZipArchive;

class FileHandler
{
    use FileValidatorTrait;

    public const ARRAY_FORMAT = 'array';

    /**
     * @var array<resource>|null
     */
    private null|array $files = [];


    /**
     * @throws FileHandlerException
     */
    public function open(
        string $filename,
        string $mode = "w",
        bool $include_path = false,
        mixed $context = null
    ): self {
        $filename = $this->sanitize($filename);
        $file = fopen($filename, $mode, $include_path, $context);

        if (!$file) {
            throw new FileHandlerException('File not found');
        }

        $this->files[] = $file;

        return $this;
    }


    /**
     * @param string $data
     * @param int<0, max>|null $length
     * @return void
     * @throws FileHandlerException
     */
    public function write(string $data, ?int $length = null): void
    {
        if (!$this->files) {
            throw new FileHandlerException('no files available to write');
        }
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
    public function compress(string $filename, string $zipFilename, int $flag = ZipArchive::CREATE): void
    {
        $filename = $this->validateFileName($filename);

        $zip = new ZipArchive();


        if (true !== $zip->open($zipFilename, $flag)) {
            throw new FileHandlerException('Failed to create the ZIP archive.');
        }

        $zip->addFile($filename);
        $zip->close();
    }

    /**
     * @throws FileHandlerException
     */
    public function getMimeType(string $filename): string|false
    {
        $filename = $this->validateFileName($filename);


        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($filename);
        if ($mimeType === 'application/octet-stream') {
            throw new FileHandlerException('unknown mime type');
        }

        return $mimeType;
    }

    /**
     * @throws FileHandlerException
     */
    public function decompress(string $zipFilename, string $extractPath = "./", int $flag = ZipArchive::CREATE): void
    {
        $zipFilename = $this->validateFileName($zipFilename);

        $zip = new ZipArchive();

        if (true !== $zip->open($zipFilename, $flag)) {
            throw new FileHandlerException('Invalid or uninitialized Zip object');
        }


        if (!$zip->extractTo($extractPath)) {
            throw new FileHandlerException('Failed to extract the ZIP archive.');
        }

        $zip->close();
    }


    public function close(): void
    {
        foreach ($this->files as $file) {
            fclose($file);
        }
        $this->resetFiles();
    }

    public function resetFiles(): void
    {
        $this->files = null;
    }

    /**
     * @throws FileHandlerException
     */
    public function delete(string $filename): void
    {
        $filename = $this->validateFileName($filename);

        unlink($filename);
    }
}
