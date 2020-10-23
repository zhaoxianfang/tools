## 微博登录

### 使用示例
``` php
<?php
// +---------------------------------------------------------------------
// | 新浪微博 登录
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
use zxf\sina\SaeTOAuthV2;

class Sina extends Base
{

    public function index()
    {
        die('非法请求');
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
        // $res['customize_data']// getAuthorizeURL 的第二个自定义数据,不传时候为 NULL
        // TODO ...

    }

    /**
     * 登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $jumpUrl      [登录成功后跳转地址]
     * @param    string       $loginModel   [登录作用的模块、不同模块session名称不同]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '')
    {
        try {
            $wbConfig = config('callback.sina');
            $o        = new SaeTOAuthV2($wbConfig);
            $code_url = $o->getAuthorizeURL($jumpUrl); // 如果传入数据 会在 sina_callback 中返回在 customize_data 值中
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        //跳转到授权页面
        return redirect($code_url);
    }

    // 回调函数
    public function unauth()
    {
        die("取消授权");
    }
}

```