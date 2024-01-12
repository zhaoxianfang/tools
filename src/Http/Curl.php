<?php
// +---------------------------------------------------------------------
// | Curl
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

namespace zxf\Http;

use Exception;

class Curl
{
    private $ch         = null; // curl 句柄
    private $httpParams = null;

    /**
     * @var object 对象实例
     */
    protected static $instance;

    public function __construct($config = [])
    {
        if (!function_exists('curl_init')) {
            throw new Exception('不支持CURL功能.');
        }
        $this->ch = curl_init();
    }

    /**
     * 判断资源类型是否需要初始化 CURL,防止第二次调用时候第一次的资源被释放
     *
     *
     * @DateTime 2018-12-29
     * @return void [type]       [description]
     * @throws Exception
     */
    private function initCurl()
    {
        if (!function_exists('curl_init')) {
            throw new Exception('不支持CURL功能.');
        }
        if (empty($this->ch)) {
            $this->ch = curl_init();
        }
    }

    /**
     * 初始化
     *
     * @access public
     *
     * @param array $options 参数
     *
     * @return Curl
     * @throws Exception
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
     *
     * @param array $header   设置的请求头
     * @param bool  $isAppend 是否追加
     *
     * @return $this
     * @throws Exception
     */
    public function setHeader(array $header, bool $isAppend = true)
    {
        $this->initCurl();

        $headData = $isAppend ? [
            "Content-type:application/x-www-form-urlencoded",
            "Content-type:application/json;charset='utf-8'",
        ] : [];

        foreach ($header as $key => $head) {
            if ($head) {
                $headData[] = is_integer($key) ? $head : ($key . ':' . $head);
            }
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headData);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        return $this;
    }

    /**
     * 设置http 超时
     *
     * @param int $time
     *
     * @return $this
     * @throws Exception
     */
    public function setTimeout($time = 3)
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
     *
     * @param string $proxy
     *
     * @return $this
     * @throws Exception
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
     *
     * @param int $port
     *
     * @return $this
     * @throws Exception
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
     *
     * @param string $referer
     *
     * @return $this
     * @throws Exception
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
     *
     * @param string $agent
     *
     * @return $this
     * @throws Exception
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

