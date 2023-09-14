<?php

namespace unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
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
    #[DataProvider('provideMovieNames')]
    #[TestDox('search result with name $keyword exists in file.')]
    public function resultFoundForExactNameMatch(string $keyword)
    {
        $isMovieAvailable = $this->fileHandler->open(filename: 'movie.csv')->searchInCsvFile(
            keyword: $keyword,
            column: 'Film'
        );
        $this->assertTrue($isMovieAvailable);
    }

    #[Test]
    #[DataProvider('provideStudioNames')]
    #[TestDox('search result with name $keyword exists in file.')]
    public function studioIsFoundForExactNameMatch(string $keyword)
    {
        $isStudioFound = $this->fileHandler->open(filename: 'movie.csv')->searchInCsvFile(
            keyword: $keyword,
            column: 'Lead Studio'
        );
        $this->assertTrue($isStudioFound);
    }


    #[Test]
    public function successfulCompression()
    {
        $testFile = 'movie.csv';
        $compressedZipFilename = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZipFilename);

        $mimeType = $this->fileHandler->getMimeType($compressedZipFilename);

        $this->assertFileExists($compressedZipFilename);
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
        $compressedZipFilename = 'compressed.zip';
        $extractPath = 'extracted_contents';

        $this->fileHandler->decompress($compressedZipFilename, $extractPath);

        $expectedContent = "Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year\n"
            . "Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,$41.94 ,2008\n"
            . "Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,$19.62 ,2010\n"
            . "Twilight,Romance,Independent,68,6.383363636,26,$702.17 ,2011";

        $this->assertEquals($expectedContent, file_get_contents("./extracted_contents/movie.csv"));


        unlink($compressedZipFilename);
        unlink("./extracted_contents/movie.csv");
        rmdir($extractPath);
    }

    #[Test]
    public function toArrayMethodReturnsValidArray()
    {
        $data = $this->fileHandler->open(filename: 'movie.csv')->toArray();
        $expected = [
            'Film' => 'Zack and Miri Make a Porno',
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Profitability' => '1.747541667',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',
            'Year' => '2008'

        ];

        $this->assertEquals($expected, $data[0]);
    }

    #[Test]
    public function toJsonMethodReturnsValidJsonFormat()
    {
        $jsonData = $this->fileHandler->open(filename: 'movie.csv')->toJson();

        $expectedData = '[{"Film":"Zack and Miri Make a Porno","Genre":"Romance","Lead Studio":"The Weinstein Company","Audience score %":"70","Profitability":"1.747541667","Rotten Tomatoes %":"64","Worldwide Gross":"$41.94 ","Year":"2008"},{"Film":"Youth in Revolt","Genre":"Comedy","Lead Studio":"The Weinstein Company","Audience score %":"52","Profitability":"1.09","Rotten Tomatoes %":"68","Worldwide Gross":"$19.62 ","Year":"2010"},{"Film":"Twilight","Genre":"Romance","Lead Studio":"Independent","Audience score %":"68","Profitability":"6.383363636","Rotten Tomatoes %":"26","Worldwide Gross":"$702.17 ","Year":"2011"}]';

        $this->assertJson($jsonData);
        $this->assertJsonStringEqualsJsonString($expectedData, $jsonData);
    }


    #[Test]
    public function searchByKeywordAndReturnArray()
    {
        $expected = [
            'Film' => 'Zack and Miri Make a Porno',
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Profitability' => '1.747541667',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',
            'Year' => '2008'

        ];

        $data = $this->fileHandler->open(filename: 'movie.csv')->searchInCsvFile(
            keyword: 'Zack and Miri Make a Porno',
            column: 'Film',
            format: FileHandler::ARRAY_FORMAT
        );

        $this->assertEquals($expected, $data);
    }

    #[Test]
    #[DataProvider('fileProvider')]
    public function throwErrorIfFileFormatIsInvalid(string $file)
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('invalid file format');

        try {
            $this->fileHandler->open($file)->searchInCsvFile(
                keyword: 'Twilight',
                column: 'Summit'
            );
        } finally {
            unlink($file);
        }
    }

    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextUsingColumnOption()
    {
        $fileHandler = new FileHandler();

        $hasReplaced = $fileHandler->findAndReplaceInCsv("movie.csv", "Twilight", "Inception", "Film");

        $this->assertTrue($hasReplaced);


        $data = $this->fileHandler->open("movie.csv")->searchInCsvFile("Inception", "Film", FileHandler::ARRAY_FORMAT);

        $this->assertEquals($data["Film"], "Inception");
    }

    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextWithoutColumnOption()
    {
        $fileHandler = new FileHandler();


        $hasReplaced = $fileHandler->findAndReplaceInCsv("movie.csv", "Inception", "Twilight");

        $this->assertTrue($hasReplaced);


        $data = $this->fileHandler->open("movie.csv")->searchInCsvFile("Twilight", "Film", FileHandler::ARRAY_FORMAT);

        $this->assertEquals($data["Film"], "Twilight");
    }

    //  Data Providers

    public static function provideStudioNames(): iterable
    {
        yield ["The Weinstein Company"];
        yield ["Independent"];
    }


    public static function provideMovieNames(): iterable
    {
        yield ["Zack and Miri Make a Porno"];
        yield ["Youth in Revolt"];
        yield ["Twilight"];
    }

    public static function fileProvider(): iterable
    {
        $file1 = 'file1.txt';
        $file2 = 'file2.txt';
        $file3 = 'file3.txt';

        file_put_contents($file1, "film,year");
        file_put_contents($file2, "film\nyear");
        file_put_contents($file3, "Film");


        yield [$file1];
        yield [$file2];
        yield [$file3];
    }
}
