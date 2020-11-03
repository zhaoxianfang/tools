## 微博登录

### 使用示例
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