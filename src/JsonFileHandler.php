<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use rcsofttech85\FileHandler\Utilities\RowColumnHelper;

readonly class JsonFileHandler
{
    use RowColumnHelper;

    /**
     * @param string $filename
     * @param array<string> $headers
     * @param array<string>|false $hideColumns
     * @param int|false $limit
     * @return array<string,string>
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
     * @param array<string> $headers
     * @param array<string>|false $hideColumns
     * @param int|false $limit
     * @return Generator
     * @throws FileHandlerException
     */
    private function getRows(
        string $filename,
        array &$headers,
        array|false $hideColumns = false,
        int|false $limit = false
    ): Generator {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file not found');
        }
        $jsonContents = file_get_contents($filename);

        if (!$jsonContents) {
            throw new FileHandlerException("{$filename} is not valid");
        }


        if (!$contents = $this->isValidJson($jsonContents)) {
            throw new FileHandlerException(json_last_error_msg());
        }


        $count = 0;
        $headers = array_keys($contents[0]);
        $indices = is_array($hideColumns) ? $this->setColumnsToHide($headers, $hideColumns) : [];
        foreach ($contents as $content) {
            if (!empty($indices)) {
                $content = array_values($content);
                $this->removeElementByIndex($content, $indices);
            }
            yield $content;
            $count++;

            if (is_int($limit) && $limit <= $count) {
                break;
            }
        }
    }

    /**
     * @param string $jsonData
     * @return array<int,array<string,string>>|false
     */
    private function isValidJson(string $jsonData): array|false
    {
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }


        if (!is_array($data)) {
            return false;
        }

        if (!isset($data[0]) || !is_array($data[0])) {
            return false;
        }

        $firstArrayKeys = array_keys($data[0]);

        foreach ($data as $item) {
            $currentArrayKeys = array_keys($item);

            if ($firstArrayKeys !== $currentArrayKeys) {
                return false;
            }
        }

        return $data;
    }
}
