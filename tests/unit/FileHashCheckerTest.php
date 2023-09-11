<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\Exception\HashException;
use rcsofttech85\FileHandler\FileHandler;
use rcsofttech85\FileHandler\FileHashChecker;
use Symfony\Component\Dotenv\Dotenv;

class FileHashCheckerTest extends TestCase
{
    private static string $file;
    private FileHashChecker|null $fileHasher = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dotenv = new Dotenv();
        $dotenv->load('.env');

        $file = $_ENV['FILE_NAME']; // this file contains list of all hashes

        self::$file = $file;


        file_put_contents("test", "hello world");
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink("test");
        unlink("sample");
    }

    #[Test]
    public function shouldGenerateValidHashForDifferentAlgo()
    {
        $expectedHash = "644bcc7e564373040999aac89e7622f3ca71fba1d972fd94a31c3bfbf24e3938";

        $actualHash = $this->fileHasher->hashFile(); //default ALGO_256

        $this->assertEquals($expectedHash, $actualHash);

        $expectedHash = "840006653e9ac9e95117a15c915caab81662918e925de9e004f774ff82d7079a40d4d27b1b372657c61d46d470304c88c788b3a4527ad074d1dccbee5dbaa99a";

        $actualHash = $this->fileHasher->hashFile(FileHashChecker::ALGO_512);

        $this->assertEquals($expectedHash, $actualHash);
    }

    #[Test]
    public function checkFileIntegrityReturnsTrueIfHashMatch()
    {
        $isVerified = $this->fileHasher->verifyHash(new FileHandler(), self::$file);

        $this->assertTrue($isVerified);
    }

    #[Test]
    public function shouldReturnFalseIfFileIsModified()
    {
        $backup = file_get_contents("test");
        file_put_contents("test", "modified", FILE_APPEND);

        $isVerified = $this->fileHasher->verifyHash(new FileHandler(), self::$file);

        $this->assertfalse($isVerified);

        file_put_contents("test", $backup);
    }

    #[Test]
    public function shouldReturnFalseIfDifferentAlgoIsUsedForVerifyHash()
    {
        $isVerified = $this->fileHasher->verifyHash(new FileHandler(), self::$file, FileHashChecker::ALGO_512);

        $this->assertFalse($isVerified);
    }

    #[Test]
    public function shouldThrowExceptionIfFileIsNotHashed()
    {
        file_put_contents("sample", "this file is not hashed");
        $this->fileHasher = new FileHashChecker("sample");

        $this->expectException(HashException::class);
        $this->expectExceptionMessage("this file is not hashed");
        $this->fileHasher->verifyHash(new FileHandler(), self::$file, FileHashChecker::ALGO_512);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileHasher = new FileHashChecker("test");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileHasher = null;
    }
}
