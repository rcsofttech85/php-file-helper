<?php

namespace rcsofttech85\FileHandler;

use Generator;
use rcsofttech85\FileHandler\Exception\FileHandlerException;

readonly class JsonFileHandler
{
    /**
     * @return array<int,array<string,string>>
     * @throws FileHandlerException
     */
    public function toArray(string $filename): array
    {
        return iterator_to_array($this->getRows($filename));
    }

    /**
     * @return Generator
     * @throws FileHandlerException
     */
    private function getRows(string $filename): Generator
    {
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

        foreach ($contents as $content) {
            yield $content;
        }
    }
}
