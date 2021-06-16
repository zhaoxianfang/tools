<?php
// +---------------------------------------------------------------------
// | Curl
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.itzxf.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

namespace zxf\req; 

class Curl
{
    private $ch         = null; // curl 句柄
    private $httpParams = null;

    /**
     * @var object 对象实例
     */
    protected static $instance;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * 判断资源类型是否需要初始化 CURL,防止第二次调用时候第一次的资源被释放
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-29
     * @return   [type]       [description]
     */
    private function initCurl()
    {
        if (empty($this->ch) || get_resource_type($this->ch) == 'Unknown') {
            $this->ch = curl_init();
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 设置http header
     * @param $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->initCurl();

        $contentType = array(
            "Content-type:application/x-www-form-urlencoded",
            "Content-type:application/json;charset='utf-8'",
        );
        if (is_array($header)) {
            //          curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
            //          $headers = array_merge($contentType,$header);
            foreach ($header as $key => $head) {
                if ($head) {
                    $contentType[] = $key . ':' . $head;
                }
            }
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $contentType);

        return $this;
    }

    /**
     * 设置http 超时
     * @param int $time
     * @return $this
     */
    public function setTimeout($time)
    {
        $this->initCurl();
        // 不能小于等于0
        if ($time <= 0) {
            $time = 5;
        }
        //只需要设置一个秒的数量就可以
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $time);
        return $this;
    }

    /**
     * 设置http 代理
     * @param string $proxy
     * @return $this
     */
    public function setProxy($proxy)
    {
        $this->initCurl();
        if ($proxy) {
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
        }
        return $this;
    }

    /**
     * 设置http 代理端口
     * @param int $port
     * @return $this
     */
    public function setProxyPort($port)
    {
        $this->initCurl();
        if (is_int($port)) {
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
        }
        return $this;
    }

    /**
     * 设置来源页面
     * @param string $referer
     * @return $this
     */
    public function setReferer($referer = "")
    {
        $this->initCurl();
        if (!empty($referer)) {
            curl_setopt($this->ch, CURLOPT_REFERER, $referer);
        }

        return $this;
    }

