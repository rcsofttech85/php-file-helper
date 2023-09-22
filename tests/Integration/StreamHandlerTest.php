<?php

namespace Integration;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\StreamException;
use Rcsofttech85\FileHandler\StreamHandler;
use Throwable;

#[Group("integration")]
class StreamHandlerTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ["output.html", "output1.html", "output2.html"];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * @param array<string,string> $urls
     * @return void
     * @throws StreamException
     * @throws Throwable
     */
    #[Test]
    #[DataProvider('streamDataProvider')]
    public function streamAndWriteToFile(array $urls): void
    {
        $stream = new StreamHandler($urls);
        $stream->initiateConcurrentStreams()->start()->resume();

        $files = array_keys($urls);
        foreach ($files as $file) {
            $data = $this->isFileValid($file);
            $this->assertGreaterThan(0, filesize($file));
            $this->assertStringContainsString('<!DOCTYPE html>', $data);
            $this->assertStringContainsString('</html>', $data);
        }
    }


    #[Test]
    #[DataProvider('wrongStreamDataProvider')]
    public function throwExceptionIfUrlIsInvalid(string $outputFile, string $url): void
    {
        $stream = new StreamHandler([$outputFile => $url]);


        $this->expectException(StreamException::class);
        $stream->initiateConcurrentStreams()->start()->resume();
    }

    #[Test]
    public function throwExceptionIfEmptyDataProvided(): void
    {
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('No stream URLs provided.');
        new StreamHandler([]);
    }

    /**
     * @return iterable<array<string>>
     */
    public static function wrongStreamDataProvider(): iterable
    {
        yield ["output.html", "https://gist.github"];
    }


    /**
     * @return iterable<array<int,array<string,string>>>
     */
    public static function streamDataProvider(): iterable
    {
        yield [
            [
                "output.html" =>
                    "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt",

                "output1.html" =>
                    "https://gist.github.com/rcsofttech85/f71f2454b1fc40a077cda14ef3097385#file-gistfile1-txt",


                "output2.html" =>
                    "https://gist.github.com/rcsofttech85/79ab19f1502e72c95cfa97d5205fa47d#file-gistfile1-txt"
            ]
        ];
    }
}
