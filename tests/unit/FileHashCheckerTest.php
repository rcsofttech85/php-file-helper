<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use rcsofttech85\FileHandler\CsvFileHandler;
use rcsofttech85\FileHandler\Exception\HashException;
use rcsofttech85\FileHandler\FileHashChecker;
use Symfony\Component\Dotenv\Dotenv;

class FileHashCheckerTest extends BaseTest
{
    private static string $file;
    private FileHashChecker|null $fileHash = null;


    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHash = self::$containerBuilder->get('file_hash');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileHash = null;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $dotenv = new Dotenv();
        $dotenv->load('.env');

        self::$file = $_ENV['FILE_NAME'];
        static::$files = ['movie.csv', 'sample'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    #[Test]
    public function shouldGenerateValidHashForDifferentAlgo()
    {
        $expectedHash = "5923032f7e18edf69e1a3221be3205ce658ec0e4fb274016212a09a804240683";

        $actualHash = $this->fileHash->hashFile(); //default ALGO_256

        $this->assertEquals($expectedHash, $actualHash);

        $expectedHash = "1050bcc2d7d840d634f067a22abb4cd693b1f2590849982e29a6f9bb28963f73392b63ea24ae17edfaa500ee62b9e5482b9648af0b2b7d941992af3b0f9cbd3b";

        $actualHash = $this->fileHash->hashFile(FileHashChecker::ALGO_512);

        $this->assertEquals($expectedHash, $actualHash);
    }

    #[Test]
    public function checkFileIntegrityReturnsTrueIfHashMatch()
    {
        $isVerified = $this->fileHash->verifyHash(storedHashesFile: self::$file);

        $this->assertTrue($isVerified);
    }

    #[Test]
    public function shouldReturnFalseIfFileIsModified()
    {
        $backup = file_get_contents("movie.csv");
        file_put_contents("movie.csv", "modified", FILE_APPEND);

        $isVerified = $this->fileHash->verifyHash(self::$file);

        $this->assertfalse($isVerified);

        file_put_contents("movie.csv", $backup);
    }

    #[Test]
    public function shouldReturnFalseIfDifferentAlgoIsUsedForVerifyHash()
    {
        $isVerified = $this->fileHash->verifyHash(self::$file, FileHashChecker::ALGO_512);

        $this->assertFalse($isVerified);
    }

    #[Test]
    public function shouldThrowExceptionIfFileIsNotHashed()
    {
        /** @var CsvFileHandler $csvFile */
        $csvFile = self::$containerBuilder->get('csv_file_handler');
        file_put_contents("sample", "this file is not hashed");
        $this->fileHash = new FileHashChecker("sample", $csvFile);
        $this->expectException(HashException::class);
        $this->expectExceptionMessage("this file is not hashed");
        $this->fileHash->verifyHash(self::$file, FileHashChecker::ALGO_512);
    }
}
