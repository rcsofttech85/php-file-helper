<?php

namespace Integration;

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

        fopen("outputFile.html", "w");
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink("outputFile.html");
    }

    #[Test]
    public function streamAndWriteToFile()
    {
        $url = "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt";
        $stream = new Stream($url, "outputFile.html");
        $stream->startStreaming();

        $this->assertGreaterThan(0, filesize("outputFile.html"));
        $this->assertStringContainsString('<!DOCTYPE html>', file_get_contents("outputFile.html"));
        $this->assertStringContainsString('</html>', file_get_contents("outputFile.html"));
    }

    #[Test]
    public function throwExceptionIfUrlIsInvalid()
    {
        $url = "https://gist.github";
        $stream = new Stream($url, "outputFile.html");

        $this->expectException(StreamException::class);
        $stream->startStreaming();
    }
}
