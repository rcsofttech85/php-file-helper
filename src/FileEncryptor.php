<?php

namespace Rcsofttech85\FileHandler;

use Exception;
use Rcsofttech85\FileHandler\Exception\FileEncryptorException;
use SensitiveParameter;
use SodiumException;

readonly class FileEncryptor
{
    public function __construct(
        private string $filename,
        #[SensitiveParameter] private string $secret
    ) {
        if (!file_exists($this->filename)) {
            throw new FileEncryptorException("File not found");
        }
    }

    /**
     *
     * @throws FileEncryptorException
     * @throws Exception
     *
     */
    public function encryptFile(): bool
    {
        $plainText = file_get_contents($this->filename);

        if (!$plainText) {
            throw new FileEncryptorException('File has no content');
        }
        if (ctype_xdigit($plainText)) {
            throw new FileEncryptorException('file is already encrypted');
        }


        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);


        $key = hash('sha256', $this->secret, true);


        $ciphertext = sodium_crypto_secretbox($plainText, $nonce, $key);

        $output = bin2hex($nonce . $ciphertext);

        $file = fopen($this->filename, 'w');
        if (!$file) {
            return false;
        }

        try {
            fwrite($file, $output);
        } finally {
            fclose($file);
        }

        return true;
    }

    /**
     *
     * @throws FileEncryptorException
     * @throws SodiumException
     *
     */
    public function decryptFile(): bool
    {
        $encryptedData = file_get_contents($this->filename);

        if (!$encryptedData) {
            throw new FileEncryptorException('File has no content');
        }

        if (!ctype_xdigit($encryptedData)) {
            throw new FileEncryptorException('file is not encrypted');
        }

        $bytes = hex2bin($encryptedData);
        if (!$bytes) {
            return false;
        }
        $nonce = substr($bytes, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($bytes, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $key = hash('sha256', $this->secret, true);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if (!$plaintext) {
            throw new FileEncryptorException('could not decrypt file');
        }


        $file = fopen($this->filename, 'w');
        if (!$file) {
            return false;
        }

        try {
            fwrite($file, $plaintext);
        } finally {
            fclose($file);
        }

        return true;
    }
}
