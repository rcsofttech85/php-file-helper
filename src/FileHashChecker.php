<?php

namespace rcsofttech85\FileHandler;

use rcsofttech85\FileHandler\Exception\HashException;

class FileHashChecker
{
    const ALGO_256 = 'sha3-256';
    const ALGO_512 = 'sha3-512';

    /**
     * @param string $filename
     * @throws HashException
     */
    public function __construct(private readonly string $filename)
    {
        if (!file_exists($this->filename)) {
            throw new HashException('file not found');
        }
    }

    /**
     * @param object $fileHandler
     * @param string $storedHashesFile
     * @param string $algo
     * @return bool
     * @throws Exception\FileHandlerException
     * @throws HashException
     */

    public function verifyHash(object $fileHandler, string $storedHashesFile, string $algo = self::ALGO_256): bool
    {
        if (!$fileHandler instanceof FileHandler) {
            throw new HashException("object must be instance of " . FileHandler::class);
        }

        if (!$storedHashesFile) {
            throw new HashException('file not found');
        }

        $file = $fileHandler->open(filename: $storedHashesFile)->searchInCsvFile(
            $this->filename,
            'File',
            FileHandler::ARRAY_FORMAT
        );

        if (!$file) {
            throw new HashException('this file is not hashed');
        }

        $expectedHash = $file['Hash'];
        $hash = $this->hashFile($algo);

        if ($hash !== $expectedHash) {
            return false;
        }

        return true;
    }

    /**
     * @param string $algo
     * @return string
     * @throws HashException
     */

    public function hashFile(string $algo = self::ALGO_256): string
    {
        if (!in_array($algo, [self::ALGO_512, self::ALGO_256])) {
            throw new HashException('algorithm not supported');
        }
        return hash_file($algo, $this->filename);
    }
}