    /**
     * 复制句柄
     *
     * @return false|resource
     * @throws Exception
     */
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
     *
     * @param $show
     *
     * @return $this
     * @throws Exception
     */
    public function showResponseHeader($show)
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_HEADER, $show);
        return $this;
    }

    /**
     * 设置http请求的参数,get或post
     *
     * @param array $params
     *
     * @return $this
     * setParams( array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
     * setParams( {'a'=>'str_a'});
     * @throws Exception
     */
    public function setParams($params, $data_type = 'json')
    {
        $this->initCurl();
        //支持json数据数据提交
        if ($data_type == 'json') {
            $params = json_encode($params);
        } elseif ($data_type == 'array') {
            $params = obj2Arr($params);
        } elseif (is_array($params)) {
            $params = http_build_query($params, '', '&');
        }
        $this->httpParams = $params;
        return $this;
    }

    /**
     * 设置证书路径
     *
     * @param $file
     *
     * @throws Exception
     */
    public function setCaPath($file)
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_CAINFO, $file);
    }

    /**
     * 获取cURL版本数组
     *
     * @return array|false
     */
    public function version()
    {
        return curl_version();
    }

    //  闭包方式 注入 Curl
    //  ...->inject(function($http){
    //      curl_setopt($http->ch, CURLOPT_SSLCERTTYPE, 'PEM');
    //  });
    public function inject(\Closure $func)
    {
        $func($this);
        return $this;
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
     * @throws Exception
     */
    public function get($url, $data_type = 'json')
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        if (stripos($url, 'https://') !== false) {
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
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        curl_setopt($this->ch, CURLOPT_HEADER, false);

        return $this->run($data_type);
    }

    /**
     * 模拟POST请求
     *
     * @param string $url
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
     * @throws Exception
     */
    public function post(string $url, string $data_type = 'json')
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
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
        }
        return $this->run($data_type);
    }

    public function put($url, $data_type = 'json')
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT"); //设置请求方式
        // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data); //设置提交的字符串
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());
        // 设置post body
        if (!empty($this->httpParams)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
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
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
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
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
        }
        return $this->run($data_type);
    }

    /**
     * 上传文件
     *
     * @param string $url      上传地址
     * @param string $filePath 被上传文件绝对地址
     * @param array  $params   上传的附加请求数据 。例如上传视频时候设置 description 等参数
     *
     * @return array|mixed
     * @throws Exception
     */
    public function upload(string $url = '', string $filePath = '', array $params = [])
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception(sprintf('文件不存在或者不可读: "%s"', $filePath));
        }
        // return "@{$filename};filename={$postname};type={$mimetype}";

        if (class_exists('\CURLFile')) {
            $data = ['media' => new \CURLFile(realpath($filePath))];
        } else {
            $data = ['media' => '@' . realpath($filePath)];//<=5.5
        }

        $reqData = !empty($params) ? array_merge($data, $params) : $data;

        $this->setParams($reqData);

        return $this->post($url);
    }

    /**
     * 下载文件
     *
     * @param string $url      远程文件地址
     * @param string $filePath 存在在本地的地址
     *
     * @return array|mixed
     * @throws Exception
     */
    public function download($url = '', $filePath = '')
    {
        set_time_limit(0);
        $this->initCurl();
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $fp = fopen($filePath, 'w+');
        curl_setopt($this->ch, CURLOPT_FILE, $fp);
        $res = $this->run();
        fclose($fp);
        return $res;
    }

    // 判断远程资源是否存在
    public function exists($url = '')
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);          // 不取回数据
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        $found = false; // 如果请求没有发送失败
        if (curl_exec($curl) !== false) {
            if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {// 再检查http响应码是否为200
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    }

    protected function run($data_type = 'json')
    {
        $content = curl_exec($this->ch);
        $errInfo = curl_error($this->ch);
        $status  = curl_getinfo($this->ch);
        curl_close($this->ch);
        $this->ch = null;// 重置

        if (empty($content) && !empty($errInfo)) {
            $content = $errInfo;
        }
        if (substr($content, 0, 9) === 'callback(') {
            $result = [];
            preg_match_all("/(?:\{)(.*)(?:\})/i", $content, $result);
            $content = $result[0][0];
        } else {
            if (!$this->isJson($content)) {
                // 判断 等号出现次数
                $countEqStr = substr_count($content, '=', 0);
                if ($countEqStr > 0) {
                    parse_str($content, $content);
                }
            }
        }
        if (is_string($content) && $this->isJson($content) && $data_type == 'json') {
            $content = json_decode($content, true);
        }

        return $this->objectArray($content);
    }

    //PHP stdClass Object转array
    protected function objectArray($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->objectArray($value);
            }
        }
        return $array;

    }

    /**
     * 判断字符串是否为 Json 格式
     *
     * @param string $string Json 字符串
     *
     * @return array|bool|object 成功返回true，失败返回 false
     */
    protected function isJson(string $string = ''): bool
    {
        try {
            $data = json_decode($string, true);
            if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
                return true;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * 异步将远程链接上的内容(图片或内容)写到本地
     *
     * @param string $url      远程地址
     * @param string $saveFile 保存在服务器上的文件名(e.g. /root/a/b.jpg)
     *
     * @return bool 当返回为true时，代表成功，反之，为失败
     */
    public function putFileFromUrlContent(string $url, string $saveFile): bool
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
        $write = @fopen($saveFile, "w");
        if (!$write) {
            return false;
        }
        if (!fwrite($write, $file)) {
            return false;
        }
        if (!fclose($write)) {
            return false;
        }
        return true;
    }

    /**
     * 生成随机的userAgent
     *
     *
     * @DateTime 2018-12-28
     * @return string [type]       [description]
     */
    protected function randUserAgent(): string
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
            "傲游（Maxthon）"                => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",
            "腾讯TT"                       => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",
            "世界之窗（The World） 2.x"      => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
            "世界之窗（The World） 3.x"      => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",
            "360浏览器"                    => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",
            "搜狗浏览器 1.x"               => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",
            "Avant"                        => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",
            "Green Browser"                => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
            //移动端口
            "safari iOS 4.33 – iPhone"     => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "safari iOS 4.33 – iPod Touch" => "Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "safari iOS 4.33 – iPad"       => "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
            "Android N1"                   => "Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Android QQ浏览器 For android" => "MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
            "Android Opera Mobile"         => "Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10",
            "Android Pad Moto Xoom"        => "Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13",
            "BlackBerry"                   => "Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+",
            "WebOS HP Touchpad"            => "Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.0; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/233.70 Safari/534.6 TouchPad/1.0",
            "UC标准"                       => "NOKIA5700/ UCWEB7.0.2.37/28/999",
            "UCOpenwave"                   => "Openwave/ UCWEB7.0.2.37/28/999",
            "UC Opera"                     => "Mozilla/4.0 (compatible; MSIE 6.0; ) Opera/UCWEB7.0.2.37/28/999",
            "微信内置浏览器"               => "Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN",

        ];

        return $agentarry[array_rand($agentarry, 1)]; //随机浏览器useragent
    }
}
