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

    public function toArray(): array
    {
        if (count($this->files) > 2) {
            throw new InvalidFileException("multiple files not allowed");
        }

        $headers = fgetcsv($this->files[0]);

        $data = [];
        while (($row = fgetcsv($this->files[0])) !== false) {
            if (count($row) < 2 || !is_array($row)) {
                throw new InvalidFileException('not a valid csv file');
            }
            $item = array_combine($headers, $row);
            $data[] = $item;
        }

        return $data;
    }

    private function getRows(): Generator
    {
        foreach ($this->files as $file) {
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) < 2 || !is_array($row)) {
                    throw new InvalidFileException('not a valid csv file');
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