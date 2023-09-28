<?php

namespace Rcsofttech85\FileHandler;

use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Exception\HashException;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;

class FileHashChecker
{
    use FileValidatorTrait;


    public const ALGO_256 = 'sha3-256';
    public const ALGO_512 = 'sha3-512';

    private const SEARCH_COLUMN_NAME = 'File';
    private const SEARCH_COLUMN_VALUE = 'Hash';


    /**
     * @param CsvFileHandler $csvFileHandler
     */
    public function __construct(private readonly CsvFileHandler $csvFileHandler)
    {
    }

    /**
     * @param string $filename
     * @param string $algo
     * @return bool
     * @throws FileHandlerException
     * @throws HashException
     */

    public function verifyHash(string $filename, string $algo = self::ALGO_256): bool
    {
        $storedHashesFile = $this->getParameter(self::STORED_HASH_FILE);
        $file = $this->csvFileHandler->searchInCsvFile(
            filename: $storedHashesFile,
            keyword: $filename,
            column: self::SEARCH_COLUMN_NAME,
            format: FileHandler::ARRAY_FORMAT
        );


        if (!$file || !is_array($file)) {
            throw new HashException('this file is not hashed');
        }

        $expectedHash = $file['Hash'];
        $hash = $this->hashFile($filename, $algo);


        if ($hash !== $expectedHash) {
            return false;
        }

        return true;
    }

    /**
     * @param string $filename
     * @param string $algo
     * @return string
     * @throws HashException|FileHandlerException
     */

    public function hashFile(string $filename, string $algo = self::ALGO_256): string
    {
        $this->validateFileName($filename);
        if (!in_array($algo, [self::ALGO_512, self::ALGO_256])) {
            throw new HashException('algorithm not supported');
        }

        if (!$hash = hash_file($algo, $filename)) {
            throw new HashException('could not hash file');
        }

        $storedHashesFile = $this->getParameter(self::STORED_HASH_FILE);


        $file = fopen($storedHashesFile, 'a+');
        if (!$file) {
            throw new FileHandlerException('file not found');
        }
        $this->checkHeaderExists($file);


        try {
            $filenameExists = $this->csvFileHandler->searchInCsvFile(
                filename: $storedHashesFile,
                keyword: $filename,
                column: 'File'
            );

            if (!$filenameExists) {
                fputcsv($file, [$filename, $hash]);
            }
        } catch (FileHandlerException) {
            fputcsv($file, [$filename, $hash]);
        } finally {
            fclose($file);
        }


        return $hash;
    }

    /**
     * @param mixed $storedHashFile
     * @return void
     */
    private function checkHeaderExists(mixed $storedHashFile): void
    {
        $header = fgetcsv($storedHashFile);

        if ($header === false
            || count($header) !== 2
            || $header[0] !== self::SEARCH_COLUMN_NAME
            || $header[1] !== self::SEARCH_COLUMN_VALUE
        ) {
            fseek($storedHashFile, 0);
            fputcsv($storedHashFile, [self::SEARCH_COLUMN_NAME, self::SEARCH_COLUMN_VALUE]);
            fflush($storedHashFile);
        }
    }
}
