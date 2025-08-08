<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

class MediaKeyGeneratorTest extends TestCase
{
    private function getRandomKey(): string
    {
        return random_bytes(32);
    }

    public function testGenerateMediaKeyStructure()
    {
        $mediaKey = $this->getRandomKey();
        $info = 'WhatsApp Image Keys';
        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, $info);

        $this->assertEquals(16, strlen($keys['iv']));
        $this->assertEquals(32, strlen($keys['cipherKey']));
        $this->assertEquals(32, strlen($keys['macKey']));
        $this->assertEquals(32, strlen($keys['refKey']));
    }
}
