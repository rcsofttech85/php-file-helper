<?php

namespace Integration;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group("integration")]
class ViewCsvCommandTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$files[] = 'profile.csv';
        self::$files[] = 'invalidProfile.csv';
    }

    /**
     * @return iterable<array<string>>
     */
    public static function fileProvider(): iterable
    {
        $file = "profile.csv";
        $csvData = <<<CSV
        Name,Age,Location,Occupation
        John,30,New York,Engineer
        Alice,25,Los Angeles,Designer
        Bob,35,Chicago,Teacher
        Emma,28,San Francisco,Doctor
        Michael,40,Houston,Accountant
        Olivia,22,Miami,Student
        William,32,Seattle,Developer
        Sophia,27,Austin,Marketing
        Liam,33,Denver,Manager
        Ava,29,Phoenix,Writer
        CSV;
        file_put_contents($file, $csvData);

        yield [$file];
    }

    /**
     * @return iterable<array<string>>
     */
    public static function invalidFileProvider(): iterable
    {
        $file = "invalidProfile.csv";
        $csvData = <<<CSV
        Name
        Name Age
        CSV;
        file_put_contents($file, $csvData);

        yield [$file];
    }

    #[Test]
    #[DataProvider('fileProvider')]
    public function viewCsvFileDisplayInformationCorrectly(string $file): void
    {
        $command = "bin/view-csv {$file}";
        exec($command, $output, $exitCode);
        $actualOutput = implode("\n", $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($file, $actualOutput);
        unlink($file);
    }

    #[Test]
    public function ifLimitIsSetToNonNumericThenCommandShouldFail(): void
    {
        $limit = 'hello';
        $command = "bin/view-csv movie.csv --limit {$limit}";
        exec($command, $output, $exitCode);
        $actualOutput = implode("\n", $output);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString("{$limit} is not numeric", $actualOutput);
    }

    #[Test]
    #[DataProvider('InvalidFileProvider')]
    public function commandShouldReturnErrorIfFileIsInvalid(string $file): void
    {
        $command = "bin/view-csv {$file}";
        exec($command, $output, $exitCode);
        $actualOutput = implode("\n", $output);

        $this->assertStringContainsString('invalid csv file', $actualOutput);
        $this->assertSame(1, $exitCode);
        unlink($file);
    }

    #[Test]
    public function viewBlockedForRestrictedFile(): void
    {
        $restrictedFile = self::$containerBuilder->getParameter('STORED_HASH_FILE');
        if (!is_string($restrictedFile)) {
            $this->fail('restricted files is expected to be string');
        }
        $command = "bin/view-csv {$restrictedFile}";
        exec($command, $output, $exitCode);
        $actualOutput = implode("\n", $output);

        $this->assertStringContainsString("{$restrictedFile} does not exists", $actualOutput);
        $this->assertSame(1, $exitCode);
    }
}
