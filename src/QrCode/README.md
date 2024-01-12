# qrcode 生成 二维码和条形码

## 说明

> 本库引用自 `composer require codeitnowin/barcode` 的代码库[此库已经停止维护] 并进行了部分改良

## 如何使用

### 引入库

```
use zxf\Qrcode\QrCode;
use zxf\Qrcode\BarCode;
```

## QrCode 创建二维码

``` php
use zxf\Qrcode\QrCode;

echo '<p>Example - QrCode</p>';
$qrCode = new QrCode();
$qrCode
    ->setText('https://www.weisifang.com/docs') // 生成二维码的内容
    ->setSize(200) // 设置二维码大小
    ->setPadding(10) // 设置边距
    ->setErrorCorrection('high') // 设置二维码纠错级别。 分为 high(30%)、quartile(25%)、medium(15%)、low(7%) 几种
    ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0)) // 设置颜色
    ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0)) // 设置背景色
    ->setLabel('在线文档|威四方') // 设置图片下面的文字
    ->setLabelFontSize(16) // 设置文字字体大小
    ->setImageType(QrCode::IMAGE_TYPE_PNG) // 设置图片类型 ,默认为 png
echo '<img src="data:' . $qrCode->getContentType() . ';base64,' . $qrCode->generate() . '" />';
```

如果想直接输出到浏览器上，而不是获取 base64 文件流，可以使用`draw()` 方法输出，例如

```php
$qrCode = new QrCode(); // 实例化
$qrCode
    ->setText('https://www.weisifang.com/docs') // 生成二维码的内容
    ->setSize(200) // 设置二维码大小
    ->setPadding(10) // 设置边距
    ->setErrorCorrection('high') // 设置二维码纠错级别。 分为 high(30%)、quartile(25%)、medium(15%)、low(7%) 几种
    ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0)) // 设置颜色
    ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0)) // 设置背景色
    ->setLabel('在线文档|威四方') // 设置图片下面的文字
    ->setLabelFontSize(16) // 设置文字字体大小
    ->setImageType(QrCode::IMAGE_TYPE_PNG) // 设置图片类型 ,默认为 png
    ->draw() // 把图片直接绘画到浏览器
    ;
```

### 设置二维码文字字体

#### 使用自定义字体

```
$qrCode->setLabelFontPath("你的ttf文件.ttf");

$qrCode->setLabelFontPath(dirname(__DIR__) . "/resource/font/pmzdxx.ttf");
```

#### 使用内置的字体

```
$qrCode->useFontFile('字体名称，不带.ttf后缀');
$qrCode->useFontFile('pmzdxx');
```

支持的字体

```
pmzdxx 庞门正道细线体
lishu   隶书
heiti   方正黑体简体
fangsong   方正仿宋简体
```

## BarCode 创建条形码

例如：

```php
echo '<p>Example - Isbn</p>';
$barcode = new BarCode(); // 实例化
$barcode->setText("0012345678901"); // 设置条形码内容
$barcode->setFontSize(10); //  设置字体大小
$barcode->setThickness(25); // 设置条码高度
$barcode->setType(BarCode::Isbn); // 设置条形码类型,支持Code128、Code11、Code39、Code39Extended、Ean128、Gs1128、I25、Isbn、Msi、Postnet、S25、Upca、Upce 类型的条形码
$code = $barcode->generate(); // 生成条形码 base64 文件流
echo '<img src="data:image/png;base64,' . $code . '" />';
```

直接输出条形码到浏览器
> 可以把`$code = $barcode->generate();` 这行代码用用`$barcode->draw();` 代替就可以直接输出图片到浏览器了，例如

```php
echo '<p>Example - Isbn</p>';
$barcode = new BarCode(); // 实例化
$barcode->setText("0012345678901"); // 设置条形码内容
$barcode->setFontSize(10); //  设置字体大小
$barcode->setType(BarCode::Isbn); // 设置条形码类型,支持Code128、Code11、Code39、Code39Extended、Ean128、Gs1128、I25、Isbn、Msi、Postnet、S25、Upca、Upce 类型的条形码
$barcode->draw(); // 把图片直接绘画到浏览器
```

### 设置条形码文字字体

#### 使用自定义字体

```
$barcode->setLabelFontPath("你的ttf文件.ttf");

$barcode->setLabelFontPath(dirname(__DIR__) . "/resource/font/pmzdxx.ttf");
```

#### 使用内置的字体

```
$barcode->useFontFile('字体名称，不带.ttf后缀');
$barcode->useFontFile('pmzdxx');
```

### 其他参数

设置分辨率

```
$barcode->setScale(2);
```

设置高度

```
$barcode->setThickness(25);
```

`GS1-128`删除 48 个字符的限制

```
$barcode->setNoLengthLimit(true);
```

`GS1-128`允许未知标识符

```
$barcode->setAllowsUnknownIdentifier(true);
```

### 其他的demo

```
echo '<p>Example - Code128</p>';
$barcode = new BarCode();
$barcode->setText("0123456789");
$barcode->setType(BarCode::Code128);
$barcode->setScale(2);
$barcode->setThickness(25);
$barcode->setFontSize(10);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Code11</p>';
$barcode = new BarCode();
$barcode->setText("0123456789");
$barcode->setType(BarCode::Code11);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Code39</p>';
$barcode = new BarCode();
$barcode->setText("0123456789");
$barcode->setType(BarCode::Code39);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Code39Extended</p>';
$barcode = new BarCode();
$barcode->setText("0123456789");
$barcode->setType(BarCode::Code39Extended);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Ean128</p>';
$barcode = new BarCode();
$barcode->setText("00123456789012345675");
$barcode->setType(BarCode::Ean128);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Gs1128</p>';
$barcode = new BarCode();
$barcode->setText("00123456789012345675");
$barcode->setType(BarCode::Gs1128);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Gs1128</p>';
$barcode = new BarCode();
$barcode->setText("4157707266014651802001012603068039000000006377069620171215");
$barcode->setType(BarCode::Gs1128);
$barcode->setNoLengthLimit(true);
$barcode->setAllowsUnknownIdentifier(true);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

// i15为偶数
echo '<hr>';
echo '<p>Example - I25</p>';
$barcode = new BarCode();
$barcode->setText("00123456789012345675");
$barcode->setType(BarCode::I25);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Isbn</p>';
$barcode = new BarCode();
$barcode->setText("0012345678901");
$barcode->setType(BarCode::Isbn);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Msi</p>';
$barcode = new BarCode();
$barcode->setText("0012345678901");
$barcode->setType(BarCode::Msi);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Postnet</p>';
$barcode = new BarCode();
$barcode->setText("01234567890");
$barcode->setType(BarCode::Postnet);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - S25</p>';
$barcode = new BarCode();
$barcode->setText("012345678901");
$barcode->setType(BarCode::S25);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Upca</p>';
$barcode = new BarCode();
$barcode->setText("012345678901");
$barcode->setType(BarCode::Upca);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';

echo '<hr>';
echo '<p>Example - Upce</p>';
$barcode = new BarCode();
$barcode->setText("012345");
$barcode->setType(BarCode::Upce);
$code = $barcode->generate();
echo '<img src="data:image/png;base64,' . $code . '" />';
```