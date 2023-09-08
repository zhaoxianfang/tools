# phantomjs

当前版本 2.1.1
需要时请移步 [phantomjs](https://phantomjs.org/download.html) 下载

然后放到把对应的可执行文件放置到 自己服务器 文件夹下

文档地址
https://phantomjs.org/api/

## 网页截图
> 使用前需要提前到[phantomjs](https://phantomjs.org/download.html) 下载相应的可执行应用程序

> 使用 `proc_open` 调用 `phantomjs` 执行截图操作

### 单个网页截图
```
use zxf\ScreenShot\ScreenShot;

// $softPath： 可执行文件phantomjs或者phantomjs.exe 所在目录； ScreenShot 会自动识别系统 $softPath 该使用 phantomjs 还是 phantomjs.exe
// $url: 被截图网页  url
// $savePath: 截图成功后的保存文件完整地址
$res = ScreenShot::init($softPath='/Users/linian/extend')->setUrl($url = 'http://www.baidu.com')->setWaitTime(2500)->run($savePath = __DIR__.'/img/'.time().'.png');

$res 返回 true|fales 表示是否截图成功
```

### 多个网页截图
```
$urls = [
    [
       'url'=>"https://www.runoob.com",  // 采集的网址
       'save_path'=>"./file_runoob.png", // 保存的文件路径
   ],
   [
       'url'=>"https://360.cn",
       'save_path'=>"./file_360.png",
   ]
];
$res = ScreenShot::init($softPath='/Users/linian/extend')->setUrl($urls)->setWaitTime(2500)->run();

```