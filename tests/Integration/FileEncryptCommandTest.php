<?php

namespace Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class FileEncryptCommandTest extends TestCase
{
    #[Test]
    public function fileIsEncryptedProperly(): void
    {
        file_put_contents("dummy.txt", "this content should get encrypted");

        $command = "bin/file-encrypt dummy.txt";
        $process = Process::fromShellCommandline($command);
        $process->run();


        $actualOutput = $process->getOutput();
        $exitCode = $process->getExitCode();

        $this->assertStringContainsString("The file encryption was successful", $actualOutput);
        $this->assertSame(0, $exitCode);
    }

    #[Test]
    public function fileIsDecryptedProperly(): void
    {
        $command = "bin/file-encrypt dummy.txt --mode=decryption";
        $process = Process::fromShellCommandline($command);
        $process->run();


        $actualOutput = $process->getOutput();
        $exitCode = $process->getExitCode();

        $this->assertStringContainsString("The file decryption was successful", $actualOutput);
        $this->assertSame(0, $exitCode);
    }
}
