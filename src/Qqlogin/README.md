# QQ 登录功能

>说明:基于 php5 开发

##调用示例
``` php
<<?php
// +---------------------------------------------------------------------
// | 腾讯QQ 登录
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.itzxf.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------
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
            $data    = $qq->get_user_info();
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }

        // 拿到用户信息后的处理
        // 快速登录
        $loginUserInfo = \app\common\model\User::fastLogin($openId, $data, 'qq');

        if ($callUrl) {
            $callUrl = base64_decode($callUrl, true);
            // return redirect($callUrl);
            echo Curl::instance()->buildRequestForm($callUrl, $loginUserInfo);
            exit;
        }
        return json(['msg' => '登录成功', 'code' => 0, 'data' => $loginUserInfo]);

    }
}

```
>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl