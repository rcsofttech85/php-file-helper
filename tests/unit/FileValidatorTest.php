<?php

namespace unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Validator\FileValidator;

class FileValidatorTest extends TestCase
{
    #[Test]
    public function filenameIsValidAndSanitized(): void
    {
        $filename = "rahul";
        $path = 'hello';

        $this->expectException(FileHandlerException::class);
        $this->expectExceptionMessage("path {$path} is not valid");
        FileValidator::validateFileName($filename, $path);
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
        FileValidator::validateFileName($filename, $path);
        unlink($path . '/' . $filename);
    }
}
