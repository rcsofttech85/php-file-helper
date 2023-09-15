<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\FileHandlerException;

class CsvFileHandler
{
    public function __construct(
        private readonly FileHandler $fileHandler,
        private readonly TempFileHandler $tempFileHandler
    ) {
    }

    public function findAndReplaceInCsv(
        string $filename,
        string $keyword,
        string $replace,
        string|null $column = null
    ): bool {
        $headers = $this->extractHeader($filename);


        if (!$headers) {
            throw new FileHandlerException('failed to extract header');
        }

        $tempFilePath = $this->tempFileHandler->createTempFileWithHeaders($headers);

        try {
            $count = 0;
            foreach ($this->getRows($filename) as $row) {
                $count += (!$column)
                    ? $this->replaceKeywordInRow($row, $keyword, $replace)
                    : $this->replaceKeywordInColumn($row, $column, $keyword, $replace);

                $this->tempFileHandler->writeRowToTempFile($tempFilePath, $row);
            }


            if ($count < 1) {
                return false;
            }

            $this->tempFileHandler->renameTempFile($tempFilePath, $filename);
        } finally {
            $this->tempFileHandler->cleanupTempFile($tempFilePath);
        }

        return true;
    }

    private function extractHeader(mixed $file): array|false
    {
        if (is_resource($file)) {
            $headers = fgetcsv($file);
        }
        if (is_string($file)) {
            if (!file_exists($file)) {
                return false;
            }
            try {
                $file = fopen($file, 'r');
                $headers = fgetcsv($file);
            } finally {
                fclose($file);
            }
        }

        if ($this->isValidCsvFileFormat($headers) !== false) {
            return $headers;
        }

        return false;
    }

    private function isValidCsvFileFormat(array|false $row): void
    {
        if (!$row || count($row) <= 1) {
            throw new FileHandlerException('invalid file format');
        }
    }

    private function getRows(string|null $filename = null): Generator
    {
        $file = $this->fileHandler->ensureSingleFileProcessing($filename);
        $headers = $this->extractHeader($file);

        $isEmptyFile = true;
        try {
            while (($row = fgetcsv($file)) !== false) {
                $isEmptyFile = false;
                $this->isValidCsvFileFormat($row);
                $item = array_combine($headers, $row);

                yield $item;
            }
        } finally {
            fclose($file);
        }


        if ($isEmptyFile) {
            throw new FileHandlerException('invalid file format');
        }
    }

    private function replaceKeywordInRow(array &$row, string $keyword, string $replace): int
    {
        $count = 0;
        $replacement = array_search($keyword, $row);

        if ($replacement !== false) {
            $row[$replacement] = $replace;
            $count++;
        }

        return $count;
    }

    private function replaceKeywordInColumn(array &$row, string $column, string $keyword, string $replace): int
    {
        $count = 0;

        if ($keyword === $row[$column]) {
            $row[$column] = $replace;
            $count++;
        }

        return $count;
    }

    public function searchInCsvFile(
        string $filename,
        string $keyword,
        string $column,
        string|null $format = null
    ): bool|array {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file not found');
        }
        $this->fileHandler->open($filename);

        foreach ($this->getRows() as $row) {
            if ($keyword === $row[$column]) {
                return ($format === FileHandler::ARRAY_FORMAT) ? $row : true;
            }
        }
        return false;
    }

    public function toJson(string $filename): string
    {
        $data = $this->toArray($filename);

        return json_encode($data);
    }

    public function toArray(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file not found');
        }
        $this->fileHandler->open($filename);
        return iterator_to_array($this->getRows());
    }
}
