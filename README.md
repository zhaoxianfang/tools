# zxf

![](https://img.shields.io/github/stars/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/forks/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/tag/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/release/zhaoxianfang/tools.svg) ![](https://img.shields.io/github/issues/zhaoxianfang/tools.svg)

> 基于 php 的项目模块开发
> 调用命名空间 使用 use zxf\…… 例如 use zxf\login\QqOauth; use zxf\min\JsMin;

创建时间：2018/06/01

## 引用

```
composer require zxf/tools
```

## 涵盖模块

| 模块         | 需要包含的文件夹/说明                                                                                     |
|------------|-------------------------------------------------------------------------------------------------|
| QQ登录       | Qqlogin                                                                                         |
| 微信         | WeChat                                                                                          |
| 截图         | ScreenShot                                                                                      |
| 微博登录       | sina                                                                                            |
| JsMin      | js 压缩工具                                                                                         |
| QrCode     | 生成二维码                                                                                           |
| BarCode    | 生成条形码 (支持Code128、Code11、Code39、Code39Extended、Ean128、Gs1128、I25、Isbn、Msi、Postnet、S25、Upca、Upce) |
| Compressor | 图片压缩类                                                                                           |
| Cache      | 文件缓存                                                                                            |
| TextToPNG  | 文字转图片                                                                                           |
| PHPMailer  | 发送邮件                                                                                            |
| Curl       | http 网络请求                                                                                       |
| Sms        | 发送短信: ali(阿里云)[默认] 或者 tencent（腾讯云）                                                              |
| MysqlTool  | 创建mysql数据库字典                                                                                    |
| Img        | 修改图片尺寸、给图片上添加文字等                                                                                |
| Pinyin     | 中文转拼音                                                                                           |
| Menu       | 生成目录菜单(adminlte、layuiadmin、nazox、inspinia)                                                      |
| Random     | 生成随机数                                                                                           |
| ImgToIco   | 图片转ico 格式                                                                                       |
| Modules    | laravel 多模块应用                                                                                   |
| Command    | 命令行解析工具                                                                                         |
| Tree       | 树形结构化                                                                                           |
| dom        | 简单快速的 HTML 解析器，此模块来源：https://github.com/Imangazaliev/DiDOM                                      |
| Db/Model   | Mysql 的基础操作类Db;封装调用类Model                                                                       |
| 其他         | 还有一些没有写在此处的工具类                                                                                  |

### 第三方登录

```php
<?php
/**
 * 第三方登录回调配置参数
 */
return [
    //微博
    'sina'   => [
        'wb_akey'         => '',
        'wb_skey'         => '',
        'wb_callback_url' => '', //回调
    ],
    //QQ
    'qq'     => [
        'appid'       => '',
        'appkey'      => '',
        'callbackUrl' => '',
    ],
    //微信
    'wechat' => [
        'token'                  => '', //填写你设定的key
        'encodingaeskey'         => '', //填写加密用的EncodingAESKey
        'appid'                  => '', //填写高级调用功能的app id
        'appsecret'              => '', //填写高级调用功能的密钥
        'GetAccessTokenCallback' => '', //回调地址
        'cache_path'             => '', //插件 缓存目录
    ],

];

```

### 微信

```php
// 直播间
use zxf\WeChat\Live\LiveRoom;
$this->sdk = LiveRoom::instance($this->config);
```

```php
// 永久素材
use zxf\WeChat\Material\PermanentFiles;
$this->sdk = PermanentFiles::instance($this->config);
```

### Curl 网络请求

``` php

zxf\tools\Curl::instance()->setParams(['path'=>'pages/index/index'])->post($url,'json');

```

### QQ登录

> 说明:不同框架可根据实际修改

``` php
<?php

use zxf\login\QqOauth;

/**
 * QQ 互联登录
 */
class Connect extends Controller
{
    /**
     * qq登录
     *
     * 可以在url 中传入 参数 callback_url 用来做通知回调 ； 例如 xxx.com/callback/tencent/login?callback_url=http%3A%2F%2Fwww.a.com%2Fa%2Fb%2Fc%3Fd%3D123
     * callback_url 参数说明 传入前需要做 urlencode($callback_url) 操作
     * callback_url 回调地址要求允许跨域或者 csrf
     */
    public function login()
    {
        $jump_url = request()->get('callback_url', '');
        $jumpUrl  = $jump_url ? urldecode($jump_url) : '';

        $qq = new QqOauth(config('callback.qq'));

        // $url = $qq->authorization(); // 不传值方式
        $url = $qq->authorization($jumpUrl); // 传入的数据 $jumpUrl 将会在 qq_callback 回调中返回得到

        // 重定向到外部地址
        return redirect()->away($url);
    }

    public function notify()
    {
        $auth        = new QqOauth(config('callback.qq'));
        $userInfo    = $auth->getUserInfo('');
        $callbackUrl = $auth->getStateParam();

        // 记录用户信息
        $loginUserInfo = UserServices::instance()->fastLogin('qq', $userInfo);
        if ($callbackUrl) {
            return buildRequestFormAndSend($callbackUrl, $loginUserInfo);
        } else {
            dump($loginUserInfo);
        }
    }
}

```

> 提示:config('callback.qq') 中需要包含3个元素 appid、appkey、callbackUrl

### 新浪微博登录

> 说明:不同框架可根据实际修改

``` php
<?php

use zxf\login\WeiboOauth;

/**
 * 新浪微博登录
 */
class Sina extends Controller
{
    /**
     * qq登录
     *
     * 可以在url 中传入 参数 callback_url 用来做通知回调 ； 例如 xxx.com/callback/weibo/login?callback_url=http%3A%2F%2Fwww.a.com%2Fa%2Fb%2Fc%3Fd%3D123
     * callback_url 参数说明 传入前需要做 urlencode($callback_url) 操作
     * callback_url 回调地址要求允许跨域或者 csrf
     */
    public function login()
    {
        $jump_url = request()->get('callback_url', '');
        $jumpUrl  = $jump_url ? urldecode($jump_url) : '';

        $weibo = new WeiboOauth(config('callback.sina'));

        // $url = $qq->authorization(); // 不传值方式
        $weibo->authorization($jumpUrl); // 传入的数据 $jumpUrl 将会在 qq_callback 回调中返回得到

    }

    public function notify()
    {
        $auth        = new WeiboOauth(config('callback.sina'));
        $userInfo    = $auth->getUserInfo('');
        $callbackUrl = $auth->getStateParam();

        // 记录用户信息
        $loginUserInfo = UserServices::instance()->fastLogin('sina', $userInfo);

        if ($callbackUrl) {
            return buildRequestFormAndSend($callbackUrl, $loginUserInfo);
        } else {
            dump($userInfo);
        }
    }
}


```

> 提示:config('callback.sina') 中需要包含3个元素 wb_akey、wb_skey、wb_callback_url

### Cache 文件缓存

#### 实例化

```php
use zxf\tools\Cache;
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
 //a:3:{s:8:"contents";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:34;i:4;i:5;i:5;i:6;i:6;i:6;}s:6:"expire";i:0;s:5:"mtime";i:1318218422;}
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

### jsMin 压缩

``` php
use zxf\min\JsMin;
$minifiedCode = JsMin::minify($jsString);
```

### QrCode 创建二维码

``` php
use zxf\qrcode\QrCode;

echo '<p>Example - QrCode</p>';
$qrCode = new QrCode();
$qrCode
    ->setText('https://www.weisifang.com/apidoc') // 生成二维码的内容
    ->setSize(200) // 设置二维码大小
    ->setPadding(10) // 设置边距
    ->setErrorCorrection('high') // 设置二维码纠错级别。 分为 high(30%)、quartile(25%)、medium(15%)、low(7%) 几种
    ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0)) // 设置颜色
    ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0)) // 设置背景色
    ->setLabel('在线文档|起跑线') // 设置图片下面的文字
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
    ->setLabel('在线文档|起跑线') // 设置图片下面的文字
    ->setLabelFontSize(16) // 设置文字字体大小
    ->setImageType(QrCode::IMAGE_TYPE_PNG) // 设置图片类型 ,默认为 png
    ->draw() // 把图片直接绘画到浏览器
    ;
