# 第三方登录


>说明:不同框架可根据实际修改

## QQ 登录

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
>提示:config('callback.qq') 中需要包含3个元素 appid、appkey、callbackUrl


## 新浪微博 登录

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

>提示:config('callback.sina') 中需要包含3个元素 wb_akey、wb_skey、wb_callback_url
