<?php

namespace zxf\http;

/**
 * HTTP 请求类
 */
class Request
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 指示“overridden 重写”请求方法的键。通常用于支持REST API上的方法。例如PUT。
     *
     * @var string
     */
    const OVERRIDE = 'HTTP_X_HTTP_METHOD_OVERRIDE';

    /**
     * 受信任代理IP地址的数组。
     *
     * @var array
     */
    protected $proxies = [];

    /**
     * URI解析器数组。
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * 请求的所有输入数据。用作缓存。
     *
     * @var array
     */
    protected $input   = [];
    protected $headers = [];
    protected $servers = [];

    protected $post     = [];
    protected $get      = [];
    protected $files    = [];
    protected $request  = [];
    protected $sessions = [];
    protected $cookies  = [];
    protected $env      = [];

    // 请求类型
    protected $contentType = '';

    /**
     * 请求方法
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * 媒体类型格式的数组。
     *
     * @var array
     */
    protected $formats = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt'  => ['text/plain'],
        'js'   => [
            'application/javascript',
            'application/x-javascript', 'text/javascript',
        ],
        'css'  => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'xml'  => [
            'text/xml', 'application/xml',
            'application/x-xml',
        ],
        'rdf'  => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss'  => ['application/rss+xml'],
    ];

    public function __construct()
    {
        $this->init();
    }

    public static function instance($refresh = false)
    {
        if (is_null(self::$instance) || $refresh) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 获取参数前初始化请求信息
     *
     * @return $this
     */
    private function init()
    {
        if (!empty($this->input)) {
            return $this;
        }

        $this->get      = !empty($_GET) ? $_GET : [];
        $this->request  = !empty($_REQUEST) ? $_REQUEST : [];
        $this->files    = !empty($_FILES) ? $_FILES : [];
        $this->sessions = !empty($_SESSION) ? $_SESSION : [];
        $this->cookies  = !empty($_COOKIE) ? $_COOKIE : [];
        $this->env      = !empty($_ENV) ? $_ENV : [];
        $this->servers  = !empty($_SERVER) ? $_SERVER : [];

        $getContent    = file_get_contents('php://input');
        $getContentArr = [];
        if (!in_array(substr($getContent, 0, 5), ['<?xml', '<xml>'])) {
            mb_parse_str($getContent, $getContentArr);
            if ($this->servers['REQUEST_METHOD'] == "POST") {
                $this->post = $getContentArr;
            }
        } else {
            //解析xml
            //1、把整个文件读入一个字符串中：(用于接收xml文件)
            //2、转换形式良好的 XML 字符串为 SimpleXMLElement 对象，然后输出对象的键和元素：(用于处理接收到的xml数据，将其转换成对象)
            $xml_object = simplexml_load_string($getContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            //3、对象转成json
            $xml_json = json_encode($xml_object);
            //4、json再转成数组
            $getContentArr = json_decode($xml_json, true);

            $this->post = $getContentArr;
        }
        $this->input = !empty($getContentArr) ? array_merge($this->input, $getContentArr) : $this->input;

        $post       = !empty($_POST) ? $_POST : (!empty($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : []);
        $this->post = !empty($post) ? (!empty($this->post) ? array_merge($this->post, $post) : $post) : $this->post;

        if (!function_exists('apache_request_headers')) {
            $headersData = array();
            $headers     = headers_list();
            foreach ($headers as $header) {
                $header                            = explode(":", $header);
                $headersData[array_shift($header)] = trim(implode(":", $header));
            }
            $this->headers = array_change_key_case($headersData, CASE_UPPER);
        } else {
            $this->headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
        }

        // 变量属性初始化结束后再调用方法
        $this->method      = $this->method();
        $this->contentType = $this->getContentType();

        return $this;
    }

    /**
     * 获取请求类型
     */
    private function getContentType()
    {
        if (isset($this->servers['CONTENT_TYPE'])) {
            return $this->servers['CONTENT_TYPE'];
        }
        if (isset($this->servers['HTTP_CONTENT_TYPE'])) {
            return $this->servers['HTTP_CONTENT_TYPE'];
        }
        return null;
    }

    /**
     * 获取超级全局数组中项的值。
     *
     * @param array  $array   超全局数组
     * @param string $key     数组的键
     * @param string $default 默认值
     *
     * @return mixed
     */
    private function lookup($array, $key, $default)
    {
        if ($key === null) {
            return $array;
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * 返回包含所有输入数据的单个数组 (i.e. GET, POST, PUT and DELETE).
     *
     * @return array
     */
    public function body()
    {
        return (array)$this->input + (array)$this->request + (array)$this->files;
    }

    /**
     * 检查请求方法是否已被重写。
     *
     * @return bool
     */
    protected function overridden()
    {
        return isset($this->post[Request::OVERRIDE]) || isset($this->servers[Request::OVERRIDE]);
    }

    /**
     * 获取get数组中项目的值。
     *
     * <code>
     *   // 从$_Get数组中获取用户名变量。
     *   $username = Request::get('username');
     *
     *   // 如果请求的项未定义，则返回默认值。
     *   $username = Request::get('username', 'Fred Nurk');
     *
     *   // 返回$_GET数组中的所有输入数据。
     *   $input = Request::get();
     * </code>
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string        如果键未定义，则为NULL。
     */
    public function get($key = null, $default = null)
    {
        return $this->lookup($this->get, $key, $default);
    }

    /**
     * 获取$_POST数组中项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string          如果键未定义，则为NULL。
     */
    public function post($key = null, $default = null)
    {
        return $this->lookup($this->post, $key, $default);
    }

    public function request($key = null, $default = null)
    {
        return $this->lookup($this->request, $key, $default);
    }

    /**
     * 追加post数据
     *
     * @param string|array $keys  需要批量添加时传入二维数组，单个添加时候传入字符串
     * @param mixed        $value 被追加的值，$keys为字符串时候生效
     *
     * @return $this
     */
    public function addPost($keys = null, $value = null)
    {
        if (is_array($keys) && count($keys) != count($keys, 1)) {
            // 是数组却为二维数组
            $this->post = $keys + $this->post;
        } else {
            $this->post[$keys] = $value;
        }
        return $this;
    }

    /**
     * 追加get数据
     *
     * @param string|array $keys  需要批量添加时传入二维数组，单个添加时候传入字符串
     * @param mixed        $value 被追加的值，$keys为字符串时候生效
     *
     * @return $this
     */
    public function addGet($keys = null, $value = null)
    {
        if (is_array($keys) && count($keys) != count($keys, 1)) {
            // 是数组却为二维数组
            $this->get = $keys + $this->get;
        } else {
            $this->get[$keys] = $value;
        }
        return $this;
    }

    /**
     * 获取通过PUT或DELETE方法提交的项的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string 如果键未定义，则为NULL。
     */
    protected function stream($key, $default)
    {
        if ($this->overridden()) {
            return $this->lookup($this->post, $key, $default);
        }

        return $this->lookup($this->input, $key, $default);
    }

    /**
     * 获取通过PUT提交的项的值
     * method (either spoofed or via REST).
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function put($key = null, $default = null)
    {
        return $this->method() === 'PUT' ? $this->stream($key, $default) : $default;
    }

    /**
     * 获取通过DELETE提交的项目的值
     * method (either spoofed or via REST).
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function delete($key = null, $default = null)
    {
        return $this->method() === 'DELETE' ? $this->stream($key, $default) : $default;
    }

    /**
     * 获取$_FILES数组中项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function files($key = null, $default = null)
    {
        return $this->lookup($this->files, $key, $default);
    }

    /**
     * 获取$_SESSION数组中项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function session($key = null, $default = null)
    {
        return $this->lookup($this->sessions, $key, $default);
    }

    /**
     * 获取$_COOKIE数组中项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function cookie($key = null, $default = null)
    {
        return $this->lookup($this->cookies, $key, $default);
    }

    /**
     * 获取$_ENV数组中项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function env($key = null, $default = null)
    {
        return $this->lookup($this->env, $key, $default);
    }

    /**
     * 获取$_SERVER数组中项的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function server($key = null, $default = null)
    {
        return $this->lookup($this->servers, $key, $default);
    }

    /**
     * 获取请求头
     *
     * @param $key
     * @param $default
     *
     * @return array|mixed|string
     */
    public function headers($key = null, $default = null)
    {
        return $this->lookup($this->headers, $key, $default);
    }

    /**
     * 从通过Get、POST、PUT或DELETE提交的输入数据中获取项目的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function input($key = null, $default = null)
    {
        return $this->lookup((array)$this->input + (array)$this->request + (array)$this->post + (array)$this->get, $key, $default);
    }

    /**
     * 从通过Get、POST、PUT、DELETE或FILES提交的输入数据中获取项的值。
     *
     * @param string $key     数组的键值
     * @param string $default 默认值
     *
     * @return string
     */
    public function all($key = null, $default = null)
    {
        return $this->lookup((array)$this->input + (array)$this->request + (array)$this->post + (array)$this->get + (array)$this->files, $key, $default);
    }

    /**
     * 从输入数据中获取项目的子集。
     *
     * <code>
     *   // 仅从输入数据中获取电子邮件变量。
     *   $email = Request::only('email');
     *
     *   // 仅从输入数据中获取用户名和电子邮件。
     *   $input = Request::only(array('username', 'email'));
     * </code>
     *
     * @param array $keys The keys to select from the input.
     *
     * @return array
     */
    public function only($keys)
    {
        return array_intersect_key(
            $this->input(), array_flip((array)$keys)
        );
    }

    /**
     * 获取除指定项或项数组之外的所有输入数据。
     *
     * <code>
     *   // 获取除用户名之外的所有输入数据。
     *   $input = Request::except('username');
     *
     *   // 获取除用户名和电子邮件之外的所有输入数据。
     *   $input = Request::except(array('username', 'email'));
     * </code>
     *
     * @param array $keys 要从输入中忽略的键。
     *
     * @return array
     */
    public function except($keys)
    {
        return array_diff_key(
            $this->input(), array_flip((array)$keys)
        );
    }

    /**
     * 检查输入数据是否包含项或所有指定的项数组。
     *
     * 如果任何输入项为空字符串，将返回FALSE。
     *
     * <code>
     *   // 请求信息中是否存在id
     *   if (Request::has('id')) { echo 'The `id` exists.'; }
     *
     *   // 请求信息中是否id 和 name 都存在？
     *   if (Request::has(array('id', 'name'))) { // do stuff }
     * </code>
     *
     * @param mixed $keys 输入数据键或键数组。
     *
     * @return bool
     */
    public function has($keys)
    {
        foreach ((array)$keys as $key) {
            if (trim($this->input($key)) == '') {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取请求的协议。例如HTTP/1.1
     *
     * 默认为HTTP/1.1。
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function protocol($default = 'HTTP/1.1')
    {
        return $this->server('SERVER_PROTOCOL', $default);
    }

    /**
     * 获取请求 scheme 。即http或https。
     *
     * 如果使用TRUE调用该方法，则将返回带有 :// 前缀的scheme
     *
     * @param bool $decorated 是否添加 :// 前缀.
     *
     * @return string
     */
    public function scheme($decorated = false)
    {
        $scheme = $this->secure() ? 'https' : 'http';
        return $decorated ? "$scheme://" : $scheme;
    }

    /**
     * 检查请求是否通过HTTPS进行。
     *
     * @return bool
     */
    public function secure()
    {
        if (in_array($this->server('HTTPS'), ['ON', 'on'])) {
            return true;
        }

        if (!$this->entrusted()) {
            return false;
        }

        return (in_array($this->server('SSL_HTTPS'), ['ON', 'on']) || in_array($this->server('X_FORWARDED_PROTO'), ['HTTPS', 'https']));
    }

    /**
     * 获取请求方法。例如GET、POST。
     *
     * 可以重写此方法以支持非浏览器请求方法。例如PUT、DELETE。
     *
     * @return string
     */
    public function method()
    {
        $method = $this->overridden() ? (isset($this->post[Request::OVERRIDE]) ? $this->post[Request::OVERRIDE] : $this->servers[Request::OVERRIDE]) : $this->servers['REQUEST_METHOD'];
        return strtoupper($method);
    }

    /**
     * 检查请求方法是否安全。即GET或HEAD。
     *
     * @return bool
     */
    public function safe()
    {
        return in_array($this->method(), ['GET', 'HEAD']);
    }

    /**
     * 获取当前请求的时间
     *
     * @access public
     *
     * @param string $format 返回时间格式 默认 'Y-m-d H:i:s'
     *
     * @return integer|float
     */
    public function time($format = '')
    {
        $format = (!empty($format) && is_string($format)) ? $format : 'Y-m-d H:i:s';
        return date($format, $this->server('REQUEST_TIME'));
    }

    /**
     * 检查请求是否为AJAX请求。
     *
     * @return bool
     */
    public function isAjax()
    {
        return isset($this->servers['HTTP_X_REQUESTED_WITH']) ? strtoupper($this->servers['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST' : false;
    }

    /**
     * 当前是否Pjax请求
     *
     * @access public
     *
     * @param bool $pjax true 获取原始pjax请求
     *
     * @return bool
     */
    public function isPjax(bool $pjax = false): bool
    {
        return !empty($val = $this->server('HTTP_X_PJAX')) ? ($pjax ? $val : true) : false;
    }

    public function isPost()
    {
        return $this->method() == 'POST';
    }

    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 获取网页是从哪个页面链接过来的
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function referrer($default = null)
    {
        return $this->server('HTTP_REFERRER', $default);
    }

    /**
     * 重写默认URI解析器列表。
     *
     * 解析器数组的元素是$_SERVER数组中的键，具有可选的“modifier”函数来调整返回值。
     *
     * @param array $resolvers URI解析器的优先级排序列表。
     *
     * @return array
     */
    public function resolvers($resolvers = [])
    {
        if ($resolvers || empty($this->resolvers)) {
            $this->resolvers = $resolvers + [
                    'PATH_INFO',
                    'REQUEST_URI' => function ($uri) {
                        return parse_url($uri, PHP_URL_PATH);
                    },
                    'PHP_SELF',
                    'REDIRECT_URL',
                ];
        }

        return $this->resolvers;
    }

    /**
     * 获取请求的URL. e.g. http://a.com/bar?q=foo
     *
     * @return string
     */
    public function url()
    {
        return $this->scheme(true) . $this->host() . $this->port(true) . $this->uri() . $this->query(true);
    }

    /**
     * 获取请求的URI. e.g. /blog/item/10
     *
     * 排除查询字符串。
     *
     * @return string
     */
    public function uri()
    {
        return parse_url($this->server('REQUEST_URI'), PHP_URL_PATH);
    }

    /**
     * 获取请求查询字符串. e.g. q=search&foo=bar
     *
     * 默认情况下，问号被排除在外。要包含问号，请使用TRUE调用该方法。
     *
     * @param bool $decorated 添加 ? 前缀.
     *
     * @return string
     */
    public function query($decorated = false)
    {
        if (count($this->get)) {
            $query = http_build_query($this->get);
            return $decorated ? "?$query" : $query;
        }
    }

    /**
     * 获取请求的URI段。
     *
     * @param array $default 默认值
     *
     * @return array
     */
    public function segments($default = [])
    {
        return explode('/', trim($this->uri() ?: $default, '/'));
    }

    /**
     * 获取请求的特定URI段。
     *
     * 使用负索引以相反顺序检索段。
     *
     * @param int    $index   A one-based segment index. 基于一的段索引。
     * @param string $default 默认值
     *
     * @return string
     */
    public function segment($index, $default = null)
    {
        $segments = $this->segments();

        if ($index < 0) {
            $index    *= -1;
            $segments = array_reverse($segments);
        }

        return $this->lookup($segments, $index - 1, $default);
    }

    /**
     * 从HTTP接受标头中获取有序的值数组。
     *
     * @param string $terms HTTP接受标头。
     * @param string $regex 用于解析标头的正则表达式。
     *
     * @return array
     */
    protected function parse($terms, $regex)
    {
        $result = array();

        foreach (array_reverse(explode(',', $terms)) as $part) {
            if (preg_match("/{$regex}/", $part, $m)) {
                $quality            = isset($m['quality']) ? $m['quality'] : 1;
                $result[$m['term']] = $quality;
            }
        }

        arsort($result);
        return array_keys($result);
    }

    /**
     * 将格式与媒体类型相关联。
     *
     * @param string $format 格式。
     * @param string $type   媒体类型。
     *
     * @return $this
     */
    public function format($format, $types)
    {
        $this->formats[$format] = is_array($types) ? $types : array($types);
        return $this;
    }

    /**
     * 获取客户端首选的语言。
     *
     * 默认值 'en'.
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function language($default = null)
    {
        return $this->lookup($this->languages(), 0, $default);
    }

    /**
     * 获取客户端首选语言的有序数组。
     *
     * @return array
     */
    public function languages()
    {
        return $this->parse(
            $this->server('HTTP_ACCEPT_LANGUAGE', 'en'),
            '(?P<term>[\w\-]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?'
        );
    }

    /**
     * 格式化媒体类型。
     *
     * @param string $type   媒体类型。
     * @param bool   $strict 返回原始 media type.
     *
     * @return string
     */
    protected function media($type, $strict = false)
    {
        if ($strict) {
            return $type;
        }

        $type = preg_split('/\s*;\s*/', $type)[0];
        foreach ($this->formats as $format => $types) {
            if (in_array($type, (array)$types)) {
                return $format;
            }
        }

        return $type;
    }

    /**
     * 获取请求主体的媒体类型。
     *
     * 默认值 'application/x-www-form-urlencoded'.
     *
     * @param string $default 默认值
     * @param bool   $strict  返回原始 media type.
     *
     * @return string
     */
    public function type($default = null, $strict = false)
    {
        $type = $this->server('HTTP_CONTENT_TYPE', $default ?: 'application/x-www-form-urlencoded');
        return $this->media($type, $strict);
    }

    /**
     * 获取客户端首选的媒体类型。
     *
     * 默认 'html'.
     *
     * @param string $default 默认值
     * @param bool   $strict  返回原始 media type.
     *
     * @return string
     */
    public function accept($default = null, $strict = false)
    {
        $type = $this->lookup($this->accepts(), 0, $default);
        return $this->media($type, $strict);
    }

    /**
     * 获取客户端首选的媒体类型的有序数组。
     *
     * @return array
     */
    public function accepts()
    {
        return $this->parse(
            $this->server('HTTP_ACCEPT', 'text/html'),
            '(?P<term>[\w\-\+\/\*]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?'
        );
    }

    /**
     * 获取客户端首选的媒体类型。
     *
     * 默认 'utf-8'.
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function charset($default = null)
    {
        return $this->lookup($this->charsets(), 0, $default);
    }

    /**
     * 获取客户端首选的字符集的有序数组。
     *
     * @return array
     */
    public function charsets()
    {
        return $this->parse(
            $this->server('HTTP_ACCEPT_CHARSET', 'utf-8'), '(?P<term>[\w\-\*]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?'
        );
    }

    /**
     * 获取用户代理. e.g. Mozilla/5.0 (Macintosh; ...)
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function userAgent($default = null)
    {
        return $this->server('HTTP_USER_AGENT', $default);
    }

    /**
     * 设置一个或多个受信任的代理服务器。
     *
     * 默认情况下，所有代理服务器都是受信任的。当请求客户端IP地址时，使用此方法只信任有限的一组代理服务器。
     *
     * 此方法不是累积的。
     *
     * @param mixed $proxies 受信任代理的IP地址或受信任代理阵列。
     *
     * @return $this
     */
    public function setProxies($proxies)
    {
        $this->proxies = (array)$proxies;
        return $this;
    }

    /**
     * 检查是否所有代理服务器都是受信任的，或者此请求是否是通过受信任的代理服务器发送的。
     *
     * @return bool
     */
    public function entrusted()
    {
        return (empty($this->proxies) || isset($this->servers['REMOTE_ADDR']) && in_array($this->servers['REMOTE_ADDR'], $this->proxies));
    }

    /**
     * 解析web服务器的名称。
     *
     * 解析顺序是请求的“host”标头，然后是“server name”指令，然后是服务器IP地址。
     * 端口号（如果存在）将被剥离。
     *
     * @param string $default 默认值
     *
     * @return string
     */
    public function host($default = null)
    {
        $keys = ['HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];

        if (
            $this->entrusted() &&
            $host = $this->server('X_FORWARDED_HOST')
        ) {
            $host = explode(',', $host);
            $host = trim($host[count($host) - 1]);
        } else {
            foreach ($keys as $key) {
                if (isset($this->servers[$key])) {
                    $host = $this->servers[$key];
                    break;
                }
            }
        }

        return isset($host) ? preg_replace('/:\d+$/', '', $host) : $default;
    }

    /**
     * 获取当前包含协议的域名
     *
     * @access public
     * @return string
     */
    public function domain(): string
    {
        return $this->scheme() . '://' . $this->host();
    }

    /**
     * 获取客户端IP地址。
     *
     * 默认情况下，HTTP_CLIENT_IP受信任。如果不信任此标头，请使用FALSE调用该方法。
     * 如果HTTP_CLIENT_IP无效或被排除，将返回通过可信代理服务器获得的有效IP地址或REMOTE_ADDR。
     * 忽略无效、专用和保留的IP地址。
     *
     * 如果无法获得有效的IP地址。 返回 0.0.0.0
     *
     * @param bool $trusted 信任客户端通过HTTP_client_IP设置的IP地址。
     *
     * @return string
     */
    public function ip($trusted = true)
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',];

        $ips = array();

        if ($trusted && isset($this->servers['HTTP_CLIENT_IP'])) {
            $ips[] = $this->servers['HTTP_CLIENT_IP'];
        }

        foreach ($keys as $key) {
            if (isset($this->servers[$key])) {
                if ($this->entrusted()) {
                    $parts = explode(',', $this->servers[$key]);
                    $ips[] = trim($parts[count($parts) - 1]);
                }
            }
        }

        foreach ($ips as $ip) {
            if (
                filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 || FILTER_FLAG_IPV6 ||
                    FILTER_FLAG_NO_PRIV_RANGE || FILTER_FLAG_NO_RES_RANGE)
            ) {
                return $ip;
            }
        }

        return $this->server('REMOTE_ADDR', '0.0.0.0');
    }

    /**
     * 检测是否使用手机访问
     *
     * @access public
     * @return bool
     */
    public function isMobile(): bool
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }

    /**
     * 获取请求的端口号. e.g. 80
     *
     * 如果使用TRUE调用该方法，则端口号（如果为80或443）将被省略，否则将以冒号作为前缀。
     * 如果未定义SERVER_port，则默认为端口80。
     *
     * @param bool $decorated 前缀为：
     *
     * @return string
     */
    public function port($decorated = false)
    {
        $port = $this->entrusted() ? $this->server('X_FORWARDED_PORT') : null;

        $port = $port ?: $this->server('SERVER_PORT');

        return $decorated ? (in_array($port, [80, 443]) ? '' : ":$port") : $port;
    }
}
