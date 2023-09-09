<?php

namespace Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\StreamException;
use rcsofttech85\FileHandler\Stream;

#[Group("integration")]
class StreamTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        fopen("output.html", "w");
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink("output.html");
    }

    #[Test]
    #[DataProvider('streamDataProvider')]
    public function streamAndWriteToFile($output, $url)
    {
        $stream = new Stream($url, $output);
        $stream->startStreaming();

        $this->assertGreaterThan(0, filesize($output));
        $this->assertStringContainsString('<!DOCTYPE html>', file_get_contents($output));
        $this->assertStringContainsString('</html>', file_get_contents($output));
    }

    #[Test]
    #[DataProvider('wrongStreamDataProvider')]
    public function throwExceptionIfUrlIsInvalid($output, $url)
    {
        $stream = new Stream($url, $output);

        $this->expectException(StreamException::class);
        $stream->startStreaming();
    }

    public static function streamDataProvider(): iterable
    {
        yield [
            "output.html",
            "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt"
        ];
    }

    public static function wrongStreamDataProvider(): iterable
    {
        yield ["output.html", "https://gist.github"];
    }
}
