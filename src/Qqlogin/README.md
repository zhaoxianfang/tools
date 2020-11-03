# QQ登录
>说明:基于 ThinkPHP6 开发，其他框架可根据实际修改

``` php
<?php
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
>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl