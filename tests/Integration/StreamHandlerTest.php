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
    private StreamHandler|null $streamHandler = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ["output.html", "output1.html", "output2.html", "profile"];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->streamHandler = null;
    }

    /**
     * @return void
     * @throws StreamException
     * @throws Throwable
     */
    #[Test]
    public function resumeAtOnceWorkingProperly(): void
    {
        $data = "<html><body>hello world</body></html>";
        file_put_contents("profile", $data);
        $this->streamHandler = new StreamHandler([
            "output.html" => "profile"

        ]);

        $this->streamHandler->initiateConcurrentStreams()->start()->resume(resumeOnce: true);
        $content = file_get_contents('output.html');
        if (!$content) {
            $this->fail('file has no content');
        }

        $this->assertStringContainsString($data, $content);
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
        $this->streamHandler = new StreamHandler($urls);
        $this->streamHandler->initiateConcurrentStreams()->start()->resume();

        $files = array_keys($urls);
        foreach ($files as $file) {
            $data = $this->isFileValid($file);
            $this->assertGreaterThan(0, filesize($file));
            $this->assertStringContainsString('<!DOCTYPE html>', $data);
            $this->assertStringContainsString('</html>', $data);
        }
    }

    /**
     * @param string $outputFile
     * @param string $url
     * @return void
     * @throws StreamException
     * @throws Throwable
     */

    #[Test]
    #[DataProvider('wrongStreamDataProvider')]
    public function throwExceptionIfUrlIsInvalid(string $outputFile, string $url): void
    {
        $this->streamHandler = new StreamHandler([$outputFile => $url]);


        $this->expectException(StreamException::class);
        $this->streamHandler->initiateConcurrentStreams()->start()->resume();
    }

    /**
     * @return void
     * @throws StreamException
     * @throws Throwable
     */

    #[Test]
    public function throwExceptionIfNoFibersAreAvailable(): void
    {
        $this->streamHandler = new StreamHandler([
            "output.html" =>
                "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt",

        ]);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('No fibers available to start');
        $this->streamHandler->resetFibers();
        $this->streamHandler->start();
    }


    /**
     * @return void
     * @throws StreamException
     * @throws Throwable
     */
    #[Test]
    public function throwExceptionIfNoFibersAreAvailableToResume(): void
    {
        $this->streamHandler = new StreamHandler([
            "output.html" =>
                "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt",

        ]);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('No fibers are currently running');
        $this->streamHandler->resetFibers();
        $this->streamHandler->resume();
    }

    /**
     * @return void
     * @throws StreamException
     */
    #[Test]
    public function throwExceptionIfEmptyDataProvided(): void
    {
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('No stream URLs provided.');
        $this->streamHandler = new StreamHandler([]);
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
