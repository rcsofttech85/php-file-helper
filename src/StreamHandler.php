<?php

namespace Rcsofttech85\FileHandler;

use Fiber;
use Rcsofttech85\FileHandler\Exception\StreamException;
use Throwable;

class StreamHandler
{
    /**
     * @var array<Fiber> $fibers
     */
    private array $fibers = [];


    /**
     * @param array<string,string> $streamUrls
     * @param int<0,100> $chunk
     * @throws StreamException
     */
    public function __construct(public readonly array $streamUrls, public readonly int $chunk = 100)
    {
        if (!$this->streamUrls) {
            throw new StreamException('No stream URLs provided.');
        }
    }


    private function stream(string $streamUrl, string $outputFilename): Fiber
    {
        return new Fiber(function () use ($streamUrl, $outputFilename) {
            $stream = fopen($streamUrl, 'r');
            $outputFile = fopen($outputFilename, 'w');
            if (!$stream || !$outputFile) {
                throw new StreamException("Failed to open stream: $streamUrl");
            }

            stream_set_blocking($stream, false);


            try {
                while (!feof($stream)) {
                    $length = $this->chunk;
                    $contents = fread($stream, $length);
                    if ($contents) {
                        fwrite($outputFile, $contents);
                    }

                    Fiber::suspend();
                }
            } finally {
                fclose($stream);
                fclose($outputFile);
            }
        });
    }

    public function initiateConcurrentStreams(): self
    {
        foreach ($this->streamUrls as $outputFile => $streamUrl) {
            $fiber = $this->stream($streamUrl, $outputFile);

            $this->fibers[] = $fiber;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws StreamException
     * @throws Throwable
     */
    public function start(): self
    {
        if (!$this->fibers) {
            throw new StreamException("No fibers available to start");
        }

        foreach ($this->fibers as $fiber) {
            $fiber->start();
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function resume(bool $resumeOnce = false): void
    {
        if (!$this->fibers) {
            throw new StreamException("No fibers are currently running");
        }

        foreach ($this->fibers as $fiber) {
            while (!$fiber->isTerminated()) {
                $fiber->resume();
                if ($resumeOnce) {
                    break;
                }
            }
        }

        $this->fibers = [];
    }

    public function resetFibers(): void
    {
        $this->fibers = [];
    }
}
