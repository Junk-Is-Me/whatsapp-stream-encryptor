<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use Sergey\WhatsappStreamEncryptor\Decrypt;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class EncryptDecryptEmptyFileTest extends TestCase
{
    private function getSampleData(): string
    {
        return '';
    }

    private function getRandomKey(): string
    {
        return random_bytes(32);
    }
    
    public function testEncryptDecryptEmptyFile()
    {
        $original = $this->getSampleData();
        $mediaKey = $this->getRandomKey();
        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, 'WhatsApp Image Keys');
        $iv = $keys['iv'];
        $cipherKey = $keys['cipherKey'];
        $macKey = $keys['macKey'];

        $encryptedWithMac = (new Encrypt(Utils::streamFor(''), $cipherKey, $iv))->encryptAndGetMac($original, $macKey);

        $encryptedStream = Utils::streamFor($encryptedWithMac);
        $decryptor = new Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
        $decrypted = $decryptor->getContents();

        $this->assertEquals($original, $decrypted, "Encrypt/Decrypt пустого файла работает");
    }
}
