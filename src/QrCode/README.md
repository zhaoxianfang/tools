# QrCode 二维码

> 来源：https://github.com/chillerlan/php-qrcode
> 文档：https://php-qrcode.readthedocs.io/en/main/
> 日期：2025-01-21
> 版本：5.0.3

- 2025-01-22 新增 Extend/WithTextOrLogo 添加二维码文本或者 Logo 功能

## 快速开始

```php
use zxf\QrCode\QRCodeGenerate;
```

### 需要自定义处理图片

```php
// 设置参数
$options=[
    'version'                => 2, // 二维码版本（1-40），数值越大，二维码越复杂
    'eccLevel'               => EccLevel::H,  // 纠错级别（L:7% (default)、M:15%、Q:25%、H:30%）
    'outputInterface'        => QRGdImagePNG::class, // 输出为 PNG 图片 :https://php-qrcode.readthedocs.io/en/v5.0.x/Usage/Configuration-settings.html#outputtype
    'scale'                  => 1,  // 每个模块的像素大小
    'outputBase64'           => false,  // 切换 base64 数据 URI 或原始数据输出（如果适用） 默认true
    'imageTransparent'       => true,  // 是否使用透明背景
    'addQuietzone'           => true,  // 是否添加静默区 二维码的 margin
    'quietzoneSize'          => 4,  // margin 大小（0 ... $moduleCount / 2） 默认为 4
    'returnResource'         => false, // 是否返回图像资源（如 GD 图像资源）而不是直接输出图像数据 默认值：false
    'cachefile'              => null, // 缓存文件
    'eol'                    => PHP_EOL, // 换行符，默认为 PHP_EOL
    'bgColor'                => "#f0f0f0", // 设置图像背景颜色 默认为"white"
    // 'invertMatrix'=>false, // 是否反转矩阵（反射率反转）
    // 'drawLightModules'=>true, // 是否绘制光（假）模块 默认为false(是否绘制浅色模块)
    'drawCircularModules'    => false, // 指定是否将模块绘制为实心圆(是否绘制圆形模块)
    // 圆形模块的半径，用于绘制圆形模块时指定半径大小
    'circleRadius'           => 0.45, // 当 QROptions::$drawCircularModules 设置为 true 时指定模块的半径,默认0.45
    // 指定当 QROptions::$drawCircularModules 设置为 true 时要排除的模块类型
    // 指定哪些模块应保持为正方形，通常用于某些特殊模块（如查找模块）。
    'keepAsSquare'           => [
        QRMatrix::M_FINDER_DARK,
        QRMatrix::M_FINDER_DOT,
    ],
    // 模块值映射，用于指定 QR 码中不同模块（如数据模块、查找模块等）的颜色或值
    // 模块值 1、QRImagick、QRMarkupHTML、QRMarkupSVG：#ABCDEF、cssname、rgb()、rgba()…
    // 2、QREps、QRFpdf、QRGdImage：[R、G、B] // 0-255
    // 3、QREps：[C、M、Y、K] // 0-255
    // 'moduleValues'        => [255,255,255],
    'moduleValues'           => [
        QRMatrix::M_DATA      => '#ffff00',
        QRMatrix::M_DATA_DARK => '#00ff00',
    ],
    // 设置 QROptions::$imageTransparent 设置为 true 时的透明度颜色。
    // Defaults to QROptions::$bgColor.
    // QRGdImage: [R, G, B], this color is set as transparent in imagecolortransparent()
    // QRImagick: "color_str", this color is set in Imagick::transparentPaintImage()
    'transparencyColor'      => true,
    // 压缩质量,给定值取决于所使用的输出类型：
    // QRGdImageBMP: [0...1]
    // QRGdImageJPEG: [0...100]
    // QRGdImageWEBP: [0...9]
    // QRGdImagePNG: [0...100]
    // QRImagick: [0...100]
    // 图像质量，用于指定生成的图像的质量，仅适用于某些图像格式（如 JPEG）
    // 'quality'      => 1,
    // 'gdImageUseUpscale'      => true, // 当 QROptions::$drawCircularModules 设置为 true 并且 QROptions::$scale 小于 20 时，切换内部放大的使用
    'cssClass'               => 'tool_qr', // 一个常见的CSS类
    // 'textLineStart'      => 'tool_qr', // 可选的行前缀，例如用于在控制台中对齐 QR 码的空白空间
    'svgAddXmlHeader'        => true, // 是否添加 XML 标题行，例如将 SVG 直接嵌入到 HTML 中
    // 'svgDefs'      => '', // SVG <defs> 标签中的任何内容
    // 'readerGrayscale'      => true, // 读取前对图像进行灰度化
    'readerInvertColors'     => false, // 反转图像的颜色
    'readerIncreaseContrast' => false, // 阅读前增加对比度
];

// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodeGenerate($options);
$out = $qrcode->content('hello')->toImg();

header('Content-type: image/png');

// 输出图像数据
echo $out;

exit;

```

### 把图片直接输出到浏览器上

```php
// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodeGenerate();
$qrcode
    ->content('hello')
    ->withText('威四方 QrCode') // 可选
    ->withLogo('/your/path/logo.png') // 可选
    ->toBrowser();

```

### 生成图片保存到文件

```php

// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodeGenerate();
$filePath = $qrcode
    ->content('hello')
    ->withText('威四方 QrCode') // 可选
    ->withLogo('/your/path/logo.png') // 可选
    ->toFile('/your/path/to/qrcode.png');

// 输出图像数据
echo $filePath;

```

### 生成base64图片字符串

```php

// 1、生成带logo 和文字的 二维码
$qrcode = new QRCodeGenerate();
$filePath = $qrcode
    ->content('hello')
    ->withText('威四方 QrCode') // 可选
    ->withLogo('/your/path/logo.png') // 可选
    ->toBase64();

// 输出图像数据
echo '<img src="' . $filePath . '">';

```

### 读取二维码内容

```php
try {
    // 2、读取二维码
    $filePath = '/your/path/qrcode.png';
    $result   = (new QRCodeGenerate)->readFromFile($filePath);
    // 可以使用结果实例...
    $content = $result->data;
    var_dump($content);
    // ...或者 简单地将结果实例转换为字符串以获取内容
    $content = (string)$result;
    var_dump($content);
} catch (Throwable $e) {
    return 'Error reading QR Code: ' . $e->getMessage();
}
```