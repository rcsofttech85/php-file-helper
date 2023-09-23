<?php

namespace Rcsofttech85\FileHandler;

use Rcsofttech85\FileHandler\Exception\HashException;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;

class FileHashChecker
{
    use FileValidatorTrait;

    public const ALGO_256 = 'sha3-256';
    public const ALGO_512 = 'sha3-512';

    /**
     * @param string $filename
     * @param CsvFileHandler $csvFileHandler
     * @throws Exception\FileHandlerException
     */
    public function __construct(private string $filename, private readonly CsvFileHandler $csvFileHandler)
    {
        $this->filename = $this->validateFileName($filename);
    }

    /**
     * @param string $storedHashesFile
     * @param string $algo
     * @return bool
     * @throws Exception\FileHandlerException
     * @throws HashException
     */

    public function verifyHash(string $storedHashesFile, string $algo = self::ALGO_256): bool
    {
        if (!$storedHashesFile) {
            throw new HashException('file not found');
        }

        $file = $this->csvFileHandler->searchInCsvFile(
            filename: $storedHashesFile,
            keyword: $this->filename,
            column: 'File',
            format: FileHandler::ARRAY_FORMAT
        );

        if (!$file || !is_array($file)) {
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
