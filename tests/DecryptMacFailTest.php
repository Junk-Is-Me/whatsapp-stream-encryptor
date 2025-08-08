<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\Decrypt;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class DecryptMacFailTest extends TestCase
{
    private function getSampleData(): string
    {
        return str_repeat("SampleData", 1000);
    }

    private function getRandomKey(): string
    {
        return random_bytes(32);
    }

    public function testDecryptWithWrongMacThrows()
    {
        $original = $this->getSampleData();
        $mediaKey = $this->getRandomKey();
        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, 'WhatsApp Image Keys');
        $iv = $keys['iv'];
        $cipherKey = $keys['cipherKey'];
        $macKey = $keys['macKey'];

        $encryptedWithMac = (new Encrypt(Utils::streamFor(''), $cipherKey, $iv))->encryptAndGetMac($original, $macKey);

        // Подделываем MAC
        $falseEncryptedWithMac = substr($encryptedWithMac, 0, -10) . str_repeat('X', 10);

        $encryptedStream = Utils::streamFor($falseEncryptedWithMac);

        $this->expectException(\RuntimeException::class);
        $decryptor = new Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
        $decryptor->getContents();
    }
}
