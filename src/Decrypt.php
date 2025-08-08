<?php

namespace Sergey\WhatsappStreamEncryptor;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Decrypt implements StreamInterface
{
    private StreamInterface $stream;
    private string $iv;
    private string $cipherKey;
    private string $macKey;
    private string $buffer = '';
    private int $position = 0;
    private const BLOCK_SIZE = 16;

    public function __construct(StreamInterface $stream, string $cipherKey, string $iv, string $macKey)
    {
        $this->stream = $stream;
        $this->cipherKey = $cipherKey;
        $this->iv = $iv;
        $this->macKey = $macKey;
    }

    public function read(int $length): string
    {
        if ($this->buffer === '') {
            $encrypted = $this->stream->getContents();
            $mac = substr($encrypted, -10);
            $encryptedData = substr($encrypted, 0, -10);

            $expectedMac = substr(hash_hmac('sha256', $this->iv . $encryptedData, $this->macKey, true), 0, 10);

            if ($mac !== $expectedMac) {
                throw new \RuntimeException('MAC mismatch');
            }

            if (strlen($encryptedData) % self::BLOCK_SIZE !== 0) {
                throw new \RuntimeException('Encrypted data length is not a multiple of block size');
            }
            
            $decrypted = openssl_decrypt(
                $encryptedData,
                'aes-256-cbc',
                $this->cipherKey,
                OPENSSL_RAW_DATA,
                $this->iv
            );

            if ($decrypted === false) {
                throw new \RuntimeException('Failed to decrypt data');
            }

            $this->buffer = $decrypted;
        }

        $result = substr($this->buffer, 0, $length);
        $this->buffer = substr($this->buffer, $length);
        $this->position += strlen($result);
        return $result;
    }

    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function eof(): bool
    {
        return $this->stream->eof() && $this->buffer === '';
    }

    public function getContents(): string
    {
        $content = '';

        while (!$this->eof()) {
            $content .= $this->read(8192);
        }

        return $content;
    }

    public function getSize(): ?int
    {
        return null;
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Stream is not seekable');
    }

    public function rewind(): void
    {
        throw new RuntimeException('Steam is not rewindable');
    }

    public function write(string $string): int
    {
        throw new RuntimeException('Stram is not writable');
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->stream->getMetadata($key);
    }
}
