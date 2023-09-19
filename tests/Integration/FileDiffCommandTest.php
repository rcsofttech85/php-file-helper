<?php

namespace Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group("integration")]
class FileDiffCommandTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unlink('new');
        unlink('old');
    }

    /**
     * @return iterable<array<string>>
     */
    public static function commandArgumentProvider(): iterable
    {
        file_put_contents("old", "this is old file" . PHP_EOL, FILE_APPEND);
        file_put_contents("new", "this is new file" . PHP_EOL, FILE_APPEND);

        file_put_contents("old", "this line has some difference" . PHP_EOL, FILE_APPEND);
        file_put_contents("new", "this line has same old code" . PHP_EOL, FILE_APPEND);


        yield ['old', 'new', 'old (Line 1):'];
        yield ['old', 'new', 'old (Line 2):'];
    }

    /**
     * @return iterable<array<string>>
     */

    public static function fileWithSameDataProvider(): iterable
    {
        file_put_contents("old", "this has matching content" . PHP_EOL, FILE_APPEND);
        file_put_contents("new", "this has matching content" . PHP_EOL, FILE_APPEND);


        yield ['old', 'new', 'old (Line 3):'];
    }

    #[Test]
    #[DataProvider('commandArgumentProvider')]
    public function fileDiffShowsCorrectChanges(string $oldFile, string $newFile, string $expected): void
    {
        $command = "php bin/file-diff $oldFile $newFile";
        exec($command, $output, $exitCode);

        $actualOutput = implode("\n", $output);

        $this->assertStringContainsString($expected, $actualOutput);


        $this->assertSame(0, $exitCode);
    }

    #[Test]
    public function throwsExceptionIfArgumentIsNotValidFile(): void
    {
        $command = "php bin/file-diff unknown unknown";
        exec($command, $output, $exitCode);

        $actualOutput = implode("\n", $output);
        $expectedOutput = "file does not exists";


        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString($expectedOutput, $actualOutput);
    }

    #[Test]
    #[DataProvider('fileWithSameDataProvider')]
    public function sameContentShouldNotBeDisplayedInTheResult(string $oldFile, string $newFile, string $expected): void
    {
        $command = "php bin/file-diff $oldFile $newFile";
        exec($command, $output, $exitCode);

        $actualOutput = implode("\n", $output);

        $expected = "Old (Line 3)";

        $this->assertStringNotContainsString($expected, $actualOutput);
        $this->assertSame(0, $exitCode);
    }
}
