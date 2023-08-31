<?php

namespace rcsofttech85\FileHandler;

use rcsofttech85\FileHandler\Exception\CouldNotWriteFileException;
use rcsofttech85\FileHandler\Exception\FileNotClosedException;
use rcsofttech85\FileHandler\Exception\FileNotFoundException;

class FileHandler
{

    private array $files = [];

    public function open(
        string $filename,
        string $mode = 'w',
        bool $include_path = false,
        $context = null
    ): mixed {
        $file = fopen($filename, $mode, $include_path, $context);

        if (!$file) {
            throw new FileNotFoundException();
        }

        $this->files[] = $file;

        return $file;
    }


    public function write(string $data, ?int $length = null): void
    {
        foreach ($this->files as $file) {
            $byteWritten = fwrite($file, $data, $length);
            if ($byteWritten !== false) {
                continue;
            }

            throw new CouldNotWriteFileException();
        }
    }

    public function close(): void
    {
        foreach ($this->files as $file) {
            if (!fclose($file)) {
                throw new FileNotClosedException();
            }
        }
    }

}