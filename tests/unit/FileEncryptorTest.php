<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\FileEncryptor;
use SodiumException;

class FileEncryptorTest extends TestCase
{
    private FileEncryptor|null $fileEncryptor;

    public static function setUpBeforeClass(): void
    {
        $content = "Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year\n"
            . "Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,$41.94 ,2008\n"
            . "Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,$19.62 ,2010\n"
            . "Twilight,Romance,Independent,68,6.383363636,26,$702.17 ,2011";

        file_put_contents('movie.csv', $content);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unlink("movie.csv");
    }

    #[Test]
    public function throwExceptionOnDecryptingNonEncryptedFile()
    {
        $this->expectException(SodiumException::class);
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
        $this->expectException(SodiumException::class);
        $this->expectExceptionMessage('file is already encrypted');
        $this->fileEncryptor->encryptFile();
    }

    #[Test]
    public function throwExceptionIfDecryptionFails()
    {
        $this->fileEncryptor = new FileEncryptor("movie.csv", "rahuls");
        $this->expectException(SodiumException::class);
        $this->expectExceptionMessage('could not decrypt file');
        $this->fileEncryptor->decryptFile();
    }

    #[Test]
    public function canDecryptFile()
    {
        $isFileDecrypted = $this->fileEncryptor->decryptFile();

        $this->assertTrue($isFileDecrypted);
    }

    protected function setUp(): void
    {
        $this->fileEncryptor = new FileEncryptor('movie.csv', 'rahul');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileEncryptor = null;
    }
}