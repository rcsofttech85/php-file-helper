<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use rcsofttech85\FileHandler\FileHandler;
use TypeError;

class FileHandlerTest extends TestCase
{
    private FileHandler|null $fileHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler = new FileHandler();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $content = "Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year\n"
            . "Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,$41.94 ,2008\n"
            . "Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,$19.62 ,2010\n"
            . "Twilight,Romance,Independent,68,6.383363636,26,$702.17 ,2011";

        fopen(filename: "file", mode: "w");
        fopen(filename: "file1", mode: "w");
        file_put_contents('movie.csv', $content);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $files = ["file", "movie.csv", 'file1'];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink(filename: $file);
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

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
        $this->fileHandler->open(filename: 'unknown');
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
    public function fileIsClosedProperly()
    {
        $this->fileHandler->open(filename: 'file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();

        $this->expectException(TypeError::class);
        $this->fileHandler->write(data: "fwrite(): supplied resource is not a valid stream resource");
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
}
