<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileEncryptorException;
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
     * @throws SodiumException
     */
    #[Test]
    public function throwExceptionOnDecryptingNonEncryptedFile(): void
    {
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('file is not encrypted');
        $this->fileEncryptor->decryptFile();
    }

    /**
     * @return void
     * @throws FileEncryptorException
     */
    #[Test]
    public function canEncryptFile(): void
    {
        $isFileEncrypted = $this->fileEncryptor->encryptFile();

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
        $this->fileEncryptor->encryptFile();
    }

    /**
     * @return void
     * @throws FileEncryptorException
     */
    #[Test]
    public function throwExceptionIfFileHasNoContentWhileEncrypt(): void
    {
        file_put_contents("test", "");
        $file = new FileEncryptor('test', 'pass');
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('File has no content');
        $file->encryptFile();
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
     */
    #[Test]
    public function throwExceptionIfFileHasNoContent(): void
    {
        file_put_contents("test", "");
        $file = new FileEncryptor('test', 'pass');
        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('File has no content');
        $file->decryptFile();
    }

    /**
     * @return void
     * @throws FileEncryptorException
     * @throws SodiumException
     */

    #[Test]
    public function throwExceptionIfDecryptionFails(): void
    {
        $fileEncryptor = new FileEncryptor('movie.csv', 'wrong');

        $this->expectException(FileEncryptorException::class);
        $this->expectExceptionMessage('could not decrypt file');
        $fileEncryptor->decryptFile();
    }

    /**
     * @return void
     * @throws FileEncryptorException
     * @throws SodiumException
     */
    #[Test]
    public function canDecryptFile(): void
    {
        $isFileDecrypted = $this->fileEncryptor->decryptFile();

        $this->assertTrue($isFileDecrypted);
    }
}
