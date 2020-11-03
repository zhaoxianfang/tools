# QQ登录
>说明:基于 ThinkPHP6 开发，其他框架可根据实际修改

``` php
<?php
namespace app\callback\controller;

use app\common\controller\Base;
use util\Curl;
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
            $url = $qq->qq_login($jumpUrl);
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
            $callUrl = $qq->qq_callback();
            $openId  = $qq->get_openid();
            $qq      = new QC(config('callback.qq'));
            $data    = $qq->get_user_info();
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        // 得到 $res 的传入值和 用户数据$userInfo
        // TODO ..

    }
}
```
>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl