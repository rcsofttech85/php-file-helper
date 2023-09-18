<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use rcsofttech85\FileHandler\Exception\FileEncryptorException;
use rcsofttech85\FileHandler\FileEncryptor;

class FileEncryptorTest extends BaseTest
{
    private FileEncryptor|null $fileEncryptor = null;


    protected function setUp(): void
    {
        parent::setUp();
        $this->fileEncryptor = $this->setObjectHandler(FileEncryptor::class, 'file_encryptor');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileEncryptor = null;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$files = ['movie.csv'];
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }


    #[Test]
    public function throwExceptionOnDecryptingNonEncryptedFile(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is not encrypted');
        $this->fileEncryptor->decryptFile();
    }

    #[Test]
    public function canEncryptFile(): void
    {
        $isFileEncrypted = $this->fileEncryptor->encryptFile();

        $this->assertTrue($isFileEncrypted);
    }

    #[Test]
    public function throwExceptionIfAlreadyEncrypted(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is already encrypted');
        $this->fileEncryptor->encryptFile();
    }

    #[Test]
    public function throwExceptionIfDecryptionFails(): void
    {
        $fileEncryptor = new FileEncryptor('movie.csv', 'wrong');

        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('could not decrypt file');
        $fileEncryptor->decryptFile();
    }

    #[Test]
    public function canDecryptFile(): void
    {
        $isFileDecrypted = $this->fileEncryptor->decryptFile();

        $this->assertTrue($isFileDecrypted);
    }
}
