# 代码压缩类

## JsMin

支持 css 、js 、 html；修改自 `https://github.com/rgrove/jsmin-php` 仓库

``` php
use zxf\min\JsMin;
// 压缩并返回压缩后的结果字符串
$minifiedCode = JsMin::minify($jsOrCssString);
```

## 压缩 CSS、JS

修改自 `https://github.com/matthiasmullie/minify` 仓库
update_at:2023-07-10

``` php
use zxf\min;
```

## 实例化与加载文件

### 实例化CSS

```
$minifier   = new min\CSS(); // 仅实例化
$minifier   = new min\CSS('body { color: #000000; }'); // 实例化 并自动引入 需要压缩的 css 代码段
$minifier   = new min\CSS('body { color: #000000; }','code2...','code3...',...); // 实例化 并自动引入 任意个 需要压缩的 css 代码段
$minifier   = new min\CSS($cssFilePath); // 实例化 并自动引入 需要压缩的 css 文件路径
$minifier   = new min\CSS($cssFilePath,$cssFilePath2,...); // 实例化 并自动引入 任意个 需要压缩的 css 文件路径

$minifier   = new min\CSS('body { color: #000000; }',$cssFilePath,...); // 实例化 并混合自动引入 任意个 需要压缩的 css 文件路径 和 代码段
```

### 实例化JS

> 同 css

```
$minifier   = new min\JS(); // 仅实例化
$minifier   = new min\JS('var a = "hello";'); // 实例化 并自动引入 需要压缩的 js 代码段
$minifier   = new min\JS('var a = "hello";','code2...','code3...',...); // 实例化 并自动引入 任意个 需要压缩的 js 代码段
$minifier   = new min\JS($jsFilePath); // 实例化 并自动引入 需要压缩的 js 文件路径
$minifier   = new min\JS($jsFilePath,$jsFilePath2,...); // 实例化 并自动引入 任意个 需要压缩的 js 文件路径

$minifier   = new min\JS('var a = "hello";',$jsFilePath,...); // 实例化 并混合自动引入 任意个 需要压缩的 js 文件路径 和 代码段
```

### 追加压缩内容(字符串或文件路径)

```
$minifier->add($sourcePath2);
```

### 进行压缩并保存到磁盘

```
$minifiedPath = 'you_path/xxx.css 或 you_path/xxx.js';
$minifier->minify($minifiedPath);
```

### 进行压缩并返回压缩结果(不保存内容)

```
$res = $minifier->minify();
```

### 压缩并保存

> 缩小并选择性地保存到文件中，就像minify() 一样，但它也会对缩小的内容进行gzencoder()

```
// gzip($path, $level); $level: 压缩级别0~9
$minifier->gzip('/target/path.js');
```

### demo

```
$minifier = new zxf\min\CSS();
// 添加文件
$sourcePath2 = 'you_path/demo.css';
$minifier->add($sourcePath2);

// 或者添加 css 字符串
$css = 'body { color: #000000; }';
$minifier->add($css);

// 仅css 支持  setMaxImportSize 和  setImportExtensions 方法
// css 设置将自动将引用的文件（如图像、字体等）（默认为5kb 大小内的）嵌入到缩小的CSS中，这样就不必通过多个连接获取它们
$minifier->setMaxImportSize(10); // 单位kb
$extensions = array(
    'gif' => 'data:image/gif',
    'png' => 'data:image/png',
);
// 这种方法允许指定文件的类型及其数据：mime类型。 默认的类型有： gif, png, jpg, jpeg, svg, apng, avif, webp, woff and woff2.
$minifier->setImportExtensions($extensions);

// 保存到磁盘
$minifiedPath = 'you_path/to_min.css';
$minifier->minify($minifiedPath);

// 或者输出内容
$res = $minifier->minify();
var_dump($res);
```

```
$sourcePath = 'you_path/demo.js';
$minifier   = new zxf\min\JS($sourcePath);

// 你也可以添加压缩文件内容
// $sourcePath2 = 'you_path/file.css';
// $minifier->add($sourcePath2);

// 保存到磁盘
$minifiedPath = 'you_path/to_min.js';
$minifier->minify($minifiedPath);

// 或者输出内容
$resJs = $minifier->minify();
var_dump($resJs);
```