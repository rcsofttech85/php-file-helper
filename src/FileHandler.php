<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\CouldNotWriteFileException;
use rcsofttech85\FileHandler\Exception\FileNotClosedException;
use rcsofttech85\FileHandler\Exception\FileNotFoundException;
use rcsofttech85\FileHandler\Exception\InvalidFileException;

class FileHandler
{
    const ARRAY_FORMAT = 'array';

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

    public function searchInCsvFile(string $keyword, string $column, string|null $format = null): bool|array
    {
        return $this->search($keyword, $column, $format);
    }

    public function toArray(): array
    {
        return iterator_to_array($this->getRows());
    }

    private function getRows(): Generator
    {
        if (count($this->files) > 1) {
            throw new InvalidFileException("multiple files not allowed");
        }

        $file = $this->files[0];
        $headers = fgetcsv($file);
        while (($row = fgetcsv($file)) !== false) {
            $item = array_combine($headers, $row);
            yield $item;
        }
        fclose($file);
    }


    private function search(string $keyword, string $column, string|null $format): bool|array
    {
        foreach ($this->getRows() as $row) {
            if ($keyword === $row[$column]) {
                return ($format === self::ARRAY_FORMAT) ? $row : true;
            }
        }
        return false;
    }
}
