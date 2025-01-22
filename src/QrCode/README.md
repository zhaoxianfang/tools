> 来源：https://github.com/chillerlan/php-qrcode
> 文档：https://php-qrcode.readthedocs.io/en/main/
> 日期：2025-01-21
> 版本：5.0.3

- 2025-01-22 新增 Extend/WithTextOrLogo 添加二维码文本或者 Logo 功能

## 快速开始

```php
use zxf\QRCode\QRCodePlus;
```

### 需要自定义处理图片

```php

$options=[];

// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodePlus();
$out = $qrcode
    ->content('hello')
    ->withText('威四方 QrCode')
    ->withLogo('/Users/linian/Pictures/Things.png')
    ->run(WithTextOrLogo::HANDLE_TYPE_RETURN_IMG)

header('Content-type: image/png');

// 输出图像数据
echo $out;

exit;

```

### 把图片直接输出到浏览器上

```php
// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodePlus();
$qrcode
    ->content('hello')
    ->withText('威四方 QrCode') // 可选
    ->withLogo('/your/path/logo.png') // 可选
    ->run(WithTextOrLogo::HANDLE_TYPE_TO_BROWSER)

```

### 生成图片保存到文件

```php
try {
// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodePlus();
$filePath = $qrcode
    ->content('hello')
    ->withText('威四方 QrCode') // 可选
    ->withLogo('/your/path/logo.png') // 可选
    ->run(WithTextOrLogo::HANDLE_TYPE_TO_PATH, '/your/path/to/qrcode.png')

// 输出图像数据
echo $filePath;

```

### 读取二维码内容

```php
try {
    // 2、读取二维码
    $filePath = '/your/path/qrcode.png';
    $result   = (new QRCodePlus)->readFromFile($filePath);
    // you can now use the result instance...
    $content = $result->data;
    var_dump($content);
    // ...or simply cast the result instance to string to get the content
    $content = (string)$result;
    var_dump($content);
} catch (Throwable $e) {
    return 'Error reading QR Code: ' . $e->getMessage();
}
```