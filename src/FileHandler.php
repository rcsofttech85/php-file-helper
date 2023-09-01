<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\CouldNotWriteFileException;
use rcsofttech85\FileHandler\Exception\FileNotClosedException;
use rcsofttech85\FileHandler\Exception\FileNotFoundException;
use rcsofttech85\FileHandler\Exception\InvalidFileException;

class FileHandler
{

    private array $files = [];

    public function open(
        string $filename,
        string $mode = "r+",
        bool $include_path = false,
        $context = null
    ): self {
        $file = fopen($filename, $mode, $include_path, $context);

        if (!$file) {
            throw new FileNotFoundException();
        }

        $this->files[] = $file;

        return $this;
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

    public function searchInCsvFile(string $keyword, int $offset = 0): bool
    {
        return $this->search($keyword, $offset);
    }

    private function getRows(): Generator
    {
        foreach ($this->files as $file) {
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) < 2 || !is_array($row)) {
                    throw new InvalidFileException();
                }
                yield $row;
            }
            fclose($file);
        }
    }

    private function search(string $keyword, int $offset): bool
    {
        foreach ($this->getRows() as $row) {
            if ($keyword === $row[$offset]) {
                return true;
            }
        }
        return false;
    }

}