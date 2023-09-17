<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\FileHandlerException;

class CsvFileHandler
{
    public function __construct(
        private readonly TempFileHandler $tempFileHandler
    ) {
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


        foreach ($this->getRows($filename) as $row) {
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

        return iterator_to_array($this->getRows($filename));
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
        $headers = [];
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

        if (!$headers) {
            return false;
        }

        if (!$this->isValidCsvFileFormat($headers)) {
            return false;
        }


        return $headers;
    }

    private function isValidCsvFileFormat(array $row): bool
    {
        if (count($row) <= 1) {
            return false;
        }
        return true;
    }

    private function getRows(string $filename): Generator
    {
        $csvFile = fopen($filename, 'r');
        $headers = $this->extractHeader($csvFile);


        $isEmptyFile = true;
        try {
            while (($row = fgetcsv($csvFile)) !== false) {
                $isEmptyFile = false;
                if (!$this->isValidCsvFileFormat($row)) {
                    throw new FileHandlerException('invalid csv file format');
                }
                $item = array_combine($headers, $row);

                yield $item;
            }
        } finally {
            fclose($csvFile);
        }


        if ($isEmptyFile) {
            throw new FileHandlerException('invalid csv file format');
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
}
