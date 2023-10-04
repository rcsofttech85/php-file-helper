<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\FileHandler;
use ZipArchive;

class FileHandlerTest extends BaseTest
{
    private FileHandler|null $fileHandler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler = $this->setObjectHandler(FileHandler::class, 'file_handler');
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ["movie.csv", "file", 'file1', 'unknown_mime_type'];
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

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function fileSuccessfullyWritten(): void
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals(expected: "hello world", actual: file_get_contents(filename: 'file'));
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldThrowExceptionIfFileIsNotFound(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('File not found');
        $this->fileHandler->open(filename: 'unknown', mode: "r");
    }


    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldThrowExceptionIfFileIsNotWritable(): void
    {
        $this->fileHandler->open(filename: 'file', mode: 'r');

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Error writing to file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function successfulCompression(): void
    {
        $testFile = 'movie.csv';
        $compressedZip = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZip);

        $mimeType = $this->fileHandler->getMimeType($compressedZip);

        $this->assertFileExists($compressedZip);
        $this->assertEquals('application/zip', $mimeType);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldThrowExceptionIfZipArchiveIsUnableToCreateWhileDecompress(): void
    {
        $testFile = 'movie.csv';
        $compressedZip = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZip);

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Invalid or uninitialized Zip object');
        $this->fileHandler->decompress(zipFilename: 'compressed.zip', flag: 10);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldThrowExceptionIfZipArchiveHasInvalidExtractPathWhileDecompress(): void
    {
        $testFile = 'movie.csv';
        $compressedZip = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZip);

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Failed to extract the ZIP archive.');
        $this->fileHandler->decompress(zipFilename: 'compressed.zip', extractPath: '/abcd');
    }

    #[Test]
    public function shouldThrowExceptionIfZipArchiveIsUnableToCreate(): void
    {
        $testFile = 'movie.csv';
        $compressedZip = 'compressed.zip';
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Failed to create the ZIP archive.');
        $this->fileHandler->compress($testFile, $compressedZip, ZipArchive::ER_EXISTS);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */

    #[Test]
    public function getMimeTypeThrowsErrorIfMimeTypeIsUnrecognised(): void
    {
        file_put_contents("unknown_mime_type", "%%");

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('unknown mime type');
        $this->fileHandler->getMimeType('unknown_mime_type');
    }


    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function getMimeTypeFunctionReturnsCorrectInfo(): void
    {
        $csvFile = $this->fileHandler->getMimeType("movie.csv");
        $zipFile = $this->fileHandler->getMimeType("compressed.zip");

        $this->assertEquals("text/csv", $csvFile);
        $this->assertEquals('application/zip', $zipFile);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function successfulDecompression(): void
    {
        $compressedZip = 'compressed.zip';
        $extractPath = 'extracted_contents';

        $this->fileHandler->decompress($compressedZip, $extractPath);

        $expectedContent = <<<EOD
        Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year
        Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,\$41.94 ,2008
        Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,\$19.62 ,2010
        Twilight,Romance,Independent,68,6.383363636,26,\$702.17 ,2011
        EOD;

        $this->assertEquals($expectedContent, file_get_contents("./extracted_contents/movie.csv"));


        unlink($compressedZip);
        unlink("./extracted_contents/movie.csv");
        rmdir($extractPath);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function fileIsClosedProperly(): void
    {
        $this->fileHandler->open(filename: 'file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('no files available to write');
        $this->fileHandler->write(data: "hello");
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function multipleFileCanBeWrittenSimultaneously(): void
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->open(filename: 'file1');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals("hello world", file_get_contents(filename: 'file'));

        $this->assertEquals("hello world", file_get_contents(filename: 'file1'));
        $this->fileHandler->close();
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function checkFilesAreDeletedProperly(): void
    {
        file_put_contents("deleteFile", "");
        $this->fileHandler->delete("deleteFile");

        if (!file_exists("deleteFile")) {
            $this->assertTrue(true);
        }
    }
}
