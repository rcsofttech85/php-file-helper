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
        static::$files[] = 'headers';
        static::$files[] = 'invalid';
    }


    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldGenerateValidHashForDifferentAlgo(): void
    {
        $expectedHash = "5923032f7e18edf69e1a3221be3205ce658ec0e4fb274016212a09a804240683";

        $actualHash = $this->fileHash->hashFile(filename: 'movie.csv'); //default ALGO_256

        $this->assertEquals($expectedHash, $actualHash);

        $expectedHash = "1050bcc2d7d840d634f067a22abb4cd693b1f2590849982e29a6f9bb28963f733" .
            "92b63ea24ae17edfaa500ee62b9e5482b9648af0b2b7d941992af3b0f9cbd3b";

        $actualHash = $this->fileHash->hashFile(filename: 'movie.csv', algo: FileHashChecker::ALGO_512);

        $this->assertEquals($expectedHash, $actualHash);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function checkFileIntegrityReturnsTrueIfHashMatch(): void
    {
        $isVerified = $this->fileHash->verifyHash(filename: 'movie.csv');

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

        $isVerified = $this->fileHash->verifyHash('movie.csv');

        $this->assertfalse($isVerified);

        file_put_contents("movie.csv", $backup);
    }

    /**
     * @return void
     * @throws FileHandlerException
     * @throws HashException
     */

    #[Test]
    public function verifyHashMethodThrowsExceptionIfHashRecordNotFound(): void
    {
        $this->expectException(HashException::class);
        $this->expectExceptionMessage('this file is not hashed');
        $isVerified = $this->fileHash->verifyHash(filename: 'movie');

        $this->assertTrue($isVerified);
    }

    /**
     * @return void
     * @throws FileHandlerException
     * @throws HashException
     */
    #[Test]
    public function verifyHashMethodThrowsExceptionIfInvalidFileProvided(): void
    {
        file_put_contents("invalid", "");
        chmod("invalid", 0000);
        $this->expectException(HashException::class);
        $this->expectExceptionMessage('could not hash file');
        $this->fileHash->hashFile(filename: 'invalid');
    }

    /**
     * @return void
     * @throws FileHandlerException
     * @throws HashException
     */
    #[Test]
    public function hashFileMethodThrowExceptionIfEnvVarIsNotFound(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage('file not found');
        $this->fileHash->hashFile(filename: 'movie.csv', env: 'INVALID_ENV_VAR');
    }


    /**
     * @return void
     * @throws FileHandlerException
     * @throws HashException
     */
    #[Test]
    public function hashFileMethodThrowExceptionIfInvalidCsvProvided(): void
    {
        $storedHashFile = self::$containerBuilder->getParameter('STORED_HASH_FILE');

        if (!is_string($storedHashFile)) {
            $this->fail('param must be a string type');
        }
        $backUpFile = file_get_contents($storedHashFile);


        file_put_contents($storedHashFile, "File,Hash");
        $this->fileHash->hashFile(filename: 'movie.csv');

        $content = file_get_contents($storedHashFile);
        if (!$content) {
            $this->fail('file has no content');
        }
        $this->assertStringContainsString("movie.csv", $content);

        file_put_contents($storedHashFile, $backUpFile);
    }

    /**
     * @return void
     */

    #[Test]
    public function checkHeaderExistIfNotShouldCreateOne(): void
    {
        file_put_contents("headers", "");
        $file = fopen("headers", 'w');
        $this->fileHash->checkHeaderExists($file);
        $content = file_get_contents("headers");
        if (!$content) {
            $this->fail('file has no content');
        }
        $this->assertStringContainsString('File,Hash', $content);
    }

    /**
     * @throws HashException
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldReturnFalseIfDifferentAlgoIsUsedForVerifyHash(): void
    {
        $isVerified = $this->fileHash->verifyHash('movie.csv', FileHashChecker::ALGO_512);

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

        $this->fileHash->hashFile('sample', FileHashChecker::ALGO_512);

        $isVerified = $this->fileHash->verifyHash('sample', FileHashChecker::ALGO_512);

        $this->assertTrue($isVerified);
    }

    /**
     * @return void
     * @throws FileHandlerException
     * @throws HashException
     */
    #[Test]
    public function shouldThrowExceptionIfInvalidAlgoProvided(): void
    {
        $this->expectException(HashException::class);
        $this->expectExceptionMessage('algorithm not supported');
        $this->fileHash->hashFile('sample', 'invalid');
    }
}
