# PHP工具箱

![](https://img.shields.io/packagist/dt/zxf/tools) ![](https://img.shields.io/github/stars/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/forks/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/tag/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/release/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/issues/zhaoxianfang/tools.svg)

> 基于 php 的项目模块开发
> 调用命名空间 使用 use zxf\…… 例如 use zxf\Http\Curl; use zxf\Tools\Collection;

<a href="http://weisifang.com/docs/2" target="_blank" >在线文档: http://weisifang.com/docs/2</a>

## 安装&引用

```
composer require zxf/tools
```

## 涵盖模块

| 模块                   | 需要包含的文件夹/说明                                                                               |
|----------------------|-------------------------------------------------------------------------------------------|
| zxf\Tools\Collection | <a href="http://www.weisifang.com/docs/doc/2_129" target="_blank">[Collection]</a> 数据集合操作 |
| Qqlogin              | QQ登录                                                                                      |
| WeChat               | 微信                                                                                        |
| 截图                   | ScreenShot                                                                                |
| 微博登录                 | sina                                                                                      |
| zxf\Min\JS           | js 压缩工具(推荐)                                                                               |
| zxf\Min\CSS          | css 压缩工具(推荐)                                                                              |
| QrCode               | 生成二维码                                                                                     |
| BarCode              | 生成条形码                                                                                     |
| Compressor           | 图片压缩类                                                                                     |
| Cache                | 文件缓存                                                                                      |
| TextToImg            | 文字转图片                                                                                     |
| PHPMailer            | 发送邮件                                                                                      |
| Curl                 | http 网络请求                                                                                 |
| Sms                  | 发送短信: ali(阿里云)[默认] 或者 tencent（腾讯云）                                                        |
| Database             | 数据库Orm模型                                                                                  |
| Menu                 | 生成目录菜单(adminlte、layuiadmin、nazox、inspinia)                                                |
| Random               | 生成随机数                                                                                     |
| ImgToIco             | 图片转ico 格式                                                                                 |
| Modules              | laravel 多模块应用                                                                             |
| Command              | 命令行解析工具                                                                                   |
| Tree                 | 树形结构化                                                                                     |
| Dom                  | 简单快速的 HTML 解析器，此模块来源：https://github.com/Imangazaliev/DiDOM                                |
| Encryption           | AES、RSA加密解密                                                                               |
| TnCode               | <a href="https://weisifang.com/docs/doc/2_284" target="_blank">改良版滑动验证码</a>               |
| 其他                   | Command、Cookie管理、站点文件生成、时区转换文件操作等工具类                                                      |

## 示例

### Curl 网络请求

> 强大且简便的的Http 请求管理
<a href="https://weisifang.com/docs/doc/2_14" target="_blank" >「Http请求文档」</a>

``` php

\zxf\Http\Curl::instance()->setParams(['path'=>'pages/index/index'])->post($url,'json');

```

### Cache 文件缓存

#### 实例化

```php
use zxf\Tools\Cache;
$cache = Cache::instance([
    'cache_dir' => "./cache", // 缓存地址
    'type'      => 'random', // 缓存方式 key: 直接使用key存储,random:对key加密存储
    'mode'      => '1', //缓存模式 1:serialize ;2:保存为可执行php文件
]);
```

#### 缓存 api

```php
//设置存放缓存文件夹路径
$cache->setCacheDir('./cache'); 

//设置缓存模式
$cache->setMode(1);
 //模式1 缓存存储方式
 //a:2:{s:11:"expiry_time";i:6475431070;s:4:"data";a:1:{s:3:"key";s:5:"value";}}
 //模式2 缓存存储方式
 // <?php
 // return array(
 //   'data'=>'xxx'
 //);

// 获取缓存
$cache->get('name');

// 设置缓存
$cache->set('name', '张三');

// 设置缓存有效期,第三个参数为int 类型表示缓存多少秒，为string 类型时候的缓存时间为 strtotime 函数支持的字符串，例如："+1 day"
$cache->set('status', '1',55); // 缓存55秒
$cache->set('status', '1','+5 hours'); // 缓存5小时

// 清空所有缓存
$cache->flush(); 

// 删除一条缓存
$cache->delete('name');

// 判断某条缓存是否存在
$cache->has('name');
```

### js压缩

```
$minifier   = new \zxf\Min\JS('var a = "hello";',$jsFilePath,...); // 实例化 并混合自动引入 任意个 需要压缩的 js 文件路径 和 代码段
$res = $minifier->minify();
```

### css压缩

```
$minifier   = new \zxf\Min\CSS('body { color: #000000; }',$cssFilePath,...); // 实例化 并混合自动引入 任意个 需要压缩的 css 文件路径 和 代码段
$res = $minifier->minify();
```

### QrCode 创建二维码

``` php
use zxf\QrCode\QRCodeGenerate;

$levelMap = [
    'high'     => EccLevel::H,
    'quartile' => EccLevel::Q,
    'medium'   => EccLevel::M,
    'low'      => EccLevel::L,
];
$qrcode   = new QRCodeGenerate([
    // 'version'  => $level == 'high' ? min(max(strlen($text) / 10, 10), 35) : 2,
    // 'version'  => \zxf\QrCode\Common\Version::AUTO,
    'eccLevel' => !empty($logoPath) ? EccLevel::H : $levelMap[$level],
    'scale'    => (int)($input['scale'] ?: 2), // 每个模块的像素大小
]);

$handle = $qrcode
    ->content($text)
    ->withText($label ?? '', $fontPath??'', $fontSize??10) // 可选
    ->withLogo($logoPath) // 可选
    ;

// 把图片直接输出到浏览器上
$handle->toBrowser();

// 生成图片保存到文件
$handle->toFile('/your/path/to/qrcode.png');

// 生成base64图片字符串
$base64 = $handle->toBase64();
echo '<img src="' . $base64 . '">';
```

## BarCode 创建条形码

```php
use zxf\BarCode\BarCodeGenerate;

$barcode = new BarCodeGenerate();

$image = $barcode
    ->width((int)$bar_width) // 条码宽度，单位为像素
    ->height((int)$thickness) // 条码高度，单位为像素
    ->padding(8) // 条码安全区，单位为像素
    ->text($label, (int)$fontSize) // 设置底部文本
    ->content($text,$textSize??10,$fontPath??'') // 设置条码内容
    ->format($codeType) // 设置条码格式
    ;

// 直接输出到浏览器
$barcode->toBrowser();

// 保存到文件
$filePath = $barcode->toFile('/your/path/barcode.png');

// 返回图片资源
$barcode->toImg();

// 返回base64图片资源
$barcode->toBase64();
```

### Compressor 图片压缩类

``` php
/**
 * 功能：图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
 * 
 * @DateTime 2019-03-08
 *
 * 调用示例：
 *        # 实例化对象
 *        $Compressor = new \zxf\tools\Compressor();
 *        OR
 *        $Compressor = \zxf\tools\Compressor::instance();
 *
 *        # 使用原始尺寸 压缩图片大小并输出到浏览器
 *        $result = $Compressor->set('001.jpg')->proportion(1)->get();
 *        # 仅压缩
 *        $result = $Compressor->set('001.jpg')->compress(5)->get();
 *        # 仅改变尺寸并保存到指定位置
 *        $result = $Compressor->set('001.jpg', './resizeOnly.jpg')->resize(500, 500)->get();
 *        # 压缩且改变尺寸并保存到指定位置
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->resize(0, 500)->compress(5)->get();
 *        #  压缩且按照比例压缩
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->proportion(0.5)->compress(5)->get();
 *        return $result;
 *  参数说明：
 *        set(原图路径,保存后的路径); // 如果要直接输出到浏览器则只传第一个参数即可
 *        resize(设置宽度,设置高度);//如果有一个参数为0，则保持宽高比例
 *        proportion(压缩比例);//0.1~1 根据比例压缩
 *        compress(压缩级别);//0~9，压缩级别，级别越高就图片越小也就越模糊
 *        get();//获取生成后的结果
 *  提示：
 *        proportion 方法 回去调用 resize 方法，因此他们两个方法只需要选择调用一个即可
 */
```

### TextToImg 文字转图片

``` php
use zxf\tools\TextToImg;

/**
 * 文字生成图片
 *
 * // 创建一个实例
 * $TextToImg = new TextToImg(1200, 800);
 *
 * $TextToImg->setFontFile('./arial.ttf'); // 设置自定义字体路径
 * $TextToImg->setFontStyle('foxi'); // 选择本库中支持的一种字体
 * $TextToImg->setText('这是<br>一段<br>测试文字'); // 设置文字内容，支持使用 <br> 换行
 * $TextToImg->setColor('FF00FF'); // 设置文字颜色
 * $TextToImg->setBgColor('00FF00'); // 设置图片背景色
 * $TextToImg->setAngle(90);// 设置文字旋转
 * $TextToImg->setSize(20);// 设置文字固定字号为20【提示：本库默认会自动计算字体大小，如果设置该属性就使用传入的固定值】
 * $TextToImg->render();// 显示图片到浏览器
 * $TextToImg->render('test.png');// 将图片保存至本地
 */
```

### 附 包中可使用的字体参照

```
pmzdxx 庞门正道细线体
pmzdbt 庞门正道标题体
lishu   隶书
yishanbei   峄山碑篆体
```

### 图片转ICO格式

```
 $imgurl = "./test.jpeg";
 // 下载到浏览器
 zxf\tools\ImgToIco::instance()->set($imgurl, 32)->generate();
 // 保存到指定文件夹
 zxf\tools\ImgToIco::instance()->set($imgurl, 32)->generate('your/path/test.ico');
```

### Tree 树形结构化,

```
// 结构：
$arr = array(
     array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     array('id'=>'2','pid'=>0,'name'=>'一级栏目二', 'weight' => 101),
     array('id'=>'3','pid'=>1,'name'=>'二级栏目一', 'weight' => 1),
     array('id'=>'4','pid'=>1,'name'=>'二级栏目二', 'weight' => 2),
     array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
)

// 用法：
    // 使用默认配置 初始化数据
    $tree = zxf\tools\Tree::instance($data);
    // OR
    $tree = zxf\tools\Tree::instance()->setData($data);
    // 自定义id、pid、children配置
    zxf\tools\Tree::instance($data)->setId('id')->setPid('pid')->setChildlist('children')->getTree();
    // 自定义权重字段和排序方式
    zxf\tools\Tree::instance($data)->setWeight('weight')->setSortType('desc')->getTree();
    // 自定义根节点id，默认为0
    zxf\tools\Tree::instance($data)->setRootId(1)->getTree();
// 接口:
    // 获取结构树
    $nodes = $tree->getTree();
    // 获取所有子节点的主键（包含自己）
    $nodes = $tree->getChildrenAndMeIds(1);
    // 获取所有子节点列表（包含自己）
    $nodes = $tree->getChildrenAndMeNodes(1);
    // 获取所有子节点的主键（不包含自己）
    $nodes = $tree->getChildrenIds(1);
    // 获取所有子节点列表（不包含自己）
    $nodes = $tree->getChildrenNodes(1);
    // 获取所有父节点主键(包含自己)
    $nodes = $tree->getParentAndMeIds(5);
    // 获取所有父节点列表(包含自己)
    $nodes = $tree->getParentAndMeNodes(5);
    // 获取所有父节点主键(不包含自己)
    $nodes = $tree->getParentIds(5);
    // 获取所有父节点列表(不包含自己)
    $nodes = $tree->getParentNodes(5);
    // 获取所有根节点主键
    $roots = $tree->getRootsIds();
    // 重新初始化数据
    $tree->reset();
    // 添加新节点
    $tree->addNode(['id' => 7, 'pid' => 0, 'name' => 'New Node']);
    // 删除节点
    $tree->removeNode(7);
    // 更新节点
    $tree->updateNode(2, ['name' => 'Updated Node']);
```

### 网页截图

> 使用前需要提前到[phantomjs](https://phantomjs.org/download.html) 下载相应的可执行应用程序

```
use zxf\ScreenShot\ScreenShot;

// $softPath： 可执行文件phantomjs或者phantomjs.exe 所在目录； ScreenShot 会自动识别系统 $softPath 该使用 phantomjs 还是 phantomjs.exe
// $url: 被截图网页  url
// $savePath: 截图成功后的保存文件完整地址
$res = ScreenShot::init($softPath='/Users/linian/extend')->setUrl($url = 'http://weisifang.com')->run($savePath = __DIR__.'/img/'.time().'.png');

$res 返回 true|fales 表示是否截图成功
```

### laravel 多模块应用

> 使用 `trace` 助手函数进行代码调试

[多模文档说明](README_laravel.md)

### Command 命令行参数解析

> https://weisifang.com/docs/doc/2_35

## 更多

<a href="http://weisifang.com/docs/2" target="_blank" >查看更多教程</a>