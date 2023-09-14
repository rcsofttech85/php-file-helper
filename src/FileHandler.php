<?php

namespace rcsofttech85\FileHandler;

use finfo;
use Generator;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use ZipArchive;

class FileHandler
{
    const ARRAY_FORMAT = 'array';

    private array $files = [];


    /**
     * @throws FileHandlerException
     */
    public function open(
        string $filename,
        string $mode = "r+",
        bool $include_path = false,
        $context = null
    ): self {
        $file = fopen($filename, $mode, $include_path, $context);

        if (!$file) {
            throw new FileHandlerException('File not found');
        }

        $this->files[] = $file;

        return $this;
    }


    /**
     * @throws FileHandlerException
     */
    public function write(string $data, ?int $length = null): void
    {
        foreach ($this->files as $file) {
            $byteWritten = fwrite($file, $data, $length);
            if (!$byteWritten) {
                throw new FileHandlerException('Error writing to file');
            }
        }
    }

    /**
     * @throws FileHandlerException
     */
    public function compress(string $filename, string $zipFilename): void
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('File to compress does not exist.');
        }

        $zip = new ZipArchive();

        if (!$zip->open($zipFilename, ZipArchive::CREATE)) {
            throw new FileHandlerException('Failed to create the ZIP archive.');
        }

        if (!$zip->addFile($filename)) {
            throw new FileHandlerException('Failed to add the file to the ZIP archive.');
        }


        $zip->close();
    }

    /**
     * @throws FileHandlerException
     */
    public function getMimeType(string $filename): string
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file does not exist.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($filename);
        if (!$mimeType) {
            throw new FileHandlerException('unknown mime type');
        }

        return $mimeType;
    }

    /**
     * @throws FileHandlerException
     */
    public function decompress(string $zipFilename, string $extractPath = "./"): void
    {
        if (!file_exists($zipFilename)) {
            throw new FileHandlerException('ZIP archive does not exist.');
        }

        $zip = new ZipArchive();

        if (!$zip->open($zipFilename)) {
            throw new FileHandlerException('Failed to open the ZIP archive.');
        }

        if (!$zip->extractTo($extractPath)) {
            throw new FileHandlerException('Failed to extract the ZIP archive.');
        }

        $zip->close();
    }

    /**
     * @throws FileHandlerException
     */
    public function close(): void
    {
        foreach ($this->files as $file) {
            if (!fclose($file)) {
                throw new FileHandlerException('file was not closed');
            }
        }
    }

    public function searchInCsvFile(string $keyword, string $column, string|null $format = null): bool|array
    {
        return $this->search($keyword, $column, $format);
    }

    /**
     * @throws FileHandlerException
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getRows());
    }


    /**
     * @throws FileHandlerException
     */
    public function toJson(): string
    {
        $data = $this->toArray();

        return json_encode($data);
    }

    /**
     * @throws FileHandlerException
     */
    public function delete(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new FileHandlerException('file does not exists');
        }
        unlink($filename);
    }

    /**
     * @throws FileHandlerException
     */
    private function getRows(string|null $filename = null): Generator
    {
        $file = $this->ensureSingleFileProcessing($filename);
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

    private function ensureSingleFileProcessing(string|null $filename): mixed
    {
        if (count($this->files) < 1) {
            if (!$filename || !file_exists($filename)) {
                throw new FileHandlerException("no files to process");
            }
            $this->open($filename);
        }
        if (count($this->files) > 1) {
            throw new FileHandlerException("multiple files not allowed");
        }
        return $this->files[0];
    }

    /**
     * @throws FileHandlerException
     */
    private function search(string $keyword, string $column, string|null $format): bool|array
    {
        foreach ($this->getRows() as $row) {
            if ($keyword === $row[$column]) {
                return ($format === self::ARRAY_FORMAT) ? $row : true;
            }
        }
        return false;
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

        $tempFilePath = $this->createTempFileWithHeaders($headers);

        try {
            $count = 0;
            foreach ($this->getRows($filename) as $row) {
                if (!$column) {
                    $count += $this->replaceKeywordInRow($row, $keyword, $replace);
                } else {
                    $count += $this->replaceKeywordInColumn($row, $column, $keyword, $replace);
                }

                $this->writeRowToTempFile($tempFilePath, $row);
            }

            if ($count < 1) {
                return false;
            }

            $this->renameTempFile($tempFilePath, $filename);
        } finally {
            $this->cleanupTempFile($tempFilePath);
        }

        return true;
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

    private function writeRowToTempFile(string $tempFilePath, array $row): void
    {
        $tempFileHandle = fopen($tempFilePath, 'a');
        fputs($tempFileHandle, implode(',', $row) . PHP_EOL);
        fclose($tempFileHandle);
    }

    private function renameTempFile(string $tempFilePath, string $filename): void
    {
        if (!rename($tempFilePath, $filename)) {
            throw new FileHandlerException('Failed to rename temp file');
        }
    }

    private function cleanupTempFile(string $tempFilePath): void
    {
        unlink($tempFilePath);
    }

    private function createTempFileWithHeaders(array $headers): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile_');
        $tempFileHandle = fopen($tempFilePath, 'w');
        fputs($tempFileHandle, implode(',', $headers) . PHP_EOL);
        fclose($tempFileHandle);

        return $tempFilePath;
    }


    /**
     * @throws FileHandlerException
     */
    private function isValidCsvFileFormat(array|false $row): void
    {
        if (!$row || count($row) <= 1) {
            throw new FileHandlerException('invalid file format');
        }
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
}
