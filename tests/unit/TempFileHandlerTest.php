<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Container;
use rcsofttech85\FileHandler\TempFileHandler;

class TempFileHandlerTest extends TestCase
{
    private TempFileHandler|null $tempFileHandler = null;

    #[Test]
    public function renameTempFile(): void
    {
        $tempFilePath = 'tempfile.txt';
        $newFileName = 'newfile.txt';


        $this->tempFileHandler->writeRowToTempFile($tempFilePath, []);

        $this->tempFileHandler->renameTempFile($tempFilePath, $newFileName);

        $this->assertFileExists($newFileName);
        $this->assertFileDoesNotExist($tempFilePath);

        unlink($newFileName);
    }

    #[Test]
    public function writeRowToTempFile(): void
    {
        $tempFilePath = 'tempfile.txt';
        $row = ['data', 'to', 'write'];


        $this->tempFileHandler->writeRowToTempFile($tempFilePath, $row);

        $fileContents = file_get_contents($tempFilePath);
        $expectedContents = implode(',', $row) . PHP_EOL;

        $this->assertEquals($expectedContents, $fileContents);

        unlink($tempFilePath);
    }

    #[Test]
    public function cleanupTempFile(): void
    {
        $tempFilePath = 'tempfile.txt';


        $this->tempFileHandler->writeRowToTempFile($tempFilePath, []);

        $this->tempFileHandler->cleanupTempFile($tempFilePath);

        $this->assertFileDoesNotExist($tempFilePath);
    }

    #[Test]
    public function createTempFileWithHeaders(): void
    {
        $headers = ['header1', 'header2', 'header3'];


        $tempFilePath = $this->tempFileHandler->createTempFileWithHeaders($headers);

        $fileContents = file_get_contents($tempFilePath);
        $expectedContents = implode(',', $headers) . PHP_EOL;

        $this->assertEquals($expectedContents, $fileContents);

        unlink($tempFilePath);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFileHandler = Container::getService('temp_file_handler');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->tempFileHandler = null;
    }
}
