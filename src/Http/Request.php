<?php

namespace zxf\Http;

/**
 * HTTP 请求类
 */
class Request
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    private ?string $contentType = '';

    private mixed $overridden = [];

    private mixed $get = [];

    private mixed $post = [];

    private mixed $input = [];

    /**
     * 接收到的原始数据
     *
     * @var mixed|array
     */
    private static mixed $rawInput = null;

    private mixed $files = [];

    private mixed $sessions = [];

    private mixed $cookies = [];

    private mixed $env = [];

    private mixed $servers = [];

    private mixed $headers = [];

    private mixed $protocol;

    private mixed $method;

    private mixed $query;

    private mixed $userAgent;

    private mixed $port;

    public function __construct()
    {
        $this->init();
    }

    public static function instance($refresh = false)
    {
        if (! isset(self::$instance) || is_null(self::$instance) || empty(self::$instance) || $refresh) {
            self::$instance = new static;
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
        // 一、基础的、逻辑较简单的放在最前面优先获取
        // 获取请求方法（GET、POST等）
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // 获取请求的内容类型
        $this->contentType = strtolower(! empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (! empty($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : 'text/html'));
        // 获取GET参数
        $this->get = ! empty($_GET) ? $_GET : [];
        // 获取POST参数
        $this->post = ! empty($_POST) ? $_POST : (! empty($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : []);
        // 获取查询字符串参数（URL中"?"后的部分）
        $this->query = $_SERVER['QUERY_STRING'] ?? '';
        // 获取用户代理（User-Agent）信息
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        // 获取文件参数
        $this->files = $_FILES ?? [];

        // 二、获取其他内容
        // 获取重写的参数
        $this->overridden = $_SERVER['REDIRECT_STATUS'] ?? [];
        // 获取输入参数（可能是GET或POST 等）
        $this->input = $this->getRawInput(false) ?? [];

        // 获取Session参数
        $this->sessions = $_SESSION ?? [];
        // 获取Cookie参数
        $this->cookies = $_COOKIE ?? [];
        // 获取环境变量参数
        $this->env = $_ENV ?? [];
        // 获取服务器参数
        $this->servers = $_SERVER ?? [];
        // 获取协议类型（HTTP或HTTPS）
        $this->protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : ($this->servers['SERVER_PROTOCOL'] ?? 'http');

        // 获取请求的端口号信息（如果是HTTP默认端口80，HTTPS默认端口443）
        $this->port = $this->port();

        // 获取请求头信息
        if (! function_exists('apache_request_headers')) {
            $headersData = [];
            $headers = headers_list();
            foreach ($headers as $header) {
                $header = explode(':', $header);
                $headersData[array_shift($header)] = trim(implode(':', $header));
            }
            $this->headers = array_change_key_case($headersData, CASE_UPPER);
        } else {
            $this->headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
        }

        return $this;
    }

    /**
     * 检查一个字符串是否为 key1=value1&key2=value2 格式的字符串
     */
    private function isUrlEncodedString(mixed $str): bool
    {
        if (empty($str) || ! is_string($str)) {
            return false;
        }
        parse_str($str, $result);

        // 如果解析后数组不为空且原始字符串包含=号，则可能是正确格式
        return ! empty($result) && str_contains($str, '=');
    }

    /**
     * 获取请求内容
     *
     * @param  bool  $returnOriginal  是否返回原始数据；默认为 true；
     *                                true:返回原始数据
     *                                false:返回解析后的数据；
     * @param  bool  $getDataType  是否获取数据类型；默认为 false；
     *                             true:返回数据类型；
     *                             false:只返回请求数据；
     */
    public function getRawInput(bool $returnOriginal = true, bool $getDataType = false)
    {
        $rawInput = $this->getRequestRawContent();

        // 如果是原始数据，直接返回
        if ($returnOriginal) {
            return $rawInput;
        }
        if (! empty($this->input)) {
            return $this->input;
        }

        // 如果未获取到原始数据，返回 null
        if (empty($rawInput) && empty($this->get) && empty($this->post) && empty($this->files)) {
            return [];
        }

        $data = [];
        // 默认返回的解析结果
        $type = 'raw';

        if ($this->isUrlEncodedString($rawInput)) {
            // 判断 $rawInput 是否为
            parse_str($rawInput, $form);
            $data = is_array($form) ? $form : [];
        }

        // 判断数据类型并进行解析
        if (is_json($rawInput)) {
            $type = 'json';
            $data = is_array($rawInput) ? $rawInput : json_decode($rawInput, true);
        } elseif (is_xml($rawInput)) {
            $type = 'xml';
            $data = \zxf\Xml\XML2Array::run($rawInput);
        }

        // 处理 get 数据
        if (! empty($this->get) && ! empty($data)) {
            $data = array_merge($data, $this->get);
        }

        // 处理 POST 数据， multipart/form-data 数据不包含在 $rawInput 中，需要单独处理
        if ((! empty($this->post) || ! empty($this->files)) && ! empty($data)) {
            $data = ! empty($this->post) ? array_merge($data, $this->post) : array_merge($data, $this->files);
        }

        // 如果需要返回数据类型
        if ($getDataType) {
            return [
                'data' => $data,
                'type' => $type,
            ];
        }

        return (array) $data;
    }

    /**
     * 获取PHP原始请求内容
     *      提示：如果是混合形式的请求(例如是 $_GET 请求 但是又包含了xml 等请求体)的只返回请求体数据，不返回$_GET 数据
     */
    private function getRequestRawContent(): mixed
    {
        if (! empty(self::$rawInput)) {
            if (\is_resource(self::$rawInput)) {
                rewind(self::$rawInput);
                $rawInput = stream_get_contents(self::$rawInput);
            } else {
                $rawInput = self::$rawInput;
            }

            return $rawInput;
        }

        // 检测常见PHP框架并获取rawInput
        $rawInput = match (true) {
            // Laravel/Lumen
            class_exists(\Illuminate\Http\Request::class) => (
                // Laravel 封装方式尝试（优先使用一次）
                app()->bound('request')
                    ? app('request')->getContent()
                    : \Illuminate\Http\Request::capture()->getContent()
            ),
            // Symfony
            class_exists(\Symfony\Component\HttpFoundation\Request::class) => \Symfony\Component\HttpFoundation\Request::createFromGlobals()->getContent(),
            // CodeIgniter 4
            class_exists(\CodeIgniter\HTTP\IncomingRequest::class) => \CodeIgniter\Config\Services::request()->getBody(),
            // Slim 3/4
            class_exists(\Slim\Psr7\Request::class) => (string) \Slim\Factory\AppFactory::determineRequestMethod()->getBody(),
            // Yii2
            class_exists(\yii\web\Request::class) => \Yii::$app->request->getRawBody(),
            // ThinkPHP 6+
            class_exists(\think\Request::class) => \think\facade\Request::instance()->getContent(),
            // CakePHP
            class_exists(\Cake\Http\ServerRequest::class) => (string) \Cake\Http\ServerRequestFactory::fromGlobals()->getBody(),
            // Phalcon
            class_exists(\Phalcon\Http\Request::class) => \Phalcon\Di\FactoryDefault::getDefault()->get('request')->getRawBody(),
            // Zend Framework / Laminas
            class_exists(\Laminas\Diactoros\ServerRequestFactory::class) => (string) \Laminas\Diactoros\ServerRequestFactory::fromGlobals()->getBody(),
            // FuelPHP
            class_exists(\Fuel\Core\Request::class) => \Fuel\Core\Request::active()->getBody(),
            // FlightPHP
            class_exists(\flight\Engine::class) => \flight\core\Dispatcher::getInstance()->request()->getBody(),
            // 默认处理
            default => $GLOBALS['HTTP_RAW_POST_DATA'] ?? null
        };
        if (empty($rawInput)) {
            // 尝试获取原始数据 优先使用
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                if (! empty($this->post)) {
                    $rawInput = http_build_query($this->post);
                } else {
                    // 最后尝试通过输入流
                    $input = fopen('php://input', 'r');
                    $rawInput = stream_get_contents($input);
                    fclose($input);
                    $rawInput = ($rawInput !== false) ? $rawInput : null;
                }
            }
        }
        if (empty($rawInput) && ! empty($this->get)) {
            $rawInput = http_build_query($this->get);
        }

        self::$rawInput = $rawInput;

        return $rawInput;
    }

    /**
     * 获取请求类型
     */
    public function getContentType(): ?string
    {
        return $this->contentType ?? null;
    }

    /**
     * 返回包含所有输入数据的单个数组
     *
     * @return array
     */
    public function body()
    {
        return $this->input();
    }

    /**
     * 检查请求方法是否已被重写。
     *
     * @return bool
     */
    public function overridden()
    {
        return ! empty($this->overridden);
    }

    private function parseData(array $data, ?string $key = null, mixed $default = null)
    {
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
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
     * @param  string|null  $key  数组的键值
     * @param  mixed  $default  默认值
     * @return mixed 如果键未定义，则为NULL。
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        return $this->parseData($this->get, $key, $default);
    }

    /**
     * 获取$_POST数组中项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  mixed  $default  默认值
     * @return mixed 如果键未定义，则为NULL。
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->parseData($this->post, $key, $default);
    }

    /**
     * 追加post数据
     *
     * @param  string|array  $keys  需要批量添加时传入二维数组，单个添加时候传入字符串
     * @param  mixed  $value  被追加的值，$keys为字符串时候生效
     * @return $this
     */
    public function addPost(string|array $keys = '', mixed $value = null): static
    {
        if (empty($keys)) {
            return $this;
        }
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
     * @param  array|string  $keys  需要批量添加时传入二维数组，单个添加时候传入字符串
     * @param  mixed|null  $value  被追加的值，$keys为字符串时候生效
     * @return $this
     */
    public function addGet(array|string $keys = '', mixed $value = null): static
    {
        if (empty($keys)) {
            return $this;
        }
        if (is_array($keys) && count($keys) != count($keys, 1)) {
            // 是数组却为二维数组
            $this->get = $keys + $this->get;
        } else {
            $this->get[$keys] = $value;
        }

        return $this;
    }

    /**
     * 获取通过PUT提交的项的值
     * method (either spoofed or via REST).
     *
     * @param  string|null  $key  数组的键值
     * @param  mixed  $default  默认值
     * @return string
     */
    public function put(?string $key = null, mixed $default = null)
    {
        return $this->method() === 'PUT' ? $this->post($key, $default) : $default;
    }

    /**
     * 获取通过DELETE提交的项目的值
     * method (either spoofed or via REST).
     *
     * @param  string|null  $key  数组的键值
     * @param  mixed  $default  默认值
     * @return string
     */
    public function delete(?string $key = null, mixed $default = null)
    {
        return $this->method() === 'DELETE' ? $this->post($key, $default) : $default;
    }

    /**
     * 获取$_FILES数组中项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function files(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->files, $key, $default);
    }

    /**
     * 获取$_SESSION数组中项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function session(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->sessions, $key, $default);
    }

    /**
     * 获取$_COOKIE数组中项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function cookie(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->cookies, $key, $default);
    }

    /**
     * 获取$_ENV数组中项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function env(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->env, $key, $default);
    }

    /**
     * 获取$_SERVER数组中项的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function server(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->servers, $key, $default);
    }

    /**
     * 获取请求头
     *
     *
     * @return array|mixed|string
     */
    public function headers(?string $key = null, mixed $default = null)
    {
        return $this->parseData($this->headers, $key, $default);
    }

    /**
     * 从通过Get、POST、PUT或DELETE提交的输入数据中获取项目的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  mixed  $default  默认值
     * @return string
     */
    public function input(?string $key = null, mixed $default = null)
    {
        return $this->parseData((array) $this->input, $key, $default);
    }

    /**
     * 从通过Get、POST、PUT、DELETE或FILES提交的输入数据中获取项的值。
     *
     * @param  string|null  $key  数组的键值
     * @param  string  $default  默认值
     * @return string
     */
    public function all(?string $key = null, mixed $default = null)
    {
        return $this->parseData((array) $this->input + (array) $this->post + (array) $this->get + (array) $this->files, $key, $default);
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
     * @param  array|string  $keys  The keys to select from the input.
     * @return array
     */
    public function only(array|string $keys)
    {
        return array_intersect_key(
            $this->input(), array_flip((array) $keys)
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
     * @param  array|string  $keys  要从输入中忽略的键。
     * @return array
     */
    public function except(array|string $keys)
    {
        return array_diff_key(
            $this->input(), array_flip((array) $keys)
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
     * @param  array|string  $keys  输入数据键或键数组。
     * @return bool
     */
    public function has(array|string $keys)
    {
        foreach ((array) $keys as $key) {
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
     * @return string
     */
    public function protocol()
    {
        return $this->protocol;
    }

    /**
     * 获取请求 scheme 。即http或https。
     *
     * 如果使用TRUE调用该方法，则将返回带有 :// 前缀的scheme
     *
     * @param  bool  $decorated  是否添加 :// 前缀.
     * @return string
     */
    public function scheme(bool $decorated = false)
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

        if (! $this->entrusted()) {
            return false;
        }

        return in_array($this->server('SSL_HTTPS'), ['ON', 'on']) || in_array($this->server('X_FORWARDED_PROTO'), ['HTTPS', 'https']);
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
        return $this->method;
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
     *
     * @param  string  $format  返回时间格式 默认 'Y-m-d H:i:s'
     * @return int|float
     */
    public function time(string $format = '')
    {
        $format = (! empty($format) && is_string($format)) ? $format : 'Y-m-d H:i:s';

        return date($format, $this->server('REQUEST_TIME'));
    }

    /**
     * 检查请求是否为AJAX请求。
     */
    public function isAjax(): bool
    {
        return isset($this->servers['HTTP_X_REQUESTED_WITH']) && strtoupper($this->servers['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST';
    }

    /**
     * 当前是否Pjax请求
     *
     *
     * @param  bool  $pjax  true 获取原始pjax请求
     */
    public function isPjax(bool $pjax = false): bool
    {
        return ! empty($val = $this->server('HTTP_X_PJAX')) ? ($pjax ? $val : true) : false;
    }

    public function isPost(): bool
    {
        return $this->method() == 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() == 'GET';
    }

    /**
     * 获取网页是从哪个页面链接过来的
     *
     * @param  null  $default  默认值
     */
    public function referrer($default = null): array|string|null
    {
        return $this->server('HTTP_REFERRER', $default);
    }

    /**
     * 重写默认URI解析器列表。
     *
     * 解析器数组的元素是$_SERVER数组中的键，具有可选的“modifier”函数来调整返回值。
     *
     * @param  array  $resolvers  URI解析器的优先级排序列表。
     * @return array
     */
    public function resolvers(array $resolvers = [])
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
     */
    public function fullUrl(): string
    {
        return $this->scheme(true).$this->host().$this->port(true).$this->uri().$this->query(true);
    }

    /**
     * 获取请求的URL. e.g. http://a.com/bar?q=foo
     *
     * @return string
     */
    public function url()
    {
        return $this->scheme(true).$this->host().$this->port(true).$this->uri().$this->query(true);
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
     * @param  bool  $decorated  添加 ? 前缀.
     * @return string
     */
    public function query(bool $decorated = false)
    {
        return $decorated ? "?$this->query" : $this->query;
    }

    /**
     * 获取请求的URI段。
     *
     * @param  array  $default  默认值
     * @return array
     */
    public function segments(array $default = [])
    {
        return explode('/', trim($this->uri() ?: $default, '/'));
    }

    /**
     * 获取请求的特定URI段。
     *
     * 使用负索引以相反顺序检索段。
     *
     * @param  int  $index  A one-based segment index. 基于一的段索引。
     * @param  string|null  $default  默认值
     * @return string
     */
    public function segment(int $index, ?string $default = null)
    {
        $segments = $this->segments();

        if ($index < 0) {
            $index *= -1;
            $segments = array_reverse($segments);
        }

        return $this->parseData($segments, $index - 1, $default);
    }

    /**
     * 从HTTP接受标头中获取有序的值数组。
     *
     * @param  string  $terms  HTTP接受标头。
     * @param  string  $regex  用于解析标头的正则表达式。
     * @return array
     */
    protected function parse(string $terms, string $regex)
    {
        $result = [];

        foreach (array_reverse(explode(',', $terms)) as $part) {
            if (preg_match("/{$regex}/", $part, $m)) {
                $quality = $m['quality'] ?? 1;
                $result[$m['term']] = $quality;
            }
        }

        arsort($result);

        return array_keys($result);
    }

    /**
     * 获取客户端首选的语言。
     *
     * 默认值 'en'.
     *
     * @param  string  $default  默认值
     * @return string
     */
    public function language($default = null)
    {
        return $this->parseData($this->languages(), 0, $default);
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
     * @param  string|null  $default  默认值
     * @return string
     */
    public function charset(?string $default = null)
    {
        return $this->parseData($this->charsets(), 0, $default);
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
     * @param  string|null  $default  默认值
     * @return string
     */
    public function userAgent(?string $default = null)
    {
        return $this->userAgent ?? $default;
    }

    /**
     * 设置一个或多个受信任的代理服务器。
     *
     * 默认情况下，所有代理服务器都是受信任的。当请求客户端IP地址时，使用此方法只信任有限的一组代理服务器。
     *
     * 此方法不是累积的。
     *
     * @param  mixed  $proxies  受信任代理的IP地址或受信任代理阵列。
     * @return $this
     */
    public function setProxies($proxies)
    {
        $this->proxies = (array) $proxies;

        return $this;
    }

    /**
     * 检查是否所有代理服务器都是受信任的，或者此请求是否是通过受信任的代理服务器发送的。
     *
     * @return bool
     */
    public function entrusted()
    {
        return empty($this->proxies) || isset($this->servers['REMOTE_ADDR']) && in_array($this->servers['REMOTE_ADDR'], $this->proxies);
    }

    /**
     * 解析web服务器的名称。
     *
     * 解析顺序是请求的“host”标头，然后是“server name”指令，然后是服务器IP地址。
     * 端口号（如果存在）将被剥离。
     *
     * @param  string|null  $default  默认值
     * @return string
     */
    public function host(?string $default = null)
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
     */
    public function domain(): string
    {
        return $this->scheme().'://'.$this->host();
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
     * @param  bool  $trusted  信任客户端通过HTTP_client_IP设置的IP地址。
     * @return string
     */
    public function ip($trusted = true)
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];

        $ips = [];

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
     */
    public function isMobile(): bool
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), 'wap')) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), 'VND.WAP.WML')) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
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
     * @param  bool  $decorated  前缀为：
     * @return string
     */
    public function port(bool $decorated = false)
    {
        $port = $_SERVER['SERVER_PORT'] ?? ($_SERVER['X_FORWARDED_PORT'] ?? 80);
        $this->port = $port;

        return $decorated ? (in_array($port, [80, 443]) ? '' : ":{$port}") : $port;
    }
}
