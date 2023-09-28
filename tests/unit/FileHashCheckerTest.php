<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Exception\HashException;
use Rcsofttech85\FileHandler\FileHashChecker;

class FileHashCheckerTest extends BaseTest
{
    private FileHashChecker|null $fileHash = null;


    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHash = $this->setObjectHandler(FileHashChecker::class, 'file_hash');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileHash = null;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$files[] = 'sample';
    }


    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldGenerateValidHashForDifferentAlgo(): void
    {
        $expectedHash = "5923032f7e18edf69e1a3221be3205ce658ec0e4fb274016212a09a804240683";

        $actualHash = $this->fileHash->hashFile(filename: self::$files[0]); //default ALGO_256

        $this->assertEquals($expectedHash, $actualHash);

        $expectedHash = "1050bcc2d7d840d634f067a22abb4cd693b1f2590849982e29a6f9bb28963f733" .
            "92b63ea24ae17edfaa500ee62b9e5482b9648af0b2b7d941992af3b0f9cbd3b";

        $actualHash = $this->fileHash->hashFile(filename: self::$files[0], algo: FileHashChecker::ALGO_512);

        $this->assertEquals($expectedHash, $actualHash);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function checkFileIntegrityReturnsTrueIfHashMatch(): void
    {
        $isVerified = $this->fileHash->verifyHash(filename: self::$files[0]);

        $this->assertTrue($isVerified);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldReturnFalseIfFileIsModified(): void
    {
        $backup = file_get_contents("movie.csv");
        file_put_contents("movie.csv", "modified", FILE_APPEND);

        $isVerified = $this->fileHash->verifyHash(self::$files[0]);

        $this->assertfalse($isVerified);

        file_put_contents("movie.csv", $backup);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldReturnFalseIfDifferentAlgoIsUsedForVerifyHash(): void
    {
        $isVerified = $this->fileHash->verifyHash(self::$files[0], FileHashChecker::ALGO_512);

        $this->assertFalse($isVerified);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldAddRecordIfNewFileIsHashed(): void
    {
        file_put_contents('sample', "hello");

        $this->fileHash->hashFile(self::$files[1], FileHashChecker::ALGO_512);

        $isVerified = $this->fileHash->verifyHash(self::$files[1], FileHashChecker::ALGO_512);

        $this->assertTrue($isVerified);
    }
}
