<?php

namespace Sergey\WhatsappStreamEncryptor;

use Psr\Http\Message\StreamInterface;

class SidecarGenerator
{
    public static function generate(StreamInterface $stream, string $iv, string $macKey, int $chunkSize = 65536): string
    {
        $sidecar = '';
        $stream->rewind();

        $size = $stream->getSize();
        if ($size === null) {
            throw new \RuntimeException('Stream size must be known to generate sidecar.');
        }

        $blockIndex = 0;
        while (true) {
            $offset = $blockIndex * $chunkSize;
            if ($offset >= $size) {
                break;
            }

            $stream->seek($offset);
            $chunk = $stream->read($chunkSize);

            // Следующие 16 байт после блока
            $extra = '';
            if (($offset + $chunkSize) < $size) {
                $stream->seek($offset + $chunkSize);
                $extra = $stream->read(16);
            }
            if (strlen($extra) < 16) {
                $extra .= str_repeat("\0", 16 - strlen($extra));
            }

            $chunkData = $chunk . $extra;
            $hmac = hash_hmac('sha256', $iv . $chunkData, $macKey, true);
            $sidecar .= substr($hmac, 0, 10);

            $blockIndex++;
        }

        return $sidecar;
    }
}

