<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileEncryptorException;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\FileEncryptor;
use SodiumException;

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
        unlink('test');
    }

    /**
     * @return void
     * @throws FileEncryptorException
     * @throws FileHandlerException
     * @throws SodiumException
     */
    #[Test]
    public function throwExceptionOnDecryptingNonEncryptedFile(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is not encrypted');
        $this->fileEncryptor->decryptFile('movie.csv');
    }


    /**
     * @return void
     * @throws FileEncryptorException
     */
    #[Test]
    public function canEncryptFile(): void
    {
        $isFileEncrypted = $this->fileEncryptor->encryptFile('movie.csv');

        $this->assertTrue($isFileEncrypted);
    }


    /**
     * @return void
     * @throws FileEncryptorException
     */
    #[Test]
    public function throwExceptionIfAlreadyEncrypted(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is already encrypted');
        $this->fileEncryptor->encryptFile('movie.csv');
    }


    /**
     * @return void
     * @throws FileEncryptorException
     */
    #[Test]
    public function throwExceptionIfFileHasNoContentWhileEncrypt(): void
    {
        file_put_contents("test", "");
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('File has no content');
        $this->fileEncryptor->encryptFile('test');
    }


    #[Test]
    public function throwExceptionIfCouldNotConvertHexToBin(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('could not convert hex to bin');
        $this->fileEncryptor->convertHexToBin('hello');
    }

    /**
     * @return void
     * @throws FileEncryptorException
     * @throws SodiumException
     * @throws FileHandlerException
     */
    #[Test]
    public function throwExceptionIfFileHasNoContent(): void
    {
        file_put_contents("test", "");
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('File has no content');
        $this->fileEncryptor->decryptFile('test');
    }


    /**
     * @return void
     * @throws FileEncryptorException
     * @throws FileHandlerException
     * @throws SodiumException
     */
    #[Test]
    public function throwExceptionIfDecryptionFails(): void
    {
        $filePath = '.env';
        $originalContent = file_get_contents($filePath);
        if (!$originalContent) {
            $this->fail('file not found');
        }
        $password = $_ENV[FileEncryptor::ENCRYPT_PASSWORD];
        $updatedContent = str_replace($password, 'pass', $originalContent);

        file_put_contents($filePath, $updatedContent);
        try {
            $this->expectException(FileEncryptorException::class);
            $this->expectExceptionMessage('could not decrypt file');
            $this->fileEncryptor->decryptFile('movie.csv');
        } finally {
            file_put_contents($filePath, $originalContent);
        }
    }

    /**
     * @return void
     * @throws FileEncryptorException
     * @throws FileHandlerException
     * @throws SodiumException
     */
    #[Test]
    public function canDecryptFile(): void
    {
        $isFileDecrypted = $this->fileEncryptor->decryptFile('movie.csv');

        $this->assertTrue($isFileDecrypted);
    }
}
