# BarCode 条形码

> 来源：https://github.com/picqer/php-barcode-generator
> 日期：2025-01-21
> 版本：3.2.0

## 快速开始

```php
use zxf\BarCode\BarCodeGenerate;
```

### Code128 条码

```php
$barcode = new BarCodeGenerate();

$barcode
    ->width(2) // 条码宽度，单位为像素
    ->height(50) // 条码高度，单位为像素
    ->padding(8) // 条码安全区，单位为像素
    // ->text('自定义文本内容!', 12) // 设置底部文本
    ->content('A01234567890') // 设置条码内容
    ->format(BarcodeGenerator::TYPE_CODE_128); // 设置条码格式
;

// 直接输出到浏览器
$barcode->toBrowser();

// 保存到文件
$filePath = $barcode->toFile('/your/path/barcode.png');
echo $filePath;

// 返回图片资源
$barcode->toImg();
```

## 其他方式

```php
$barcode = (new zxf\BarCode\Types\TypeCode128())->getBarcode('081231723897');

// 使用 HTML 渲染器在浏览器中将条形码输出为 HTML
$renderer = new zxf\BarCode\Renderers\HtmlRenderer();
$renderer->setForegroundColor([255, 0, 0]); // 为条形指定红色，默认为黑色。为红色、绿色和蓝色指定 3 倍的 0-255 值。
$renderer->setBackgroundColor([0, 0, 255]); // 为背景指定蓝色，默认为透明。为红色、绿色和蓝色指定 3 倍的 0-255 值。

echo $renderer->render($barcode);
// or
$renderer->render($barcode, 450.20, 75); // 宽度和高度支持浮动
```

### 设置颜色和png 格式

```php
$colorRed = [255, 0, 0];

$barcode = (new zxf\BarCode\Types\TypeCode128())->getBarcode('081231723897');
$renderer = new zxf\BarCode\Renderers\PngRenderer();
$renderer->setForegroundColor($colorRed);

// 将 PNG 保存到文件系统，宽度因子为 3（条形码宽度 x 3），高度为 50 像素
file_put_contents('barcode.png', $renderer->render($barcode, $barcode->getWidth() * 3, 50));
```

### 生成png图片

```php
$redColor = [255, 0, 0];

$generator = new zxf\BarCode\BarcodeGeneratorPNG();
file_put_contents('barcode.png', $generator->getBarcode('081231723897', $generator::TYPE_CODE_128, 3, 50, $redColor));

```

### 支持的生成图片类型

```php
$generatorSVG = new zxf\BarCode\BarcodeGeneratorSVG(); // 基于矢量的 SVG
$generatorPNG = new zxf\BarCode\BarcodeGeneratorPNG(); // 基于像素的 PNG
$generatorJPG = new zxf\BarCode\BarcodeGeneratorJPG(); // 基于像素的 JPG
$generatorHTML = new zxf\BarCode\BarcodeGeneratorHTML(); // 基于像素的 HTML
$generatorHTML = new zxf\BarCode\BarcodeGeneratorDynamicHTML(); // 基于矢量的 HTML
```