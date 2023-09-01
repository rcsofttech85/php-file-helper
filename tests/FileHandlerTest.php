<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\CouldNotWriteFileException;
use rcsofttech85\FileHandler\Exception\FileNotFoundException;
use rcsofttech85\FileHandler\Exception\InvalidFileException;
use rcsofttech85\FileHandler\FileHandler;

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
    public function file_successfully_written()
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals(expected: "hello world", actual: file_get_contents(filename: 'file'));
    }

    #[Test]
    public function should_throw_exception_if_file_is_not_Found()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->fileHandler->open(filename: 'unknown');
    }

    #[Test]
    public function should_throw_exception_if_file_is_not_writable()
    {
        $this->fileHandler->open(filename: 'file', mode: 'r');

        $this->expectException(CouldNotWriteFileException::class);
        $this->expectExceptionMessage('Error writing to file');
        $this->fileHandler->write(data: "hello world");
    }

    #[Test]
    public function multiple_file_can_be_written_simultaneously()
    {
        $this->fileHandler->open(filename: 'file');

        $this->fileHandler->open(filename: 'file1', mode: 'w');

        $this->fileHandler->write(data: "hello world");

        $this->assertEquals("hello world", file_get_contents(filename: 'file'));

        $this->assertEquals("hello world", file_get_contents(filename: 'file1'));

        unlink("file1");
    }


    #[Test]
    public function file_is_closed_properly()
    {
        $this->fileHandler->open(filename: 'file');
        $this->fileHandler->write(data: "hello world");
        $this->fileHandler->close();

        $this->expectException(TypeError::class);
        $this->fileHandler->write(data: "fwrite(): supplied resource is not a valid stream resource");
    }

    #[Test]
    #[DataProvider('provide_movie_names')]
    #[TestDox('Movie with name $keyword exists in collection.')]
    public function movie_is_found_for_exact_name_match(string $keyword)
    {
        $isMovieAvailable = $this->fileHandler->open(filename: 'movie.csv')->searchInCsvFile(keyword: $keyword);
        $this->assertTrue($isMovieAvailable);
    }

    #[Test]
    #[DataProvider('provide_studio_names')]
    #[TestDox('Studio with name $keyword exists in collection.')]
    public function studio_is_found_for_exact_name_match(string $keyword)
    {
        $isStudioFound = $this->fileHandler->open(filename: 'movie.csv')->searchInCsvFile(keyword: $keyword, offset: 2);
        $this->assertTrue($isStudioFound);
    }

    #[Test]
    public function should_throw_exception_if_not_valid_csv()
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage("not a valid csv file");
        $this->fileHandler->open(filename: 'invalid.csv')->searchInCsvFile(keyword: 'hello');
    }

    #[Test]
    public function to_array_method_returns_valid_array()
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

    public static function provide_studio_names(): iterable
    {
        yield ["Fox"];
        yield ["Universal"];
        yield ["Warner Bros."];
    }


    public static function provide_movie_names(): iterable
    {
        yield ["The Ugly Truth"];
        yield ["Leap Year"];
        yield ["Twilight"];
    }

}