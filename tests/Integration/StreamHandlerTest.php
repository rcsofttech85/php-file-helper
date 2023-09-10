<?php

namespace Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\StreamException;
use rcsofttech85\FileHandler\StreamHandler;

#[Group("integration")]
class StreamHandlerTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $files = ["output.html", "output1.html", "output2.html"];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    #[Test]
    #[DataProvider('streamDataProvider')]
    public function streamAndWriteToFile($urls)
    {
        $stream = new StreamHandler($urls);
        $stream->initiateConcurrentStreams()->start()->resume();

        foreach ($urls as $file => $url) {
            $this->assertGreaterThan(0, filesize($file));
            $this->assertStringContainsString('<!DOCTYPE html>', file_get_contents($file));
            $this->assertStringContainsString('</html>', file_get_contents($file));
        }
    }

    #[Test]
    #[DataProvider('wrongStreamDataProvider')]
    public function throwExceptionIfUrlIsInvalid($outputFile, $url)
    {
        $stream = new StreamHandler([$outputFile => $url]);


        $this->expectException(StreamException::class);
        $stream->initiateConcurrentStreams()->start()->resume();
    }

    #[Test]
    public function throwExceptionIfEmptyDataProvided()
    {
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('No stream URLs provided.');
        new StreamHandler([]);
    }

    public static function wrongStreamDataProvider(): iterable
    {
        yield ["output.html", "https://gist.github"];
    }


    public static function streamDataProvider(): iterable
    {
        yield [
            [
                "output.html" =>
                    "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt",

                "output1.html" => "https://gist.github.com/rcsofttech85/f71f2454b1fc40a077cda14ef3097385#file-gistfile1-txt",


                "output2.html" => "https://gist.github.com/rcsofttech85/79ab19f1502e72c95cfa97d5205fa47d#file-gistfile1-txt"
            ]
        ];
    }
}
