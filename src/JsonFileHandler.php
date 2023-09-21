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

        $contents = json_decode($jsonContents, true);
        if (!$contents || json_last_error() !== JSON_ERROR_NONE) {
            throw new FileHandlerException(json_last_error_msg());
        }

        $count = 0;
        $headers = array_keys(reset($contents));
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
}
