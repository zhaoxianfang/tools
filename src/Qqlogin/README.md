# QQ 登录功能

>说明:基于 thinkphp5 开发

##调用示例
``` php
<?php

use app\common\controller\ControllerBase;
use zxf\Qqlogin\QC;

/**
 * 腾讯QQ 登录
 */
class Tencent extends ControllerBase
{
    public function index()
    {
        die('非法请求');
    }

    /**
     * 处理qq登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @param    string       $jumpUrl [登录完成后跳转的地址]
     * @return   [type]                [description]
     */
    public function login($jumpUrl = '')
    {
        try {
            $qq  = new QC(config('callback.qq'));
            $url = $qq->qq_login();
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        if($jumpUrl){
            session('qq_callback',$jumpUrl);
        }
        $this->redirect($url);
    }

    // qq登录回调函数
    public function callback()
    {
        try {
            $qq = new QC(config('callback.qq'));
            $qq->qq_callback();
            $openId = $qq->get_openid();
            $data   = $qq->get_user_info();
        } catch (\Exception $e) {

            return $this->error('出错啦: ' . $e->getMessage());
        }
        // 拿到用户信息后的处理
        // 快速登录
        $loginUserInfo = $this->logicUser->fastLogin($openId, $data, 'qq');
        //回调地址
        $callUrl = session('qq_callback');
        if($callUrl){
            $this->redirect($callUrl);
        }
        return json(['msg'=>'登录成功','code'=>0,'data'=>$loginUserInfo]);
    }
}
```
>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl