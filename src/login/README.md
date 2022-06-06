# QQ登录
>说明:基于 ThinkPHP6 开发，其他框架可根据实际修改

``` php
<?php

namespace Modules\Callback\Http\Controllers\Web\Tencent;

use Illuminate\Routing\Controller;
use zxf\Qqlogin\QqOauth;

/**
 * QQ 互联
 */
class Connect extends Controller
{

    /**
     * qq登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @param string $jumpUrl [登录完成后跳转的地址 , 跳转地址参数 jumpUrl需要做 urlencode( base64_encode($jumpUrl) 操作]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '')
    {
        $jumpUrl = $jumpUrl ? urldecode($jumpUrl) : '';

        $qq = new QqOauth(config('callback.qq'));

        // $url = $qq->authorization(); // 不传值方式
        $url = $qq->authorization($jumpUrl); // 传入的数据 $jumpUrl 将会在 qq_callback 回调中返回得到

        // 重定向到外部地址
        return redirect()->away($url);
    }

    public function notify()
    {

        $auth  = new QqOauth(config('callback.qq'));
        $token = $auth->getAccessToken();

        $userInfo = $auth->getUserInfo($token);
        $jumpUrl  = $auth->getStateParam();
        dump($jumpUrl);
        dump($userInfo);
        die('notify');
    }
}

```
>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl
