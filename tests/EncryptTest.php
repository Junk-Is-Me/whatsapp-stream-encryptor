<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use Sergey\WhatsappStreamEncryptor\Encrypt;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class EncryptTest extends TestCase
{
    private function getRandomKey(): string
    {
        return random_bytes(32);
    }

    private function getSampleData(): string
    {
        return str_repeat("SampleData", 1000);
    }

    public function testEncryptDecryptRoundTrip()
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

        $encryptedStream = Utils::streamFor($encryptedWithMac);
        $decryptor = new \Sergey\WhatsappStreamEncryptor\Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
        $decrypted = $decryptor->getContents();

        $this->assertEquals($data, $decrypted, "Дешифрованные данные должны совпадать с исходными");
    }

    public function testEncryptDifferentMediaTypes()
    {
        $mediaTypes = [
            'IMAGE' => 'WhatsApp Image Keys',
            'VIDEO' => 'WhatsApp Video Keys',
            'AUDIO' => 'WhatsApp Audio Keys',
            'DOCUMENT' => 'WhatsApp Document Keys',
        ];
        $data = $this->getSampleData();

        foreach ($mediaTypes as $type => $info) {
            $mediaKey = $this->getRandomKey();
            $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, $info);
            $iv = $keys['iv'];
            $cipherKey = $keys['cipherKey'];
            $macKey = $keys['macKey'];

            $encryptor = new Encrypt(Utils::streamFor(''), $cipherKey, $iv);
            $encryptedWithMac = $encryptor->encryptAndGetMac($data, $macKey);

            $encryptedStream = Utils::streamFor($encryptedWithMac);
            $decryptor = new \Sergey\WhatsappStreamEncryptor\Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
            $decrypted = $decryptor->getContents();

            $this->assertEquals($data, $decrypted, "Дешифрованные данные для $type должны совпадать с исходными");
        }
    }
}
