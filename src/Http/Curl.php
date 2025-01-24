<?php
// +---------------------------------------------------------------------
// | Curl
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

namespace zxf\Http;

use CURLFile;
use Exception;
use zxf\Tools\Collection;

class Curl
{
    private $ch         = null; // curl 句柄
    private $httpParams = null;
    // cookie文件路径地址
    private string $cookieFile = '';
    private string $cookieJar  = '';

    // 发起请求后是否响应Curl对象; true:返回Curl对象,false:直接返回请求结果
    private bool $responseObject = false;

    // 响应内容
    private array $respObjData   = [];
    private array $defaultParams = [
        CURLOPT_RETURNTRANSFER => true, // 返回响应内容，而不是直接输出; 获取的信息以文件流的形式返回
        CURLOPT_HEADER         => true, // 返回头部信息
        CURLOPT_SSL_VERIFYPEER => false, // 对认证证书来源的检查
        CURLOPT_SSL_VERIFYHOST => false, // 从证书中检查SSL加密算法是否存在
        CURLOPT_SSLVERSION     => 1, // 选择合适的 SSL 版本
        CURLOPT_TIMEOUT        => 10, // 设置超时限制防止死循环
        CURLOPT_FOLLOWLOCATION => true, // 启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器
        CURLOPT_AUTOREFERER    => true, // 自动设置Referer
        CURLOPT_MAXREDIRS      => 5, // 最大跳转次数
        CURLOPT_CONNECTTIMEOUT => 10, // 设置连接等待时间
    ];

