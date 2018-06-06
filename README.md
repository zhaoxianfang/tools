# zxf


>基于thinkphp5 的项目模块开发
>调用命名空间 使用 use zxf\…… 例如 use zxf\Qqlogin\QC;  zxf\Wechat\……;

创建时间：2018/06/01

## 涵盖模块
- QQ登录
- 微信模块
- 微博登录模块
- 截图与网页爬数据模块


|  模块  |  需要包含的文件夹  |
| --- | --- |
|  QQ登录  |  Qqlogin  |
|  微信  |  wechat  |
|  截图  |  JonnyW、Psr、Symfony  |
|  微博登录  |  sina  |
|  QueryList  |  QueryList  |


### 截图功能
>参考[安装php-phantomjs](https://segmentfault.com/a/1190000008234820) 改装

``` php


<?php
/**
 * 来源 https://segmentfault.com/a/1190000008234820
 * 截图 OK
 */

// require "vendor\autoload.php";
use zxf\JonnyW\PhantomJs\Client;

// require_once 'vendor_phantomjs/jonnyw/php-phantomjs/src/JonnyW/PhantomJs/Client.php';
$link    = 'https://image.baidu.com/';
$client = Client::getInstance();
$client->getEngine()->setPath('E:/www/zxf_test/bin/phantomjs.exe');
//上面一行要填写自己的phantomjs路径\ linux 换为 二进制文件
/**
 * @see JonnyW\PhantomJs\Http\PdfRequest
 **/
$delay   = 3; //设置延迟时间 秒
$request = $client->getMessageFactory()->createPdfRequest($link, 'GET'); //参数里面的数字5000是网页加载的超时时间，放在网络问题一直加载，可以不填写，默认5s。


// $request->setTimeout($delay+2);//超过指定时间则中断渲染
$request->setDelay($delay); //设置延迟5秒 设置delay是因为有一些特效会在页面加载完成后加载，没有等待就会漏掉

/*截图(图或PDF文件)*/
$request->setRepeatingHeader('<h1>Header <span style="float:right">%pageNum% / %pageTotal%</span></h1>',100);//自定义PDF类的头尾及其高度
$request->setRepeatingFooter('<footer>Footer <span style="float:right">%pageNum% / %pageTotal%</span></footer>',100);//自定义PDF类的头尾
$request->setViewportSize(200, 100);//设置可视宽高
 $request->setBodyStyles(array('backgroundColor' => '#ff0000'));//设置纸张背景色
 $request->setFormat('A4');//设置尺寸格式,如A4
 $request->setOrientation('landscape');//设置纸张方向如纵向
$request->setPaperSize('10cm', '20cm');//PDF纸张大小
$request->setMargin('1cm');//PDF纸张边距
$request->setOutputFile('E:/www/zxf_test/download/file.jpg');//截图或PDF存储路径
$request->setCaptureDimensions(240, 320, 10, 20);//设置截图宽高与边距$width, $height, $top, $left


/**
 * @see JonnyW\PhantomJs\Http\Response
 **/
$response = $client->getMessageFactory()->createResponse();

// Send the request
$client->send($request, $response);

/*响应结果*/
$headers = $response->getHeaders(); //返回头组成的数组
// $response->getHeader();//返回头
$status      = $response->getStatus(); //返回状态码:200则正确,其余错误.
$content     = $response->getContent(); //返回正文
$contentType = $response->getContentType(); //返回正文类型
$url         = $response->getUrl(); //返回请求地址
$redirectUrl = $response->getRedirectUrl(); //返回重定向后的地址
$redirect    = $response->isRedirect(); //返回是否重定向
$console     = $response->getConsole(); //返回JS控制台内容

dump($headers);
dump($status);
dump($content);
dump($contentType);
dump($url);
dump($redirectUrl);
dump($redirect);
dump($console);

```
