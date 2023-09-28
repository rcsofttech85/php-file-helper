<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\JsonFileHandler;

class JsonFileHandlerTest extends BaseTest
{
    private JsonFileHandler|null $jsonFileHandler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files[] = 'book.json';

        $content = '[
            {
                "title": "The Catcher in the Rye",
                "author": "J.D. Salinger",
                "published_year": 1951
            },
            {
                "title": "To Kill a Mockingbird",
                "author": "Harper Lee",
                "published_year": 1960
            },
            {
                "title": "1984",
                "author": "George Orwell",
                "published_year": 1949
            }
        ]';

        file_put_contents("book.json", $content);
    }


    /**
     * @return array<int,array<int, array<int, array<string, string>>>>
     */
    public static function bookListProvider(): iterable
    {
        yield
        [

            [
                [
                    'title' => 'The Catcher in the Rye',
                    'author' => 'J.D. Salinger',
                    'published_year' => '1951'
                ],
                [
                    'title' => 'To Kill a Mockingbird',
                    'author' => 'Harper Lee',
                    'published_year' => '1960'
                ],
                [
                    'title' => '1984',
                    'author' => 'George Orwell',
                    'published_year' => '1949'
                ],


            ]

        ];
    }

    /**
     * @param array<int,array<string,string>> $book
     * @return void
     * @throws FileHandlerException
     */
    #[Test]
    #[DataProvider('bookListProvider')]
    public function jsonFormatToArray(array $book): void
    {
        $data = $this->jsonFileHandler->toArray('book.json');

        $this->assertSame(3, count($data));
        $this->assertEquals($data, $book);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonFileHandler = new JsonFileHandler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->jsonFileHandler = null;
    }
}