```

### BarCode 创建条形码

例如：

```php
echo '<p>Example - Isbn</p>';
$barcode = new BarCode(); // 实例化
$barcode->setText("0012345678901"); // 设置条形码内容
$barcode->setFontSize(10); //  设置字体大小
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

#### 其他参数

设置分辨率 `$barcode->setScale(2);`
设置高度 `$barcode->setThickness(25);`
`GS1-128`删除 48 个字符的限制 `$barcode->setNoLengthLimit(true);`
`GS1-128`允许未知标识符 `$barcode->setAllowsUnknownIdentifier(true);`

### PHPMailer 发送邮件

``` php

/**
 * 发送邮件
 * @Author   ZhaoXianFang
 * @DateTime 2019-11-27
 * @param    string       $to      [接收人]
 * @param    string       $title   [邮件标题]
 * @param    string       $content [邮件内容]
 * @param    string       $sender  [发件人]
 * @param    boolean      $isHtml  [是否为html页面]
 * @return   [type]                [true|false]
 * @example   send_mailer('111@qq.com', '邮件测试标题', $content,'发送人', $isHtml = true);
 * @example   send_mailer(['111@qq.com'=>'小张'],'邮件测试标题', $content,'发送人', $isHtml = true);
 * @example   send_mailer(['111@qq.com','222@qq.com'], '邮件测试标题', $content,'发送人', $isHtml = true);
 * @example   send_mailer([['111@qq.com'=>'小张'], ['222@qq.com'=>'无我']], '邮件测试标题', $content,'发送人', $isHtml = true);
 */
function send_mailer($to = '', $title = '', $content = '', $sender = '邮件测试', $isHtml = false)
{
    if (!$to || !$title || !$content) {
        return false;
    }
    $mail = new \zxf\req\PHPMailer(true);
    try {
        //使用STMP服务
        $mail->isSMTP();
        //这里使用我们第二步设置的stmp服务地址
        $mail->Host = "smtp.qq.com";
        //设置是否进行权限校验
        $mail->SMTPAuth = true;
        //第二步中登录网易邮箱的账号
        $mail->Username = "邮件来源@qq.com";
        //客户端授权密码，注意不是登录密码
        $mail->Password = "客户端授权密码";
        //使用ssl协议
        $mail->SMTPSecure = 'ssl';
        //端口设置
        $mail->Port = 465;
        //字符集设置，防止中文乱码
        $mail->CharSet = "utf-8";
        //设置邮箱的来源，邮箱与$mail->Username一致，名称随意
        $mail->setFrom("邮件来源@qq.com", $sender);

        //设置回复地址，一般与来源保持一直
        $mail->addReplyTo("邮件来源@qq.com", "邮件反馈");
        // $mail->AddAttachment('xx.xls','我的附件.xls'); // 添加附件,并指定名称

        $mail->isHTML(true);
        //标题
        $mail->Subject = $title;
        //正文
        if ($isHtml) {
            $mail->msgHTML($content);
        } else {
            $mail->Body = $content;
        }
        //设置收件的邮箱地址
        if (is_string($to)) {
            $mail->addAddress($to);
        } else {
            foreach ($to as $key => $userEmail) {
                if(is_array($userEmail)){
                    foreach ($userEmail as $user_email => $user_name) {
                        if (is_string($user_email)) {
                            $mail->addAddress($user_email, $user_name);
                        } else {
                            $mail->addAddress($user_name);
                        }
                    }
                }else{
                    if (is_string($key)) {
                        $mail->addAddress($key, $userEmail);
                    } else {
                        $mail->addAddress($firstVal);
                    }
                }

            }
        }
        $mail->send();
        return true;
    } catch (\Exception $e) {
        return $mail->ErrorInfo;
        // return false;
    }
}
```

