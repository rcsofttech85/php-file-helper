<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use rcsofttech85\FileHandler\Container;
use rcsofttech85\FileHandler\CsvFileHandler;
use rcsofttech85\FileHandler\Exception\FileHandlerException;
use rcsofttech85\FileHandler\FileHandler;

class CsvFileHandlerTest extends BaseTest
{
    private CsvFileHandler|null $csvFileHandler = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ['movie.csv'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvFileHandler = Container::getService('csv_file_handler');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->csvFileHandler = null;
    }

    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextWithoutColumnOption()
    {
        $hasReplaced = $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Twilight", "Inception");

        $this->assertTrue($hasReplaced);
        $this->assertStringContainsString('Inception', file_get_contents('movie.csv'));
    }

    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextUsingColumnOption()
    {
        $hasReplaced = $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Inception", "Twilight", "Film");

        $this->assertTrue($hasReplaced);
        $this->assertStringContainsString('Twilight', file_get_contents('movie.csv'));
    }

    #[Test]
    #[DataProvider('provideMovieNames')]
    public function searchByKeyword(string $keyword)
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
    public function searchBystudioName(string $keyword)
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
    public function toJsonMethodReturnsValidJsonFormat()
    {
        $jsonData = $this->csvFileHandler->toJson("movie.csv");

        $expectedData = '[{"Film":"Zack and Miri Make a Porno","Genre":"Romance","Lead Studio":"The Weinstein Company","Audience score %":"70","Profitability":"1.747541667","Rotten Tomatoes %":"64","Worldwide Gross":"$41.94 ","Year":"2008"},{"Film":"Youth in Revolt","Genre":"Comedy","Lead Studio":"The Weinstein Company","Audience score %":"52","Profitability":"1.09","Rotten Tomatoes %":"68","Worldwide Gross":"$19.62 ","Year":"2010"},{"Film":"Twilight","Genre":"Romance","Lead Studio":"Independent","Audience score %":"68","Profitability":"6.383363636","Rotten Tomatoes %":"26","Worldwide Gross":"$702.17 ","Year":"2011"}]';

        $this->assertJson($jsonData);
        $this->assertJsonStringEqualsJsonString($expectedData, $jsonData);
    }


    #[Test]
    #[DataProvider('fileProvider')]
    public function throwErrorIfFileFormatIsInvalid(string $file)
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('invalid csv file format');

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
