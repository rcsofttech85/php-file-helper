<?php

namespace unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\CsvFileHandler;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use rcsofttech85\FileHandler\FileHandler;
use rcsofttech85\FileHandler\TempFileHandler;

class CsvFileHandlerTest extends TestCase
{
    private CsvFileHandler|null $csvFileHandler = null;

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

    public static function provideMovieNames(): iterable
    {
        yield ["Zack and Miri Make a Porno"];
        yield ["Youth in Revolt"];
        yield ["Twilight"];
    }

    public static function provideStudioNames(): iterable
    {
        yield ["The Weinstein Company"];
        yield ["Independent"];
    }

    #[Test]
    #[DataProvider('provideMovieNames')]
    #[TestDox('search result with name $keyword exists in file.')]
    public function resultFoundForExactNameMatch(string $keyword)
    {
        $isMovieAvailable = $this->csvFileHandler->searchInCsvFile(
            filename: "movie.csv",
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
        $isStudioFound = $this->csvFileHandler->searchInCsvFile(
            filename: "movie.csv",
            keyword: $keyword,
            column: 'Lead Studio'
        );
        $this->assertTrue($isStudioFound);
    }

    #[Test]
    public function toArrayMethodReturnsValidArray()
    {
        $data = $this->csvFileHandler->toArray("movie.csv");
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
        $jsonData = $this->csvFileHandler->toJson("movie.csv");

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

        $data = $this->csvFileHandler->searchInCsvFile(
            filename: "movie.csv",
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
            $this->csvFileHandler->searchInCsvFile(
                filename: $file,
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
        $tempHandler = new TempFileHandler();
        $csvFileHandler = new CsvFileHandler($fileHandler, $tempHandler);

        $hasReplaced = $csvFileHandler->findAndReplaceInCsv("movie.csv", "Twilight", "Inception", "Film");

        $this->assertTrue($hasReplaced);


        $data = $this->csvFileHandler->searchInCsvFile("movie.csv", "Inception", "Film", FileHandler::ARRAY_FORMAT);

        $this->assertEquals($data["Film"], "Inception");
    }


    /////////////////////////// DATA PROVIDER ////////////////////////////////

    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextWithoutColumnOption()
    {
        $fileHandler = new FileHandler();
        $tempHandler = new TempFileHandler();
        $csvFileHandler = new CsvFileHandler($fileHandler, $tempHandler);


        $hasReplaced = $csvFileHandler->findAndReplaceInCsv("movie.csv", "Inception", "Twilight");

        $this->assertTrue($hasReplaced);


        $data = $this->csvFileHandler->searchInCsvFile("movie.csv", "Twilight", "Film", FileHandler::ARRAY_FORMAT);

        $this->assertEquals($data["Film"], "Twilight");
    }

    protected function setUp(): void
    {
        parent::setUp();
        $fileHandler = new FileHandler();
        $tempFileHandler = new TempFileHandler();
        $this->csvFileHandler = new CsvFileHandler($fileHandler, $tempFileHandler);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->csvFileHandler = null;
    }
}
