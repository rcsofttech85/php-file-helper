<?php

namespace rcsofttech85\FileHandler;

use Fiber;
use rcsofttech85\FileHandler\Exception\StreamException;
use Throwable;

readonly class Stream
{
    public function __construct(public string $streamUrl, public string $outputFilename)
    {
    }

    /**
     * @throws StreamException
     */
    public function startStreaming(): void
    {
        $fiber = new Fiber(function ($streamUrl): void {
            while (!feof($streamUrl)) {
                $contents = fread($streamUrl, 100);
                Fiber::suspend($contents);
            }
        });

        $stream = fopen($this->streamUrl, 'r');
        if (!$stream) {
            throw new StreamException();
        }
        stream_set_blocking($stream, false);

        $outputFile = fopen($this->outputFilename, 'w');

        try {
            $content = $fiber->start($stream);
            while (!$fiber->isTerminated()) {
                fwrite($outputFile, $content);

                $content = $fiber->resume();
            }
        } catch (Throwable $e) {
            throw new StreamException();
        } finally {
            fclose($stream);
            fclose($outputFile);
        }
    }
}
