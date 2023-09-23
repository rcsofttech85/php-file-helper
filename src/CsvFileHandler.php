<?php

namespace Rcsofttech85\FileHandler;

use Generator;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Utilities\RowColumnHelper;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;

class CsvFileHandler
{
    use RowColumnHelper;
    use FileValidatorTrait;

    public function __construct(
        private readonly TempFileHandler $tempFileHandler
    ) {
    }

    /**
     * @param string $filename
     * @param string $keyword
     * @param string $column
     * @param string|null $format
     * @return bool|array<string,string>
     * @throws FileHandlerException
     */
    public function searchInCsvFile(
        string $filename,
        string $keyword,
        string $column,
        string|null $format = null
    ): bool|array {
        foreach ($this->getRows($filename) as $row) {
            if ($keyword === $row[$column]) {
                return ($format === FileHandler::ARRAY_FORMAT) ? $row : true;
            }
        }
        return false;
    }

    /**
     * @param string $filename
     * @return string|false
     * @throws FileHandlerException
     */

    public function toJson(string $filename): string|false
    {
        $data = $this->toArray($filename);

        return json_encode($data);
    }

    /**
     * @param string $filename
     * @param array<string> $hideColumns
     * @param int|false $limit
     * @return array<int,array<string,string>>
     * @throws FileHandlerException
     */
    public function toArray(string $filename, array|false $hideColumns = false, int|false $limit = false): array
    {
        return iterator_to_array($this->getRows($filename, $hideColumns, $limit));
    }

    /**
     * @param string $filename
     * @param string $keyword
     * @param string $replace
     * @param string|null $column
     * @return bool
     * @throws FileHandlerException
     */
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
        if (!$tempFilePath) {
            return false;
        }


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

    /**
     * @param mixed $file
     * @return array<string>|false
     */

    private function extractHeader(mixed $file): array|false
    {
        $headers = [];
        if (is_resource($file)) {
            $headers = fgetcsv($file);
        }
        if (is_string($file)) {
            $file = fopen($file, 'r');
            if (!$file) {
                return false;
            }
            $headers = fgetcsv($file);
            fclose($file);
        }

        if (!$headers) {
            return false;
        }

        if (!$this->isValidCsvFileFormat($headers)) {
            return false;
        }


        return $headers;
    }

    /**
     * @param array<string> $row
     * @return bool
     */
    private function isValidCsvFileFormat(array $row): bool
    {
        if (count($row) <= 1) {
            return false;
        }
        return true;
    }


    /**
     * @param string $filename
     * @param array<string>|false $hideColumns
     * @param int|false $limit
     * @return Generator
     * @throws FileHandlerException
     */
    private function getRows(string $filename, array|false $hideColumns = false, int|false $limit = false): Generator
    {
        $filename = $this->validateFileName($filename);
        $csvFile = fopen($filename, 'r');
        if (!$csvFile) {
            throw new FileHandlerException('file not found');
        }

        $headers = $this->extractHeader($csvFile);
        if (!is_array($headers)) {
            fclose($csvFile);
            throw new FileHandlerException('could not extract header');
        }

        $indices = is_array($hideColumns) ? $this->setColumnsToHide($headers, $hideColumns) : [];

        $isEmptyFile = true;
        $count = 0;

        try {
            while (($row = fgetcsv($csvFile)) !== false) {
                $isEmptyFile = false;
                if (!$this->isValidCsvFileFormat($row)) {
                    throw new FileHandlerException('invalid csv file format');
                }

                if (!empty($indices)) {
                    $this->removeElementByIndex($row, $indices);
                }

                $item = array_combine($headers, $row);
                yield $item;
                $count++;

                if (is_int($limit) && $limit <= $count) {
                    break;
                }
            }
        } finally {
            fclose($csvFile);
        }

        if ($isEmptyFile) {
            throw new FileHandlerException('invalid csv file format');
        }
    }


    /**
     * @param array<string> $row
     * @param string $keyword
     * @param string $replace
     * @return int
     */
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

    /**
     * @param array<string> $row
     * @param string $column
     * @param string $keyword
     * @param string $replace
     * @return int
     * @throws FileHandlerException
     */
    private function replaceKeywordInColumn(array &$row, string $column, string $keyword, string $replace): int
    {
        if (!array_key_exists($column, $row)) {
            throw new FileHandlerException("invalid column name");
        }
        $count = 0;

        if ($keyword === $row[$column]) {
            $row[$column] = $replace;
            $count++;
        }

        return $count;
    }
}
