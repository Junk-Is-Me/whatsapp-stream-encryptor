<?php

namespace Sergey\WhatsappStreamEncryptor\Tests;

use PHPUnit\Framework\TestCase;
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;
use Sergey\WhatsappStreamEncryptor\SidecarGenerator;
use GuzzleHttp\Psr7\Utils;

require_once __DIR__ . '/../vendor/autoload.php';

class SidecarGeneratorTest extends TestCase
{
    public function testSidecarGenerationMatchesSample()
    {
        // Используем тестовый файл из samples
        $encrypted = file_get_contents(__DIR__ . '/../samples/VIDEO.encrypted');
        $mediaKey = file_get_contents(__DIR__ . '/../samples/VIDEO.key');
        //$mediaKey = base64_decode(trim(file_get_contents(__DIR__ . '/../samples/VIDEO.key')));
        $expectedSidecar = file_get_contents(__DIR__ . '/../samples/VIDEO.sidecar');

        $keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, 'WhatsApp Video Keys');
        $iv = $keys['iv'];
        $macKey = $keys['macKey'];

        // Генерируем sidecar
        $stream = Utils::streamFor($encrypted);
        $sidecar = SidecarGenerator::generate($stream, $iv, $macKey);

        $this->assertEquals($expectedSidecar, $sidecar, "Sidecar должен совпадать");
    }
}
