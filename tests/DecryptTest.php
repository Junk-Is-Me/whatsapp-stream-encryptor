<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use Sergey\WhatsappStreamEncryptor\Decrypt;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class DecryptTest extends TestCase
{
    private function getRandomKey(): string
    {
        return random_bytes(32);
    }

    private function getSampleData(): string
    {
        return str_repeat("DecryptTestData", 1000);
    }

    public function testDecryptWithInvalidMacThrows()
    {
        $mediaKey = $this->getRandomKey();
        $mediaType = 'IMAGE';
        $info = 'WhatsApp Image Keys';
        $data = $this->getSampleData();

        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, $info);
        $iv = $keys['iv'];
        $cipherKey = $keys['cipherKey'];
        $macKey = $keys['macKey'];

        $encryptor = new Encrypt(Utils::streamFor(''), $cipherKey, $iv);
        $encryptedWithMac = $encryptor->encryptAndGetMac($data, $macKey);

        // Повреждаем MAC
        $corrupted = substr($encryptedWithMac, 0, -1) . chr(ord(substr($encryptedWithMac, -1)) ^ 0xFF);

        $encryptedStream = Utils::streamFor($corrupted);

        $this->expectException(\RuntimeException::class);
        $decryptor = new Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
        $decryptor->getContents();
    }
}