    // 默认请求头信息
    private array $defaultHeaders = [
        "Content-type:application/x-www-form-urlencoded",
        "Content-type:application/json;charset='utf-8'",
    ];

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * @param array $config          给curl进行默认配置
     *                               eg: [
     *                               CURLOPT_TIMEOUT => 10,
     *                               CURLOPT_RETURNTRANSFER=>true,
     *                               CURLOPT_HTTPHEADER => [
     *                               'Accept: application/json',
     *                               'Authorization: Bearer YOUR_API_TOKEN'
     *                               ],
     *                               CURLOPT_COOKIEJAR => 'cookie.txt',
     *                               CURLOPT_COOKIEFILE => 'cookie.txt',
     *                               ...
     *                               ]
     * @param bool  $responseObject  发起请求后是否响应对象; true:返回Curl对象,false:直接返回请求结果
     *                               true:返回Curl对象, 后面可以再次调用
     *                               ->getStatusCode()、->getBody()、->isSuccessful()、->getHeader()、->hasRedirect()、->getError()、->getResp()等方法
     *                               false:直接返回请求结果，直接返回html原始网页、json数组、Collection集合等
     *
     * @throws Exception
     */
    public function __construct(array $config = [], bool $responseObject = false)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('不支持CURL功能.');
        }
        $this->responseObject = $responseObject;
        $this->ch             = null;

        $this->defaultParams[CURLOPT_HTTPHEADER]  = $this->defaultHeaders;
        $this->defaultParams[CURLINFO_HEADER_OUT] = true;
        if ($config) {
            $this->defaultParams = array_merge($this->defaultParams, $config);
        }

        $this->initCurl();
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
        if (empty($this->ch)) {
            $this->ch = curl_init();
            curl_setopt_array($this->ch, $this->defaultParams);
        }
        // 使用cookie文件
        !empty($this->cookieJar) && curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        !empty($this->cookieFile) && curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        $this->respObjData = [];
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
    public static function instance(array $options = [], bool $responseObject = false)
    {
        if (!isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static($options, $responseObject);
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

        $headData = [];
        foreach ($header as $key => $head) {
            if ($head) {
                $headData[] = is_integer($key) ? $head : ($key . ':' . $head);
            }
        }
        $this->defaultHeaders = $isAppend ? array_merge($this->defaultHeaders, $headData) : $headData;

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->defaultHeaders);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        return $this;
    }

    /**
     * 设置cookie文件的保存路径地址或者读取地址
     *
     * @param string $file
     *
     * @return $this
     */
    public function setCookieFile(string $cookieFile = '', string $cookieJarFile = '')
    {
        $cookieFile && create_dir_or_filepath($cookieFile);
        $cookieJarFile && create_dir_or_filepath($cookieJarFile);
        $this->cookieFile = $cookieFile;
        $this->cookieJar  = $cookieJarFile;

        return $this;
    }

    /**
     * 设置cookie字符串
     *
     * @param string $cookieString
     *
     * @return $this
     */
    public function setCookieString(string $cookieString)
    {
        curl_setopt($this->ch, CURLOPT_COOKIE, $cookieString);
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
    public function setTimeout(int $time = 3)
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
    public function setProxy(string $proxy)
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
    public function setProxyPort(int $port)
    {
        $this->initCurl();
        curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
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
    public function setReferer(string $referer = "")
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
    public function setUserAgent(string $agent = "")
    {
        $this->initCurl();
        if ($agent) {
            // 模拟用户使用的浏览器
            curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);
        }
        return $this;
    }

    /**
     * 记录调试文件
     *
     * @param string $debugFile
     *
     * @return $this
     */
    public function debug(string $debugFile = '')
    {
        // 启用调试输出
        curl_setopt($this->ch, CURLOPT_VERBOSE, true);
        // 将调试信息写入文件
        $debugFile = fopen('curl_debug.log', 'w+');
        curl_setopt($this->ch, CURLOPT_STDERR, $debugFile);
        return $this;
    }

    /**
     * 设置是否返回对象
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function respObj(bool $flag = true)
    {
        $this->responseObject = $flag;
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
     * @param array  $params
     * @param string $data_type 数据类型 array|json|string
     *
     * @return $this
     * setParams( array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
     * setParams( {'a'=>'str_a'});
     * @throws Exception
     */
    public function setParams(array $params, string $data_type = 'array')
    {
        $this->initCurl();
        //支持json数据数据提交
        if ($data_type == 'json') {
            $params = json_encode($params);
        } elseif ($data_type == 'array') {
            $params = obj2Arr($params);
        } else {
            $params = http_build_query($params, '', '&');
        }
        $this->httpParams = $params;
        return $this;
    }

    /**
     * 设置证书路径
     *
     * @param string $file
     *
     * @throws Exception
     */
    public function setCaPath(string $file)
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
     * @param string $data_type 返回数据类型
     *
     * @return mixed
     *
     * Examples:
     * ```
     * Curl::get('http://api.example.com/?a=123&b=456', 'json');
     * ```
     * @throws Exception
     */
    public function get(string $url, string $data_type = 'json')
    {
        $this->initCurl();
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

        // 设置请求方式为 GET
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->randUserAgent());

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
     * Curl::post('http://api.example.com/', 'json');
     * ```
     * @throws Exception
     */
    public function post(string $url, string $data_type = 'json')
    {
        $this->initCurl();

        curl_setopt($this->ch, CURLOPT_URL, $url);
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
     * @param string $name     上传字段名称；默认 media，eg: file
     * @param array  $params   上传的附加请求数据 。例如上传视频时候设置 description 等参数
     *
     * @return array|mixed
     * @throws Exception
     */
    public function upload(string $url = '', string $filePath = '', string $name = '', array $params = [])
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception(sprintf('文件不存在或者不可读: "%s"', $filePath));
        }
        // return "@{$filename};filename={$postname};type={$mimetype}";

        // 上传字段名称；eg: file
        $name = !empty($name) ? $name : 'media';
        if (class_exists('\CURLFile')) {
            $data = [$name => new CURLFile(realpath($filePath))];
        } else {
            $data = [$name => '@' . realpath($filePath)];//<=5.5
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
    public function download(string $url = '', string $filePath = ''): mixed
    {
        set_time_limit(0);
        $this->initCurl();

        curl_setopt($this->ch, CURLOPT_URL, $url);
        $fp = fopen($filePath, 'w+');
        curl_setopt($this->ch, CURLOPT_FILE, $fp);
        $res = $this->run();
        fclose($fp);
        return $res;
    }

    /**
     * 断点续传下载文件
     *
     * @param string $url      远程文件地址
     * @param string $filePath 存在在本地的地址
     * @param int    $range    下载节点
     *
     * @return array|mixed
     * @throws Exception
     */
    public function downloadByRange(string $url = '', string $filePath = '', int $range = 0): mixed
    {
        set_time_limit(0);
        $this->initCurl();
        // curl_setopt($this->ch, CURLOPT_RANGE, $range.'1000-'); // 从字节 1000 开始继续下载
        curl_setopt($this->ch, CURLOPT_RANGE, $range . '-'); // 从字节 $range 开始继续下载
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $fp = fopen($filePath, 'w+');
        curl_setopt($this->ch, CURLOPT_FILE, $fp);
        $res = $this->run();
        fclose($fp);
        return $res;
    }

    // 判断远程资源是否存在
    public function exists(string $url = '')
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

    /**
     * 执行请求 [如果是网页内容会直接返回网页]
     *
     * @param string $data_type 返回数据类型 json|string(json字符串)|collection
     *
     * @return array|mixed
     */
    protected function run(string $data_type = 'json')
    {
        // 解析返回的数据
        $this->parserResponse();

        curl_close($this->ch);

        $this->ch = null;// 重置

        if ($this->responseObject) {
            // 返回curl对象，以便调用其他方法
            return $this;
        }

        $body = $this->respObjData['body'];

        if ($this->isError()) {
            return $this->getError();
        }

        // 响应内容为无法解析的文本，直接返回响应内容
        if (is_string($body)) {
            return $body;
        }

        if ($data_type == 'string') {
            return json_array_to_string($body);
        }
        // 集合
        if ($data_type == 'collection') {
            return new Collection($body);
        }
        // 默认返回json 数组
        return $body;
    }

    private function parserResponse()
    {
        $content = curl_exec($this->ch);

        $http_code           = curl_getinfo($this->ch, CURLINFO_HTTP_CODE); // 获取 HTTP 状态码
        $effective_url       = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL); // 最终请求的 URL（跟随重定向后的 URL）
        $content_type        = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE); // 响应的 Content-Type
        $total_time          = curl_getinfo($this->ch, CURLINFO_TOTAL_TIME);//总请求时间（秒）
        $name_lookup_time    = curl_getinfo($this->ch, CURLINFO_NAMELOOKUP_TIME);// DNS 解析时间（秒）
        $connect_time        = curl_getinfo($this->ch, CURLINFO_CONNECT_TIME);// 连接服务器所用时间（秒）
        $pre_transfer_time   = curl_getinfo($this->ch, CURLINFO_PRETRANSFER_TIME);// 从连接到数据传输前所用时间（秒）
        $start_transfer_time = curl_getinfo($this->ch, CURLINFO_STARTTRANSFER_TIME);// 从请求开始到第一个字节传输时间（秒）
        $redirect_count      = curl_getinfo($this->ch, CURLINFO_REDIRECT_COUNT); // 重定向次数
        $redirect_time       = curl_getinfo($this->ch, CURLINFO_REDIRECT_TIME); // 重定向消耗的总时间（秒）
        $download_size       = curl_getinfo($this->ch, CURLINFO_SIZE_DOWNLOAD); // 下载内容大小（字节）
        $size_upload         = curl_getinfo($this->ch, CURLINFO_SIZE_UPLOAD); // 上传内容大小（字节）
        $speed_download      = curl_getinfo($this->ch, CURLINFO_SPEED_DOWNLOAD); // 平均下载速度（字节/秒）
        $speed_upload        = curl_getinfo($this->ch, CURLINFO_SPEED_UPLOAD); // 平均上传速度（字节/秒）
        $header_size         = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE); // 响应头部大小（字节）
        $request_size        = curl_getinfo($this->ch, CURLINFO_REQUEST_SIZE); // 请求头部大小（字节）
        $ssl_verify_result   = curl_getinfo($this->ch, CURLINFO_SSL_VERIFYRESULT); // SSL 证书验证结果
        $primary_ip          = curl_getinfo($this->ch, CURLINFO_PRIMARY_IP); // 服务器ip
        $primary_port        = curl_getinfo($this->ch, CURLINFO_PRIMARY_PORT); // 服务器端口
        $local_ip            = curl_getinfo($this->ch, CURLINFO_LOCAL_IP); // 本地ip
        $local_port          = curl_getinfo($this->ch, CURLINFO_LOCAL_PORT); // 本地端口
        $http_version        = curl_getinfo($this->ch, CURLINFO_HTTP_VERSION); // HTTP 版本
        $request_header      = curl_getinfo($this->ch, CURLINFO_HEADER_OUT); // 请求头部
        $redirect_url        = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL); // 重定向 URL
        $error               = curl_errno($this->ch) ? [
            'no'   => curl_errno($this->ch),
            'info' => curl_error($this->ch),
        ] : []; // 获取请求是否发生错误

        // 提取响应头和主体内容
        $headerStr = substr($content, 0, $header_size);  // 响应头
        $body      = substr($content, $header_size);  // 响应主体

        // 提取响应头
        $headers = [];
        $lines   = explode("\r\n", $headerStr);
        foreach ($lines as $line) {
            if (str_contains($line, ': ')) {
                list($key, $value) = explode(': ', $line, 2);
                $headers[$key] = $value;
            }
        }
        $headers['content_type'] = $content_type; // 响应的 Content-Type
        $headers['header_size']  = $header_size; // 响应头部大小（字节）
        $headers['http_code']    = $http_code; // HTTP 响应状态码
        $headers['request_size'] = $request_size;// 请求头部大小（字节）

        // 服务器信息
        $servers = [
            'is_redirect'         => $redirect_count > 0, // 请求是否发生跳转（重定向）
            'total_time'          => $total_time, //总请求时间（秒）
            'primary_ip'          => $primary_ip, // 服务器ip
            'primary_port'        => $primary_port, // 服务器端口
            'local_ip'            => $local_ip, // 本地ip
            'local_port'          => $local_port, // 本地端口
            'http_version'        => $http_version, // HTTP 版本
            'request_header'      => $request_header, // 请求头部
            'redirect_url'        => $redirect_url, // 重定向 URL
            'ssl_verify_result'   => $ssl_verify_result, // SSL 证书验证结果
            'redirect_count'      => $redirect_count, // 重定向次数
            'redirect_time'       => $redirect_time, // 重定向消耗的总时间（秒）
            'download_size'       => $download_size, // 下载内容大小（字节）
            'size_upload'         => $size_upload, // 上传内容大小（字节）
            'speed_download'      => $speed_download, // 平均下载速度（字节/秒）
            'speed_upload'        => $speed_upload, // 平均上传速度（字节/秒）
            'effective_url'       => $effective_url, // 最终请求的 URL（跟随重定向后的 URL）
            'name_lookup_time'    => $name_lookup_time,// DNS 解析时间（秒）
            'connect_time'        => $connect_time,// 连接服务器所用时间（秒）
            'pre_transfer_time'   => $pre_transfer_time,// 从连接到数据传输前所用时间（秒）
            'start_transfer_time' => $start_transfer_time,// 从请求开始到第一个字节传输时间（秒）
        ];

        $this->respObjData = [
            'success'   => $http_code >= 200 && $http_code < 300, // 请求是否成功
            'http_code' => $http_code, // HTTP 响应状态码
            'servers'   => $servers, // 服务器信息
            'headers'   => $headers, // 响应头
            'body'      => $body,// 响应内容
            'error'     => $error, // 获取请求是否发生错误
        ];

        // JSONP 格式的json字符串处理, 如:callback({"key":"value"})
        if (str_starts_with($body, 'callback(')) {
            $result = [];
            preg_match_all("/(?:\{)(.*)(?:\})/i", $body, $result);
            $body = $result[0][0];
        }

        // 根据 `Content-Type` 解析内容
        if (stripos($content_type, 'text/xml') !== false || stripos($content_type, 'application/xml') !== false) {
            // 解析 XML
            $xmlObject = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
            // 将对象转换为 JSON，再转换为数组
            $body = json_decode(json_encode($xmlObject), true);
        } else {
            // 其他类型
            // json(application/json)、html(text/html)、文本(text/plain) 和其他不需要处理的格式
            if ($this->isJson($body)) {
                // json字符串处理
                $body = is_array($body) ? $body : json_decode($body, true);
                // $body = $this->objectToArray($body);
            }
        }
        $this->respObjData['body'] = $body;

        return $this;
    }

    /**
     * 请求是否成功（响应状态码是否为 2xx ）
     *
     * @return mixed
     */
    public function isSuccessful()
    {
        return $this->respObjData['success'];
    }

    /**
     * 判断请求是否发生请求错误
     *
     * @return bool
     */
    public function isError(): bool
    {
        return !empty($this->respObjData['error']);
    }

    /**
     * 返回响应状态码
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->respObjData['http_code'];
    }

    // 响应内容
    public function getBody()
    {
        return $this->respObjData['body'];
    }

    // 响应头
    public function getHeader(string $key = '')
    {
        if ($key) {
            return $this->respObjData['headers'][$key] ?? null;
        }
        return $this->respObjData['headers'];
    }

    /**
     * 获取 服务器信息
     *
     * @param string $key eg: primary_ip:服务器ip
     *
     * @return mixed|null
     */
    public function getServer(string $key = '')
    {
        if ($key) {
            return $this->respObjData['servers'][$key] ?? null;
        }
        return $this->respObjData['servers'];
    }

    // 是否重定向
    public function hasRedirect()
    {
        return $this->respObjData['servers']['is_redirect'];
    }

    // 所有响应错误内容
    public function getError()
    {
        return $this->respObjData['error'];
    }

    /**
     * 所有响应数据
     *
     * @return array
     */
    public function getResp(): array
    {
        return $this->respObjData;
    }

    //PHP stdClass Object转array
    protected function objToArr($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->objToArr($value);
            }
        }
        return $array;
    }

    /**
     * 判断字符串是否为 Json 格式
     *
     * @param string|array $string $string Json 字符串
     *
     * @return bool 成功返回true，失败返回 false
     */
    protected function isJson(string|array $string = ''): bool
    {
        try {
            if (is_array($string)) {
                return true;
            }
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