### Compressor 图片压缩类

``` php
/**
 * 功能：图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
 * @Author   ZhaoXianFang
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

### TextToPNG 文字转图片

``` php
use zxf\tools\TextToPNG;

$text = 'hello';
$color = '#ffffff';
$bgcolor = '#cccccc';
$rotate = 0;
$font = 'diandian'; // 使用的字体

TextToPNG::instance()->setFontStyle($font)->setText($text)->setSize('900', '500')->setColor($color)->setBackgroundColor($bgcolor)->setTransparent(false)->setRotate($rotate)->draw();
```

#### 附 TextToPNG 文字转图片 可使用的字体参照

```
yuanti        圆体
diandain      点点像素体-方形
diandain_yt   点点像素体-圆形
diandain_lx   点点像素体-菱形
lishu         隶书
qiuhong       秋鸿楷体
taiwan_lishu  台湾隶书
xingshu       行书
code          代码体
caoshu        草书
kaiti         方正楷体简体
fangsong      方正仿宋简体
oppo          OPPO官方字体
ali_puhui     阿里巴巴普惠体2.0
baotuxiaobai  包图小白体
heiti         方正黑体简体
honglei       鸿雷板书简体
haoshenti     优设好身体
myshouxie     沐瑶软笔手写体
foxi          佛系体
```

### Sms 发送短信

``` php
use zxf\sms\Sms;

$accessKeyId     = "阿里云或者腾讯云 appid";
$accessKeySecret = "阿里云或者腾讯云 secret";

// 可发送多个手机号，变量为数组即可，如：[11111111111, 22222222222]
$mobile   = '18***888';
$template = '您申请的短信模板';
$sign     = '您申请的短信签名';

