<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\CsvFileHandler;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\FileHandler;

class CsvFileHandlerTest extends BaseTest
{
    private CsvFileHandler|null $csvFileHandler;


    protected function setUp(): void
    {
        parent::setUp();
        $this->csvFileHandler = $this->setObjectHandler(CsvFileHandler::class, 'csv_file_handler');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->csvFileHandler = null;
        unlink('temp');
    }

    #[Test]
    #[DataProvider("wrongColumnNameProvider")]
    public function throwExceptionIfWrongColumnNameProvided(string $columnName): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("invalid column name");
        $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Twilight", "hello", $columnName);
    }

    /**
     * @return void
     */
    #[Test]
    public function throwExceptionIfHeadersCouldNotBeExtracted(): void
    {
        file_put_contents("temp", "");
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('failed to extract header');
        $this->csvFileHandler->findAndReplaceInCsv("temp", "Twilight", "hello");
    }

    #[Test]
    public function throwExceptionIfFileFormatIsNotValid(): void
    {
        file_put_contents("temp", "File,Hash\nHello");
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('invalid csv file format');
        $this->csvFileHandler->findAndReplaceInCsv("temp", "Twilight", "hello");
    }

    #[Test]
    public function throwExceptionIfFilenameIsInvalid(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('failed to extract header');
        $this->csvFileHandler->findAndReplaceInCsv("temp", "Twilight", "hello");
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldReturnFalseIfKeywordIsNotMatched(): void
    {
        $result = $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Twil", "hello");
        $this->assertFalse(false);
    }

    #[Test]
    public function findAndReplaceShouldThrowErrorIfDirNameIsInValid(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("could not create temp file");
        $this->csvFileHandler->findAndReplaceInCsv(
            filename: 'movie.csv',
            keyword: 'hello',
            replace: 'how',
            dirName: '/ab'
        );
    }


    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextWithoutColumnOption(): void
    {
        $hasReplaced = $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Twilight", "Inception");

        $data = $this->isFileValid('movie.csv');

        $this->assertTrue($hasReplaced);
        $this->assertStringContainsString('Inception', $data);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function findAndReplaceInCsvMethodShouldReplaceTextUsingColumnOption(): void
    {
        $hasReplaced = $this->csvFileHandler->findAndReplaceInCsv("movie.csv", "Inception", "Twilight", "Film");

        $data = $this->isFileValid('movie.csv');
        $this->assertTrue($hasReplaced);
        $this->assertStringContainsString('Twilight', $data);
    }

    /**
     * @param string $keyword
     * @return void
     * @throws FileHandlerException
     */

    #[Test]
    #[DataProvider('provideMovieNames')]
    public function searchByKeyword(string $keyword): void
    {
        $isMovieAvailable = $this->csvFileHandler->searchInCsvFile(
            filename: "movie.csv",
            keyword: $keyword,
            column: 'Film'
        );
        $this->assertTrue($isMovieAvailable);
    }

    /**
     * @param string $keyword
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    #[DataProvider('provideStudioNames')]
    public function searchByStudioName(string $keyword): void
    {
        $isStudioFound = $this->csvFileHandler->searchInCsvFile(
            filename: "movie.csv",
            keyword: $keyword,
            column: 'Lead Studio'
        );
        $this->assertTrue($isStudioFound);
    }

    /**
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    public function toArrayMethodReturnsValidArray(): void
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

    /**
     * @param array<string> $columnsToHide
     * @param array<string,string> $expected
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    #[DataProvider('columnsToHideDataProvider')]
    public function toArrayMethodWithHideColumnsOptionReturnsValidArray(array $columnsToHide, array $expected): void
    {
        $data = $this->csvFileHandler->toArray("movie.csv", $columnsToHide);
        $this->assertEquals($expected, $data[0]);
    }

    /**
     * @param int $limit
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    #[DataProvider('limitDataProvider')]
    public function toArrayMethodShouldRestrictNumberOfRecordsWhenLimitIsSet(int $limit): void
    {
        $data = $this->csvFileHandler->toArray("movie.csv", ["Year"], $limit);

        $count = count($data);

        $this->assertSame($count, $limit);
    }

    #[Test]
    public function searchByKeywordAndReturnArray(): void
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
    public function toJsonMethodReturnsValidJsonFormat(): void
    {
        $jsonData = $this->csvFileHandler->toJson("movie.csv");
        if (!$jsonData) {
            $this->fail('Could not convert to JSON format');
        }

        $expectedData = '[
        {
            "Film": "Zack and Miri Make a Porno",
            "Genre": "Romance",
            "Lead Studio": "The Weinstein Company",
            "Audience score %": "70",
            "Profitability": "1.747541667",
            "Rotten Tomatoes %": "64",
            "Worldwide Gross": "$41.94 ",
            "Year": "2008"
        },
        {
            "Film": "Youth in Revolt",
            "Genre": "Comedy",
            "Lead Studio": "The Weinstein Company",
            "Audience score %": "52",
            "Profitability": "1.09",
            "Rotten Tomatoes %": "68",
            "Worldwide Gross": "$19.62 ",
            "Year": "2010"
        },
        {
            "Film": "Twilight",
            "Genre": "Romance",
            "Lead Studio": "Independent",
            "Audience score %": "68",
            "Profitability": "6.383363636",
            "Rotten Tomatoes %": "26",
            "Worldwide Gross": "$702.17 ",
            "Year": "2011"
        }
    ]';

        $this->assertJson($jsonData);
        $this->assertJsonStringEqualsJsonString($expectedData, $jsonData);
    }


    #[Test]
    #[DataProvider('fileProvider')]
    public function throwErrorIfFileFormatIsInvalid(string $file): void
    {
        $message = ($file === 'file1.txt') ? 'invalid csv file format' : 'could not extract header';
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionMessage($message);

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


    /**
     * @return iterable<array<string>>
     */
    public static function provideStudioNames(): iterable
    {
        yield ["The Weinstein Company"];
        yield ["Independent"];
    }

    /**
     * @return iterable<array<string>>
     */
    public static function provideMovieNames(): iterable
    {
        yield ["Zack and Miri Make a Porno"];
        yield ["Youth in Revolt"];
        yield ["Twilight"];
    }

    /**
     * @return iterable<array<string>>
     */

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

    /**
     * @return iterable<array<string>>
     */
    public static function wrongColumnNameProvider(): iterable
    {
        yield ["wrong"];
        yield ["honey bee"];
    }

    /**
     * @return iterable<array<array<string>>>
     */
    public static function columnsToHideDataProvider(): iterable
    {
        $hideSingleColumn = ["Film"];
        $expected1 = [
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Profitability' => '1.747541667',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',
            'Year' => '2008'

        ];

        $hideMultipleColumns = ["Film", "Profitability", "Year"];
        $expected2 = [
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',


        ];


        yield [$hideSingleColumn, $expected1];
        yield [$hideMultipleColumns, $expected2];
    }

    /**
     * max limit for the test file is 3
     * @return iterable<array<int>>
     */
    public static function limitDataProvider(): iterable
    {
        yield [1];
        yield [2];
    }
}
