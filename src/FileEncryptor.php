<?php

namespace Rcsofttech85\FileHandler;

use Exception;
use Rcsofttech85\FileHandler\Exception\FileEncryptorException;
use Rcsofttech85\FileHandler\Exception\FileHandlerException;
use Rcsofttech85\FileHandler\Validator\FileValidatorTrait;
use SodiumException;

final class FileEncryptor
{
    use FileValidatorTrait;

    public const ENCRYPT_PASSWORD = 'ENCRYPT_PASSWORD';

    /**
     *
     * @throws FileEncryptorException
     * @throws Exception
     *
     */
    public function encryptFile(string $filename): bool
    {
        $this->validateFileName($filename);
        $plainText = file_get_contents($filename);

        if (!$plainText) {
            throw new FileEncryptorException('File has no content');
        }
        if (ctype_xdigit($plainText)) {
            throw new FileEncryptorException('file is already encrypted');
        }


        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);


        $secret = $this->getParam(self::ENCRYPT_PASSWORD);

        $key = hash('sha256', $secret, true);


        $ciphertext = sodium_crypto_secretbox($plainText, $nonce, $key);

        $output = bin2hex($nonce . $ciphertext);

        $file = $this->openFileAndReturnResource($filename);

        try {
            fwrite($file, $output);
        } finally {
            fclose($file);
        }

        return true;
    }

    /**
     * @return bool
     * @throws FileEncryptorException
     * @throws FileHandlerException
     * @throws SodiumException
     */
    public function decryptFile(string $filename): bool
    {
        $this->validateFileName($filename);
        $encryptedData = file_get_contents($filename);

        if (!$encryptedData) {
            throw new FileEncryptorException('File has no content');
        }

        if (!ctype_xdigit($encryptedData)) {
            throw new FileEncryptorException('file is not encrypted');
        }


        $bytes = $this->convertHexToBin($encryptedData);

        $nonce = substr($bytes, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($bytes, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);


        $secret = $this->getParam(self::ENCRYPT_PASSWORD);

        $key = hash('sha256', $secret, true);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        $file = $this->openFileAndReturnResource($filename);

        if (!$plaintext) {
            fwrite($file, $encryptedData);
            throw new FileEncryptorException('could not decrypt file');
        }


        try {
            fwrite($file, $plaintext);
        } finally {
            fclose($file);
        }

        return true;
    }

    /**
     * @param string $encryptedData
     * @return string
     * @throws FileEncryptorException
     */
    public function convertHexToBin(string $encryptedData): string
    {
        $bytes = hex2bin($encryptedData);
        if (!$bytes) {
            throw new FileEncryptorException('could not convert hex to bin');
        }
        return $bytes;
    }
}