    /**
     * 设置用户代理
     * @param string $agent
     * @return $this
     */
    public function setUserAgent($agent = "")
    {
        $this->initCurl();
        if ($agent) {
            // 模拟用户使用的浏览器
            curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);
        }
        return $this;
    }

    // 复制句柄
    public function copyCurl()
    {
        $this->initCurl();
        return curl_copy_handle($this->ch);
    }

    // 重置所有的预先设置的选项
    public function reset()
    {
        $this->initCurl();
        curl_reset($this->ch);
        return $this;
    }

    /**
     * http响应中是否显示header，1表示显示
     * @param $show
     * @return $this
     */
    public function showResponseHeader($show)
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_HEADER, $show);
        return $this;
    }

    /**
     * 设置http请求的参数,get或post
     * @param array $params
     * @return $this
     * setParams( array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
     */
    public function setParams($params)
    {
        $this->initCurl();
        $this->httpParams = $params;
        return $this;
    }

    /**
     * 设置证书路径
     * @param $file
     */
    public function setCainfo($file)
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_CAINFO, $file);
    }

    // 获取cURL版本数组
    public function version()
    {
        return curl_version();
    }

    /**
     * 模拟GET请求
     *
     * @param string $url
     * @param string $data_type
     *
     * @return mixed
     *
     * Examples:
     * ```
     * HttpCurl::get('http://api.example.com/?a=123&b=456', 'json');
     * ```
     */
    public function get($url, $data_type = 'json')
    {
        $this->initCurl();

        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        // 设置get参数
        if (!empty($this->httpParams) && is_array($this->httpParams)) {
            if (strpos($url, '?') !== false) {
                $url .= http_build_query($this->httpParams);
            } else {
                $url .= '?' . http_build_query($this->httpParams);
            }
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        return $this->run($data_type);
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @param string $data_type
     *
     * @return mixed
     *
     * Examples:
     * ```
     * Http->post('http://api.example.com/?a=123',  'json');
     * Http->post('http://api.example.com/',  'json');
     * 文件post上传
     * HttpCurl::post('http://api.example.com/', 'json');
     * ```
     */
    public function post($url, $data_type = 'json')
    {
        $this->initCurl();

        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());

        // 设置post body
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        return $this->run($data_type);
    }

    public function put($url, $data_type = 'json')
    {
        $this->initCurl();
        // $data = json_encode($this->httpParams);
        curl_setopt($this->ch, CURLOPT_URL, $url); //设置请求的URL
        // curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT"); //设置请求方式
        // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); //设置提交的字符串
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        // 设置post body
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        return $this->run($data_type);
    }

    public function delete($url, $data_type = 'json')
    {
        $this->initCurl();
        // $data = json_encode($data);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        return $this->run($data_type);
    }

    public function patch($url, $data_type = 'json')
    {
        $this->initCurl();
        // $data = json_encode($data);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); //20170611修改接口，用/id的方式传递，直接写在url中了
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        return $this->run($data_type);
    }

    protected function run($data_type = 'json')
    {
        $content = curl_exec($this->ch);
        $errInfo = curl_error($this->ch);
        $status  = curl_getinfo($this->ch);
        curl_close($this->ch);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content);
            }
        } else {
            $content = [
                'code'     => 500,
                'msg'      => '【系统提示】请求服务器网络不通或者出现请求异常',
                'html'     => $content,
                'err_info' => $errInfo,
            ];
            return $content;
        }
        // return $content;
        return $this->objectArray($content);
    }

    //PHP stdClass Object转array
    protected function objectArray($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->objectArray($value);
            }
        }
        return $array;

    }

    /**
     * @desc 异步将远程链接上的内容(图片或内容)写到本地
     * @param $url    远程地址
     * @param $saveName    保存在服务器上的文件名
     * @param $path    保存路径
     * @return boolean 当返回为true时，代表成功，反之，为失败
     */
    public function putFileFromUrlContent($url, $saveName, $path)
    {
        // 设置运行时间为无限制
        set_time_limit(0);
        $url = trim($url);
        // 设置你需要抓取的URL
        curl_setopt($this->ch, CURLOPT_URL, $url);
        // 设置header
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        //这个是重点，加上这个便可以支持http和https下载
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        // 运行cURL，请求网页
        $file = curl_exec($this->ch);
        // 关闭URL请求
        curl_close($this->ch);
        // 将文件写入获得的数据
        $filename = $path . $saveName;
        $write    = @fopen($filename, "w");
        if ($write == false) {
            return false;
        }
        if (fwrite($write, $file) == false) {
            return false;
        }
        if (fclose($write) == false) {
            return false;
        }
        return true;
    }

    /**
     * 建立跳转请求表单
     * @param string $url 数据提交跳转到的URL
     * @param array $data 请求参数数组
     * @param string $method 提交方式：post或get 默认post
     * @return string 提交表单的HTML文本
     * 示例
     * $url = 'http://www.itzxf.com/';
     * $data = array(
     *   'name' => 'aaa',
     *   'domain' => 'itzxf.com',
     *   'date' => '2019-03-22'
     * );
     * echo buildRequestForm($url, $data);
     */
    public function buildRequestForm($url = '', $data, $method = 'post')
    {
        $formId = 'requestForm_' . rand();
        $sHtml  = "<form id='" . $formId . "' name='requestForm' action='" . $url . "' method='" . $method . "'>";
        foreach ($data as $key => $val) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "' />";
        }
        $sHtml = $sHtml . "<input type='submit' value='确定' style='display:none;'></form>";
        $sHtml = $sHtml . "<script>document.forms['" . $formId . "'].submit();</script>";
        return $sHtml;
    }

    /**
     * 生成随机的userAgent
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-28
     * @return   [type]       [description]
     */
    protected function randUserAgent()
    {
        $agentarry = [
            //PC端的UserAgent
            "safari 5.1 – MAC"             => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
            "safari 5.1 – Windows"         => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
            "Firefox 38esr"                => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
            "IE 11"                        => "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
            "IE 9.0"                       => "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
            "IE 8.0"                       => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
            "IE 7.0"                       => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
            "IE 6.0"                       => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
            "Firefox 4.0.1 – MAC"          => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Firefox 4.0.1 – Windows"      => "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Opera 11.11 – MAC"            => "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
            "Opera 11.11 – Windows"        => "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
            "Chrome 17.0 – MAC"            => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
            "傲游（Maxthon）"                  => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",
            "腾讯TT"                         => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",
            "世界之窗（The World） 2.x"          => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
            "世界之窗（The World） 3.x"          => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",
            "360浏览器"                       => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",
            "搜狗浏览器 1.x"                    => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",
            "Avant"                        => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",
            "Green Browser"                => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
            //移动端口
            "safari iOS 4.33 – iPhone"     => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "safari iOS 4.33 – iPod Touch" => "Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "safari iOS 4.33 – iPad"       => "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "Android N1"                   => "Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Android QQ浏览器 For android"    => "MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Android Opera Mobile"         => "Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10",
            "Android Pad Moto Xoom"        => "Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13",
            "BlackBerry"                   => "Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+",
            "WebOS HP Touchpad"            => "Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.0; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/233.70 Safari/534.6 TouchPad/1.0",
            "UC标准"                         => "NOKIA5700/ UCWEB7.0.2.37/28/999",
            "UCOpenwave"                   => "Openwave/ UCWEB7.0.2.37/28/999",
            "UC Opera"                     => "Mozilla/4.0 (compatible; MSIE 6.0; ) Opera/UCWEB7.0.2.37/28/999",
            "微信内置浏览器"                      => "Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN",

        ];

        return $agentarry[array_rand($agentarry, 1)]; //随机浏览器useragent
    }
}
