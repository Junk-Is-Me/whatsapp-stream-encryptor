<?php

namespace Sergey\WhatsappStreamEncryptor;

use Psr\Http\Message\StreamInterface;

class Encrypt implements StreamInterface
{
    private StreamInterface $stream;
    private string $iv;
    private string $cipherKey;
    private bool $isClosed = false;

    public function __construct(StreamInterface $stream, string $cipherKey, string $iv)
    {
        $this->stream = $stream;
        $this->cipherKey = $cipherKey;
        $this->iv = $iv;
    }

    public function encryptAndGetMac(string $data, string $macKey): string
    {
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            $this->cipherKey,
            OPENSSL_RAW_DATA,
            $this->iv
        );
        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }
        $mac = substr(hash_hmac('sha256', $this->iv . $encrypted, $macKey, true), 0, 10);

        return $encrypted . $mac;
    }

    public function write(string $string): int
    {
        if ($this->isClosed) {
            throw new \RuntimeException('Stream is closed');
        }

        $encrypted = openssl_encrypt(
            $string,
            'aes-256-cbc',
            $this->cipherKey,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return $this->stream->write($encrypted);
    }

    public function close(): void
    {
        $this->isClosed = true;
        $this->stream->close();
    }

    public function detach()
    {
        $this->isClosed = true;
        return $this->stream->detach();
    }

    public function getMetadata(string $key = null)
    {
        return $this->stream->getMetadata($key);
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return false;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function getSize(): int|null
    {
        return null;
    }

    public function tell(): int
    {
        return $this->stream->tell();
    }

    public function eof(): bool
    {
        return $this->stream->eof();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Stream is not seekable');
    }

    public function read(int $length): string
    {
        throw new \RuntimeException('Stram is not reedable');
    }

    public function rewind(): void
    {
        throw new \RuntimeException('Stream is not rewindable');
    }

    public function getContents(): string
    {
        throw new \RuntimeException('Stram is not readable');
    }

    public function __toString(): string
    {
        return '';
    }
}