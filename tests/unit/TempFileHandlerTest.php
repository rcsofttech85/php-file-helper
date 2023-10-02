<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\TempFileHandler;

class TempFileHandlerTest extends TestCase
{
    private TempFileHandler|null $tempFileHandler = null;

    /**
     * @return void
     * @throws FileHandlerException
     */
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
    public function getTempNameReturnFalseOnWrongValue(): void
    {
        $isTempFileValid = $this->tempFileHandler->createTempFileWithHeaders([], 'abcd', 'abcd');

        $this->assertFalse($isTempFileValid);
    }


    #[Test]
    public function createTempFileWithHeaders(): void
    {
        $headers = ['header1', 'header2', 'header3'];

        $tempFilePath = $this->tempFileHandler->createTempFileWithHeaders($headers);
        if (!$tempFilePath) {
            $this->fail('could not generate temp file with header');
        }

        $fileContents = file_get_contents($tempFilePath);
        $expectedContents = implode(',', $headers) . PHP_EOL;

        $this->assertEquals($expectedContents, $fileContents);

        unlink($tempFilePath);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function throwsExceptionIfRenameFails(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Failed to rename temp file');

        $this->tempFileHandler->renameTempFile('', '');
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFileHandler = new TempFileHandler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->tempFileHandler = null;
    }
}
