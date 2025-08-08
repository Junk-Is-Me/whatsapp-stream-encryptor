<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\Decrypt;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class DecryptWrongKeyTest extends TestCase
{
    private function getSampleData(): string
    {
        return str_repeat("SampleData", 1000);
    }

    private function getRandomKey(): string
    {
        return random_bytes(32);
    }

    public function testDecryptWithWrongKeyThrows()
    {
        $original = $this->getSampleData();
        $mediaKey = random_bytes(32);
        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, 'WhatsApp Image Keys');
        $iv = $keys['iv'];
        $cipherKey = $keys['cipherKey'];
        $macKey = $keys['macKey'];

        $encryptedWithMac = (new Encrypt(Utils::streamFor(''), $cipherKey, $iv))->encryptAndGetMac($original, $macKey);

        // Используем другой ключ для дешифрации
        $wrongMediaKey = random_bytes(32);
        $wrongKeys = MediaKeyGenerator::generateMediaKey('sha256', $wrongMediaKey, 112, 'WhatsApp Image Keys');
        $wrongIv = $wrongKeys['iv'];
        $wrongCipherKey = $wrongKeys['cipherKey'];
        $wrongMacKey = $wrongKeys['macKey'];

        $encryptedStream = Utils::streamFor($encryptedWithMac);

        $this->expectException(\RuntimeException::class);
        $decryptor = new Decrypt($encryptedStream, $wrongCipherKey, $wrongIv, $wrongMacKey);
        $decryptor->getContents();
    }
}

