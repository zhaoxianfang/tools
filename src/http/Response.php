<?php

namespace zxf\http;

/**
 * HTTP 响应
 */
class Response
{

    /**
     * @var object 对象实例
     */
    protected static object $instance;

    private array $headers    = [];
    private mixed $body;
    private mixed $statusCode = 200;

    public function __construct($body = null, $statusCode = 200)
    {
        $this->body       = $body;
        $this->statusCode = $statusCode;
    }

    public static function instance($refresh = false)
    {
        if (is_null(self::$instance) || $refresh) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    //  设置响应头
    // $response->setHeader('Content-Type', 'application/json');
    // $response->setHeader('Access-Control-Allow-Origin', '*');
    public function setHeader(mixed $name, mixed $value)
    {
        if (is_array($name)) {
            $this->headers = array_merge($this->headers, $name);
        } else {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    //  获取响应头
    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    //  获取所有响应头
    public function getHeaders()
    {
        return $this->headers;
    }

    //  设置响应体
    // $response->setBody('{"message": "Hello, world!"}');
    // $response->setBody(''<h1>Hello, World!</h1>'');
    public function setBody(mixed $body)
    {
        $this->body = $body;
        return $this;
    }

    //  获取响应体
    public function getBody()
    {
        return $this->body;
    }

    //  设置状态码
    public function setStatusCode(int $statusCode = 200)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    //  获取状态码
    public function getStatusCode()
    {
        return (int)$this->statusCode;
    }


    /**
     * 重定向
     *
     * @param string $url
     * @param bool   $permanent 是否永久重定向
     *
     * @return $this
     */
    public function redirect(string $url, bool $permanent = false)
    {
        $this->setHeader('Location', $url);
        if ($permanent) {
            $this->setStatusCode(301);
        } else {
            $this->setStatusCode(302);
        }

        return $this;
    }

    //  下载文件
    public function download(string $file = '', mixed $filename = null)
    {
        if (!$filename) {
            $filename = basename($file);
        }
        // 检查文件是否存在
        if (file_exists($file)) {
            // 设置头信息，告诉浏览器该文件为下载文件
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        } else {
            throw new \Exception('文件不存在');
        }
    }

    //  发送 JSON 响应
    // $response->json(['message' => 'Hello, world!']);
    public function json(array $data = [])
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setBody(json_encode($data));

        return $this;
    }

    //  发送文本响应
    public function text(string $text = '')
    {
        $this->setHeader('Content-Type', 'text/plain');
        $this->setBody($text);

        return $this;
    }

    //  发送 HTML 响应
    public function html(string $html)
    {
        $this->setHeader('Content-Type', 'text/html');
        $this->setBody($html);

        return $this;
    }

    //  设置响应字符集
    // $response->setCharset('UTF-8');
    public function setCharset(string $charset = 'UTF-8')
    {
        $this->setHeader('Content-Type', 'text/html; charset=' . $charset);
        return $this;
    }

    //  发送 XML 响应
    public function xml(array $data)
    {
        $this->headers = [];
        $this->setHeader('Content-Type', 'text/xml');
        $this->setBody($this->arrayToXml($data));
        return $this;
    }

    // 将数据对象转换为 SimpleXMLElement 对象
    private function arrayToXml($array, $rootElement = 'root', $xml = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement('<' . $rootElement . '/>');
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayToXml($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }

    //  发送空白响应（空内容响应）
    public function emptyResponse()
    {
        $this->setBody('');
        return $this;
    }

    //  发送 CSV 响应
    public function csv(array $data, $filename = 'csv_file')
    {
        $this->headers = [];
        // 设置响应标头，告诉浏览器要下载的文件类型是CSV
        // 设置响应标头，告诉浏览器要下载的文件类型是CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        // 创建一个文件句柄，选择一个输出流
        $output = fopen('php://output', 'w');

        // 循环遍历数组并将每一行写入CSV文件
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        // 关闭文件句柄
        fclose($output);
        exit();
    }

    //  设置响应的长度
    public function setLength(int $length = 0)
    {
        $this->setHeader('Content-Length', $length);
        return $this;
    }

    //  设置 ETag
    // $response->setEtag('1234567890');
    public function setEtag($etag)
    {
        $this->setHeader('ETag', $etag);
        return $this;
    }

    //  展示图片内容到浏览器
    // $response->image();
    public function image($imagePath)
    {
        // 检查文件是否存在
        if (file_exists($imagePath)) {
            // 获取文件信息
            $finfo     = finfo_open(FILEINFO_MIME_TYPE);
            $mediaType = finfo_file($finfo, $imagePath);
            finfo_close($finfo);
            // 设置正确的头信息
            header('Content-Type: ' . $mediaType);
            ob_clean();
            flush();
            // 将图片内容发送到浏览器
            readfile($imagePath);
            exit;
        } else {
            throw new Exception('图片不存在');
        }
    }

    //  设置响应的过期时间
    public function setExpires(int $expires = 0)
    {
        $this->setHeader('Expires', $expires);
        return $this;
    }

    //  设置响应的缓存控制
    // $response->setCacheControl('max-age=3600, must-revalidate');
    public function setCacheControl(string $cacheControl)
    {
        $this->setHeader('Cache-Control', $cacheControl);
        return $this;
    }


    //  设置响应的身份验证信息
    public function setAuthentication($username, $password = null)
    {
        if (!$password) {
            $this->setHeader('WWW-Authenticate', 'Basic');
        } else {
            $this->setHeader('WWW-Authenticate', 'Basic realm="' . $username . '"');
        }

        return $this;
    }

    //  设置响应的压缩格式
    public function setCompression($format)
    {
        $this->setHeader('Content-Encoding', $format);
        return $this;
    }

    //  发送异常响应
    // $response->sendException(new Exception('Something went wrong!'));
    public function sendException(\Exception $exception)
    {
        $this->setStatusCode($exception->getCode());
        $this->setBody($exception->getMessage());

        return $this;
    }


    //  设置响应的代理认证信息
    public function setProxyAuthentication($username, $password = null)
    {
        if (!$password) {
            $this->setHeader('Proxy-Authenticate', 'Basic');
        } else {
            $this->setHeader('Proxy-Authenticate', 'Basic realm="' . $username . '"');
        }

        return $this;
    }

    //  发送 JSONP 响应
    public function jsonp($data, $callback = null)
    {
        if (!$callback) {
            return $this->json($data);
        }

        $this->setHeader('Content-Type', 'application/javascript');
        $this->setBody($callback . '(' . json_encode($data) . ');');

        return $this;
    }

    //  发送大文件响应
    public function sendLargeFile($file)
    {
        $this->setHeader('Content-Type', 'application/octet-stream');
        $this->setBody(fread($file, filesize($file)));

        return $this;
    }

    //  设置响应的服务器信息
    // $response->setServerInfo('Apache/2.4.18 (Unix) PHP/5.6.34', '1.0');
    public function setServerInfo($serverName, $serverVersion = null)
    {
        $this->setHeader('Server', $serverName);
        if ($serverVersion) {
            $this->setHeader('Server', $serverName . '/' . $serverVersion);
        }

        return $this;
    }

    //  设置响应的接口信息
    // $response->setInterfaceInfo('APIv1', '1.0');
    public function setInterfaceInfo($interfaceName, $version = null)
    {
        $this->setHeader('X-Interface', $interfaceName);
        if ($version) {
            $this->setHeader('X-Interface', $interfaceName . '/' . $version);
        }

        return $this;
    }


    //  发送包含会话ID的响应
    public function sessionId($sessionId)
    {
        $this->setHeader('Cookie', 'PHPSESSID=' . $sessionId);
        return $this;
    }


    //  设置响应的Last-Modified时间
    // $response->setLastModified('Tue, 15 Nov 1994 12:45:26 GMT');
    public function setLastModified($lastModified)
    {
        $this->setHeader('Last-Modified', $lastModified);
        return $this;
    }

    /**
     * 发送响应并终止当前脚本执行
     */
    public function send()
    {
        // 设置响应头
        empty($this->headers) || $this->headers = ['Content-Type' => 'text/html; charset=UTF-8'];
        foreach ($this->headers as $header => $value) {
            header($header . ': ' . $value);
        }

        // 设置响应状态码
        http_response_code($this->statusCode ?? 200);

        echo $this->body;
        exit();
    }
}
