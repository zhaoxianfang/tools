# QQ 登录功能

>说明:基于 thinkphp5 开发

##调用示例

    <?php

	use zxf\qqlogin\QC;

	……

	// 处理qq登录
    public function login()
    {
        try {
            $qq  = new QC(config('qq'));
            $url = $qq->qq_login();
        } catch (\Exception $e) {
            die('出错啦: ' . $e->getMessage());
        }
        $this->redirect($url);
    }

    // qq登录回调函数
    public function callback()
    {
        try {
            $qq = new QC(config('qq'));
            $qq->qq_callback();
            $openId = $qq->get_openid();
            $data   = $qq->get_user_info();
        } catch (\Exception $e) {

            die('出错啦: ' . $e->getMessage());
        }
        // 拿到用户信息后的处理
        #TODO
        
    }

>提示:config('qq') 中需要包含3个元素 appid、appkey、callbackUrl