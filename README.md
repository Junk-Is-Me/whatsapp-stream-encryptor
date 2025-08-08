# whatsapp-stream-encryptor

PSR-7 декораторы для шифрования и дешифрования WhatsApp, генерация sidecar для стриминга.

## Возможности

- Шифрование и дешифрование медиафайлов WhatsApp (IMAGE, VIDEO, AUDIO, DOCUMENT)
- Генерация sidecar-файлов для стриминга (поддержка WhatsApp-совместимого формата)
- Промышленное качество кода и тесты

## Установка

```bash
composer require sergey/whatsapp-stream-encryptor
```

## Использование

### Генерация ключей

```php
use Sergey\WhatsappStreamEncryptor\MediaKeyGenerator;

$mediaKey = random_bytes(32);
$keys = MediaKeyGenerator::generateMediaKey('sha256', $mediaKey, 112, 'WhatsApp Image Keys');
$iv = $keys['iv'];
$cipherKey = $keys['cipherKey'];
$macKey = $keys['macKey'];
```

### Шифрование

```php
use Sergey\WhatsappStreamEncryptor\Encrypt;
use GuzzleHttp\Psr7\Utils;

$data = file_get_contents('input.jpg');
$encryptor = new Encrypt(Utils::streamFor(''), $cipherKey, $iv);
$encryptedWithMac = $encryptor->encryptAndGetMac($data, $macKey);
file_put_contents('output.encrypted', $encryptedWithMac);
```

### Дешифрование

```php
use Sergey\WhatsappStreamEncryptor\Decrypt;

$encrypted = file_get_contents('output.encrypted');
$encryptedStream = Utils::streamFor($encrypted);
$decryptor = new Decrypt($encryptedStream, $cipherKey, $iv, $macKey);
$decrypted = $decryptor->getContents();
file_put_contents('output.decrypted.jpg', $decrypted);
```

### Генерация sidecar для стриминга

```php
use Sergey\WhatsappStreamEncryptor\SidecarGenerator;

$stream = Utils::streamFor(file_get_contents('output.encrypted'));
$sidecar = SidecarGenerator::generate($stream, $iv, $macKey);
file_put_contents('output.sidecar', $sidecar);
```

## Тестирование

```bash
composer install
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/
```

## Структура проекта

- `src/` — исходный код библиотеки
- `tests/` — модульные тесты (PHPUnit)
- `samples/` — тестовые файлы (оригиналы, ключи, зашифрованные, sidecar)

## Пример HKDF info для разных типов

| Тип медиа | Info-строка для HKDF         |
|-----------|------------------------------|
| IMAGE     | WhatsApp Image Keys          |
| VIDEO     | WhatsApp Video Keys          |
| AUDIO     | WhatsApp Audio Keys          |
| DOCUMENT  | WhatsApp Document Keys       |

## Лицензия

MIT

---
