# zxf


>基于 php 的项目模块开发
>调用命名空间 使用 use zxf\…… 例如 use zxf\Qqlogin\QC;  use zxf\String\JsMin;

创建时间：2018/06/01

## 引用
```
composer require zxf/tools
```

## 涵盖模块


|  模块  |  需要包含的文件夹  |
| --- | --- |
|  QQ登录  |  Qqlogin  |
|  微信  |  wechat(未完成)  |
|  截图  |  JonnyW、Psr、Symfony(废弃)  |
|  微博登录  |  sina  |
|  QueryList  |  QueryList(废弃)  |
|  JsMin  |  js 压缩工具  |
|  QrCode  |  文字生成二维码  |
|  Compressor  |  图片压缩类  |
|  TextToPNG  |  文字转图片  |
|  PHPMailer  |  发送邮件  |
|  Curl  |  http 网络请求  |
|  Sms  |  发送短信: ali(阿里云)[默认] 或者 tencent（腾讯云）  |
|  MysqlTool  |  创建mysql数据库字典  |
|  Img  |  修改图片尺寸、给图片上添加文字等  |
|  Pinyin  |  中文转拼音  |
|  Menu  |  生成目录菜单(adminlte|layuiadmin|nazox|inspinia)  |
|  Random  |  生成随机数  |


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


### Curl 网络请求
``` php

Curl::instance()->setParams(json_encode(['path'=>'pages/index/index']))->post($url,'text');

```

### QQ登录
>说明:此demo 为 ThinkPHP6 ，其他框架可根据实际修改

``` php
<?php
namespace app\callback\controller;

use app\common\controller\Base;
use zxf\Qqlogin\QC;

class Tencent extends Base
{
    public function index()
    {
        die('非法请求');
    }

    /**
     * 处理qq登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @param    string       $jumpUrl      [登录完成后跳转的地址 , 跳转地址参数 jumpUrl需要做 urlencode( base64_encode($jumpUrl) 操作]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '')
    {
        $jumpUrl = $jumpUrl ? urldecode($jumpUrl) : '';
        try {
            $qq  = new QC(config('callback.qq'));
            // $url = $qq->qq_login(); // 不传值方式
            $url = $qq->qq_login($jumpUrl); // 传入的数据 $jumpUrl 将会在 qq_callback 回调中返回得到
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        return redirect($url);
    }

    // qq登录回调函数
    public function callback()
    {
        try {
            $qq      = new QC(config('callback.qq'));
            $jumpUrl = $qq->qq_callback(); // 如果 qq_login 传入了值则 $res 的值为传入数据；如果 qq_login 没有传值则 $res 的值为 null
            $openId  = $qq->get_openid();
            $qq      = new QC(config('callback.qq'));
            $userInfo= $qq->get_user_info();
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        // 得到 $jumpUrl 的传入值和 用户数据$userInfo
        // TODO ..

    }
}
```

### 新浪微博登录

>说明:此demo 为 ThinkPHP6 ，其他框架可根据实际修改

``` php
<?php
namespace app\callback\controller;

use app\common\controller\Base;
use zxf\sina\SaeTOAuthV2;

class Sina extends Base
{

    public function index()
    {
        die('非法请求');
    }

    /**
     * 登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $jumpUrl      [登录成功后跳转地址 跳转地址参数 jumpUrl需要做 urlencode( base64_encode($jumpUrl) 操作]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '')
    {
        $jumpUrl = $jumpUrl ? urldecode($jumpUrl) : '';
        try {
            $wbConfig = config('callback.sina');
            $o        = new SaeTOAuthV2($wbConfig);
            // $code_url = $o->getAuthorizeURL(); // 不传入数据 
            $code_url = $o->getAuthorizeURL($jumpUrl); // 如果传入数据 会在 sina_callback 中返回在 customize_data 值中
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        //跳转到授权页面
        return redirect($code_url);
    }

    /**
     * 微博回调
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $value [description]
     */
    public function callback()
    {

        if (!isset($_REQUEST['code'])) {
            return $this->error('非法请求');
        }

        $wbConfig = config('callback.sina');

        try {
            $o   = new SaeTOAuthV2($wbConfig);
            $res = $o->sina_callback(); // 自定义
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }

        // $res['user_info']  // 微信用户信息
        // $res['uid'] // 微博uid 类似于 open_id
        // $res['customize_data']// getAuthorizeURL 传入的自定义数据,不传时候为 NULL

    }

    // 回调函数
    public function unauth()
    {
        die("取消授权");
    }
}
```

### jsMin 压缩

``` php
use zxf\String\JsMin;
$minifiedCode = JsMin::minify($jsString);
```



### QrCode 创建二维码

``` php

$text         = 'https://www.itzxf.com/';
$filename     = false; //二维码图片保存路径(若不生成文件则设置为false)
$level        = "L"; //二维码容错率，默认L
$size         = '6'; //二维码图片每个黑点的像素，默认4
$padding      = 2; //二维码边框的间距，默认2
$saveandprint = false; //保存二维码图片并显示出来，$filename必须传递文件路径
QrCode::png($text, $filename, $level, $size, $padding, $saveandprint);
die;
```


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
    $mail = new PHPMailer(true);
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
 *        $Compressor = new Compressor(); 
 *        OR 
 *        $Compressor = Compressor::instance()
 *        # 仅压缩
 *        $result = $Compressor->set('001.jpg', './compressOnly.png')->compress(5)->get();
 *        # 仅改变尺寸
 *        $result = $Compressor->set('001.jpg', './resizeOnly.jpg')->resize(500, 500)->get();
 *        # 压缩且改变尺寸
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->resize(0, 500)->compress(5)->get();
 *        #  压缩且按照比例压缩
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->proportion(0.5)->compress(5)->get();
 *        return $result;
 *  参数说明：
 *        set(原图路径,保存后的路径);
 *        resize(设置宽度,设置高度);//如果有一个参数为0，则保持宽高比例
 *        proportion(压缩比例);//0.1~1 根据比例压缩
 *        compress(压缩级别);//0~9，压缩级别，级别越高 图片越小
 *        get();//获取生成后的结果
 *  提示：
 *        如果使用到compress 方法，先设置其他参数最后一步再执行 compress 压缩方法
 */
```


### TextToPNG 文字转图片
``` php
use zxf\img\TextToPNG;

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
