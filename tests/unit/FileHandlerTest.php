<?php

namespace unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\CouldNotWriteFileException;
use rcsofttech85\FileHandler\Exception\FileNotFoundException;
use rcsofttech85\FileHandler\Exception\InvalidFileException;
use rcsofttech85\FileHandler\FileHandler;
use TypeError;

class FileHandlerTest extends TestCase
{
    private FileHandler|null $fileHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler = new FileHandler();
        fopen(filename: "file", mode: "w");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->fileHandler = null;
        unlink(filename: "file");
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
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->fileHandler->open(filename: 'unknown');
    }

    #[Test]
    public function shouldThrowExceptionIfFileIsNotWritable()
    {
        $this->fileHandler->open(filename: 'file', mode: 'r');

        $this->expectException(CouldNotWriteFileException::class);
        $this->expectExceptionMessage('Error writing to file');
        $this->fileHandler->write(data: "hello world");
    }

    #[Test]
    public function multipleFileCanBeWrittenSimultaneously()
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->open(filename: 'file1', mode: 'w');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals("hello world", file_get_contents(filename: 'file'));

        $this->assertEquals("hello world", file_get_contents(filename: 'file1'));

        unlink("file1");
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
    #[DataProvider('fileProvider')]
    public function throwErrorIfFileFormatIsInvalid(string $file)
    {
        $this->expectException(InvalidFileException::class);
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

    public static function provideStudioNames(): iterable
    {
        yield ["Fox"];
        yield ["Universal"];
        yield ["Warner Bros."];
    }


    public static function provideMovieNames(): iterable
    {
        yield ["The Ugly Truth"];
        yield ["Leap Year"];
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
