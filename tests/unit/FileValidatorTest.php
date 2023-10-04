<?php

namespace unit;

use Base\BaseTest;
use PHPUnit\Framework\Attributes\Test;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;

class FileValidatorTest extends BaseTest
{
    use FileValidatorTrait;

    #[Test]
    public function throwErrorIfPathIsInvalid(): void
    {
        $filename = "movie.csv";
        $path = 'hello';

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("path {$path} is not valid");
        $this->validateFileName($filename, $path);
    }


    #[Test]
    public function shouldThrowExceptionIfFileNameContainsIllegalCharacter(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("file not found");
        $this->validateFileName('@#$');
    }

    /**
     * @return void
     */
    #[Test]
    public function checkFileIsRestricted(): void
    {
        $filename = self::$containerBuilder->getParameter('STORED_HASH_FILE');
        if (!is_string($filename)) {
            $this->fail('expected string type');
        }
        $isFileRestricted = $this->isFileRestricted($filename, self::STORED_HASH_FILE);
        $this->assertTrue($isFileRestricted);
    }

    #[Test]
    public function getParamMethodValidateForStringType(): void
    {
        $container = self::$containerBuilder;
        $container->setParameter('arr', []);
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("arr is not string type");
        $this->getParam($container, 'arr');
    }

    #[Test]
    public function throwExceptionIfFileFailedToOpen(): void
    {
        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("file is not valid");
        $this->openFileAndReturnResource('movie.csv', 'f');
    }


    /**
     * @throws FileHandlerException
     */
    #[Test]
    public function shouldNotThrowExceptionIfFileExists(): void
    {
        $filename = "sample";
        $path = __DIR__;

        file_put_contents($path . '/' . $filename, '');
        $this->expectNotToPerformAssertions();
        $this->validateFileName($filename, $path);
        unlink($path . '/' . $filename);
    }
}
