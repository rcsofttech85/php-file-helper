<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use rcsofttech85\FileHandler\Container;
use rcsofttech85\FileHandler\Exception\FileEncryptorException;
use rcsofttech85\FileHandler\FileEncryptor;

class FileEncryptorTest extends BaseTest
{
    private FileEncryptor|null $fileEncryptor = null;


    protected function setUp(): void
    {
        $this->fileEncryptor = Container::getService('file_encryptor');
        parent::setUp();
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
    public function throwExceptionOnDecryptingNonEncryptedFile()
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is not encrypted');
        $this->fileEncryptor->decryptFile();
    }

    #[Test]
    public function canEncryptFile()
    {
        $isFileEncrypted = $this->fileEncryptor->encryptFile();

        $this->assertTrue($isFileEncrypted);
    }

    #[Test]
    public function throwExceptionIfAlreadyEncrypted()
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is already encrypted');
        $this->fileEncryptor->encryptFile();
    }

    #[Test]
    public function throwExceptionIfDecryptionFails()
    {
        $fileEncryptor = new FileEncryptor('movie.csv', 'wrong');

        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('could not decrypt file');
        $fileEncryptor->decryptFile();
    }

    #[Test]
    public function canDecryptFile()
    {
        $isFileDecrypted = $this->fileEncryptor->decryptFile();

        $this->assertTrue($isFileDecrypted);
    }
}
