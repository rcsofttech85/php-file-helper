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
        static::$files[] = 'sample';


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

    #[Test]
    public function throwExceptionIfFileIsNotFound(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('abc is not valid');
        $this->jsonFileHandler->getValidJsonData('abc');
    }

    #[Test]
    public function throwExceptionIfFileIsDoesNotContainValidJson(): void
    {
        file_put_contents("sample", "hello");
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('could not decode json');
        $this->jsonFileHandler->getValidJsonData('sample');
        unlink('sample');
    }

    #[Test]
    public function throwExceptionIfJsonIsNotAList(): void
    {
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
                "titles": "1984",
                "authors": "George Orwell",
                "published_year": 1949
            }
        ]';
        file_put_contents("sample", $content);
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('Inconsistent JSON data');
        $this->jsonFileHandler->getValidJsonData('sample');
    }

    #[Test]
    public function setLimitWorkingProperly(): void
    {
        $headers = [];
        $data = $this->jsonFileHandler->getRows(filename: 'book.json', headers: $headers, limit: 2);

        $data = iterator_to_array($data);

        $this->assertSame(2, count($data));
    }

    #[Test]
    public function setHideColumnWorkingProperly(): void
    {
        $headers = [];
        $this->jsonFileHandler->getRows(
            filename: 'book.json',
            headers: $headers,
            hideColumns: ['title'],
            limit: 1
        );

        if (in_array('title', $headers)) {
            $this->fail('hide column is not working properly');
        }

        $this->assertTrue(true);
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
