<?php

namespace Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ViewJsonCommandTest extends TestCase
{
    /**
     * @return iterable<array<string>>
     */
    public static function fileProvider(): iterable
    {
        $file = "book.json";
        $csvData = '[
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
        file_put_contents($file, $csvData);

        yield [$file];
    }

    #[Test]
    #[DataProvider('fileProvider')]
    public function viewJsonFileDisplayInformationCorrectly(string $file): void
    {
        $command = "php bin/view-json {$file}";
        exec($command, $output, $exitCode);
        $actualOutput = implode("\n", $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($file, $actualOutput);
        $this->assertStringContainsString('The Catcher in the Rye', $actualOutput);
        unlink($file);
    }
}