// 短信模板中用到的 参数 模板变量为键值对数组
$params = [
    "code"    => rand(1000, 9999),
    "title"   => '您的标题',
    "content" => '您的内容',
];

// 初始化 短信服务（阿里云短信或者腾讯云短信）
$smsObj = Sms::instance($accessKeyId, $accessKeySecret,'ali或者tencent');

// 若使用的是 腾讯云短信 需要 设置 appid 参数; 阿里云则不用
// $smsObj = $smsObj->setAppid($appid);

// 发起请求
// 需要注意，设置配置不分先后顺序，send后也不会清空配置 
$result    = $aliyunSms->setMobile($mobile)->setParams($params)->setTemplate($template)->setSign($sign)->send();
/**
 * 返回值为bool，你可获得阿里云响应做出你业务内的处理
 *
 * status bool 此变量是此包用来判断是否发送成功
 * code string 阿里云短信响应代码
 * message string 阿里云短信响应信息
 */
if (!$result) {
    $response = $aliyunSms->getResponse();
    // 做出处理
}

```

### 图片转ICO格式

```
 $imgurl = "./test.jpeg";
 // 下载到浏览器
 zxf\tools\ImgToIco::instance()->set($imgurl, 32)->generate();
 // 保存到指定文件夹
 zxf\tools\ImgToIco::instance()->set($imgurl, 32)->generate('E:/www');
```

### Tree 树形结构化,

```
$arr = array(
     array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
)
$tree = zxf\tools\Tree::instance()->init($arr, 'id', 'pid', 'childlist');

// 添加节点 会自动补上 pid
$tree->addNode(3,array('id'=>'8','name'=>'二级栏目一的子节点'));
// 删除节点
$tree->delNode(6);
// 修改节点 ,会自动补上 id 和 pid
$tree->changeNode(5,array('name'=>'二级栏目三的修改项'));
// 通过id查找节点
$tree->find(5);
// 通过id查找子节点
$tree->childTree(5);
// 通过id查找父节点
$tree->parentTree(5);

// 获取tree
$tree->getTree();
```

### 网页截图

> 使用前需要提前到[phantomjs](https://phantomjs.org/download.html) 下载相应的可执行应用程序

```
use zxf\ScreenShot\ScreenShot;

// $softPath： 可执行文件phantomjs或者phantomjs.exe 所在目录； ScreenShot 会自动识别系统 $softPath 该使用 phantomjs 还是 phantomjs.exe
// $url: 被截图网页  url
// $savePath: 截图成功后的保存文件完整地址
$res = ScreenShot::init($softPath='/Users/linian/extend')->setUrl($url = 'https://www.weisifang.com')->run($savePath = __DIR__.'/img/'.time().'.png');

$res 返回 true|fales 表示是否截图成功
```

### laravel 多模块应用

[多模文档说明](README_laravel.md)

### Command 命令行参数解析

> 在项目根目录新建一个`command` 脚本，测试内容如下

```
/**
 * 命令行参数解析工具类
 */
#!/usr/bin/env php
<?php

$cmd = new zxf\tools\Command::instance();

// 获取所有参数值
$cmd->all();

// 解析选项 port
$cmd->option('port', function ($val) {
   // $val port选项传入的值
   echo 'Option port handler=》.$val;
});

// 解析参数 test
$cmd->args('test', function ($bool){
$bool 是否解析到 test true|false
    if($bool){
        // 传入了 test
    }else{
       //未传入 test
    }
});

// 获取所有Opts的值
$cmd->getOptVal();
// 获取 port 的值 ，没有则返回null
$cmd->getOptVal('post');

// 获取所有Args的值
$cmd->getArgVal();
// 获取 是否传入 test 的 ，返回true|false
$cmd->getArgVal('test');

/**
 * 调用 demo:  php command --port 3307 -c 100 -hlocal -g test
 * 传入参数说明：
 *    --opts参数名称 加 空格 加 opts参数值 例如：--port 3307 表示 port 的值为 3307      ; 返回到 opts 中
 *    -opts参数名称 加 空格 加 opts参数值 例如：-c 100 表示 c 的值为 100                ; 返回到 opts 中
 *    -opts参数简称「单字母」 不加空格 接opts参数值 例如：-hlocal 表示  的值为 local      ; 返回到 opts 中
 *    -opts参数简称「单字母」 例如：-g 表示 传入了参数 g                                ; 返回到 opts 中
 *    参数名称 例如：test 表示 传入了参数 test                                         ; 返回到 args 中
 */
```


