<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use Sergey\WhatsappStreamEncryptor\Decrypt;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class EncryptDecryptMediaTypesTest extends TestCase
{
    private function getRandomKey(): string
    {
        return random_bytes(32);
    }
    public function testEncryptDecryptAllMediaTypes()
    {
        $mediaTypes = [
            'IMAGE' => 'WhatsApp Image Keys',
            'VIDEO' => 'WhatsApp Video Keys',
            'AUDIO' => 'WhatsApp Audio Keys',
            'DOCUMENT' => 'WhatsApp Document Keys',
        ];

        $originalData = file_get_contents(__DIR__ . '/../samples/IMAGE.original');

        foreach ($mediaTypes as $type => $info) {
            $mediaKey = $this->getRandomKey();
            $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, $info);
            $iv = $keys['iv'];
            $cipherKey = $keys['cipherKey'];
            $macKey = $keys['macKey'];

            $encryptedWithMac = (new Encrypt(Utils::streamFor(''), $cipherKey, $iv))->encryptAndGetMac($originalData, $macKey);

            $encryptedStream = Utils::streamFor($encryptedWithMac);
            $decryptor = new Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
            $decryptedData = $decryptor->getContents();

            $this->assertEquals($originalData, $decryptedData, "Дешифрация для $type совпадает с оригиналом");
        }
    }
}