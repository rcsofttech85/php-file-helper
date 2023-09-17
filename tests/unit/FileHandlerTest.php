<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use rcsofttech85\FileHandler\Container;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use rcsofttech85\FileHandler\FileHandler;

class FileHandlerTest extends BaseTest
{
    private FileHandler|null $fileHandler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler = Container::getService('file_handler');
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ["movie.csv", "file", 'file1'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileHandler->resetFiles();
        $this->fileHandler = null;
    }


    #[Test]
    public function fileSuccessfullyWritten()
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals(expected: "hello world", actual: file_get_contents(filename: 'file'));
    }

    #[Test]
    public function shouldThrowExceptionIfFileIsNotFound()
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('File not found');
        $this->fileHandler->open(filename: 'unknown', mode: "r");
    }

    #[Test]
    public function shouldThrowExceptionIfFileIsNotWritable()
    {
        $this->fileHandler->open(filename: 'file', mode: 'r');

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Error writing to file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();
    }

    #[Test]
    public function successfulCompression()
    {
        $testFile = 'movie.csv';
        $compressedZip = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZip);

        $mimeType = $this->fileHandler->getMimeType($compressedZip);

        $this->assertFileExists($compressedZip);
        $this->assertEquals('application/zip', $mimeType);
    }

    #[Test]
    public function getMimeTypeFunctionReturnsCorrectInfo()
    {
        $csvFile = $this->fileHandler->getMimeType("movie.csv");
        $zipFile = $this->fileHandler->getMimeType("compressed.zip");

        $this->assertEquals("text/csv", $csvFile);
        $this->assertEquals('application/zip', $zipFile);
    }

    #[Test]
    public function successfulDecompression()
    {
        $compressedZip = 'compressed.zip';
        $extractPath = 'extracted_contents';

        $this->fileHandler->decompress($compressedZip, $extractPath);

        $expectedContent = "Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year\n"
            . "Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,$41.94 ,2008\n"
            . "Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,$19.62 ,2010\n"
            . "Twilight,Romance,Independent,68,6.383363636,26,$702.17 ,2011";

        $this->assertEquals($expectedContent, file_get_contents("./extracted_contents/movie.csv"));


        unlink($compressedZip);
        unlink("./extracted_contents/movie.csv");
        rmdir($extractPath);
    }

    #[Test]
    public function fileIsClosedProperly()
    {
        $this->fileHandler->open(filename: 'file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('no files available to write');
        $this->fileHandler->write(data: "hello");
    }

    #[Test]
    public function multipleFileCanBeWrittenSimultaneously()
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->open(filename: 'file1');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals("hello world", file_get_contents(filename: 'file'));

        $this->assertEquals("hello world", file_get_contents(filename: 'file1'));
        $this->fileHandler->close();
    }
}
