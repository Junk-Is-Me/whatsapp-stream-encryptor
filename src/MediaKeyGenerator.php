<?php

namespace Sergey\WhatsappStreamEncryptor;

class MediaKeyGenerator
{
    public static function generateMediaKey(string $algo, string $mediaKey, int $length, string $info): array
    {
        $expandedKey = self::hkdf($algo, $mediaKey, $length, $info);

        return [
            'iv' => substr($expandedKey, 0, 16),
            'cipherKey' => substr($expandedKey, 16, 32),
            'macKey' => substr($expandedKey, 48, 32),
            'refKey' => substr($expandedKey, 80, 32),
        ];
    }
    private static function hkdf(string $algo, string $ikm, int $length, string $info): string
    {
        $hashLen = strlen(hash($algo, '', true));
        $salt = str_repeat("\0", $hashLen);
        $prk = hash_hmac($algo, $ikm, $salt, true);


        $outputKey = '';
        $block = '';
        $counter = 1;

        while (strlen($outputKey) < $length) {
            $block = hash_hmac($algo, $block . $info . chr($counter), $prk, true);
            $outputKey .= $block;
            $counter++;
        }

        return substr($outputKey, 0, $length);
    }
}
