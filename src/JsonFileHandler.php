<?php

namespace Rcsofttech85\FileHandler;

use Generator;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Utilities\RowColumnHelper;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;

class JsonFileHandler
{
    use RowColumnHelper;
    use FileValidatorTrait;

    /**
     * @param string $filename
     * @param array<string> $headers
     * @param array<string>|false $hideColumns
     * @param int|false $limit
     * @return array<int,array<string,string>>
     * @throws FileHandlerException
     */
    public function toArray(
        string $filename,
        array &$headers = [],
        array|false $hideColumns = false,
        int|false $limit = false
    ): array {
        return iterator_to_array($this->getRows($filename, $headers, $hideColumns, $limit));
    }

    /**
     * @param string $filename
     * @return array<int,array<string,string>>
     * @throws FileHandlerException
     */

    private function validateFile(string $filename): array
    {
        $filename = $this->validateFileName($filename);
        $jsonContents = $this->getFileContents($filename);
        $contents = $this->parseJson($jsonContents);
        if (!$contents) {
            throw new FileHandlerException('could not parse json');
        }
        $this->validateJsonData($contents);
        return $contents;
    }


    /**
     * @param string $filename
     * @return string
     * @throws FileHandlerException
     */
    private function getFileContents(string $filename): string
    {
        $jsonContents = file_get_contents($filename);
        if (!$jsonContents) {
            throw new FileHandlerException("{$filename} is not valid");
        }
        return $jsonContents;
    }

    /**
     * @param string $jsonData
     * @return array<int,array<string,string>>|false
     */
    private function parseJson(string $jsonData): array|false
    {
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return false;
        }
        return $data;
    }

    /**
     * @param array<int,array<string,string>>|false $data
     * @return void
     * @throws FileHandlerException
     */
    private function validateJsonData(array|false $data): void
    {
        if (empty($data) || !is_array($data[0])) {
            throw new FileHandlerException(json_last_error_msg());
        }

        $firstArrayKeys = array_keys($data[0]);

        foreach ($data as $item) {
            $currentArrayKeys = array_keys($item);

            if ($firstArrayKeys !== $currentArrayKeys) {
                throw new FileHandlerException('Inconsistent JSON data');
            }
        }
    }

    /**
     * @param array<int,array<string,string>> $contents
     * @param array<int<0,max>,int> $indices
     * @param int|false $limit
     * @return Generator
     */
    private function getProcessedContent(array $contents, array $indices, int|false $limit = false): Generator
    {
        $count = 0;
        $shouldLimit = is_int($limit);

        foreach ($contents as $content) {
            if (!empty($indices)) {
                $content = array_values($content);
                $this->removeElementByIndex($content, $indices);
            }

            yield $content;
            $count++;

            if ($shouldLimit && $count >= $limit) {
                break;
            }
        }
    }

    /**
     * @param string $filename
     * @param array<string> $headers
     * @param array<string,string>|false $hideColumns
     * @param int|false $limit
     * @return Generator
     * @throws FileHandlerException
     */
    public function getRows(
        string $filename,
        array &$headers,
        array|false $hideColumns = false,
        int|false $limit = false
    ): Generator {
        $contents = $this->validateFile($filename);

        $headers = array_keys($contents[0]);
        $indices = is_array($hideColumns) ? $this->setColumnsToHide($headers, $hideColumns) : [];

        return $this->getProcessedContent($contents, $indices, $limit);
    }
}
