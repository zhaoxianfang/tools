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
|  微信  |  wechat(未完成)  |
|  截图  |  JonnyW、Psr、Symfony  |
|  微博登录  |  sina  |
|  QueryList  |  QueryList  |


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
### QQ登录
>说明:基于 ThinkPHP6 开发，其他框架可根据实际修改

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
>说明:基于 ThinkPHP6 开发，其他框架可根据实际修改
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
