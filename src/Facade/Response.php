<?php

namespace zxf\Facade;

/**
 * http 响应
 *
 * @method static mixed setHeader(mixed $name, mixed $value) 设置响应头
 * @method static mixed getHeader(string $name) 获取响应头
 * @method static array getHeaders() 获取所有响应头
 * @method static mixed setBody(mixed $body) 设置响应内容
 * @method static mixed getBody() 获取响应内容
 * @method static mixed setStatusCode(int $statusCode = 200) 设置响应状态码
 * @method static int getStatusCode() 获取响应状态码
 * @method static mixed redirect(string $url, bool $permanent = false)              重定向($permanent: 是否永久重定向)
 * @method static mixed json(array $data = []) 响应json数据
 * @method static mixed text(string $text = '') 响应文本数据
 * @method static mixed html(string $html = '') 响应html数据
 * @method static mixed setCharset(string $charset = 'UTF-8') 设置编码
 * @method static mixed xml(array $data, array $config = []) 发送xml数据
 * @method static mixed emptyResponse() 发送空白数据
 * @method static mixed setLength(int $length = 0) 设置响应的长度
 * @method static mixed setEtag($etag) 设置 ETag
 * @method static mixed setExpires(int $expires = 0) 设置响应的过期时间
 * @method static mixed setCacheControl(string $cacheControl)                       设置响应的缓存控制(e.g:->setCacheControl('max-age=3600,must-revalidate'))
 * @method static mixed setAuthentication($username, $password = null) 设置响应的身份验证信息
 * @method static mixed setCompression($format) 设置响应的压缩格式
 * @method static mixed sendException(\Exception $exception) 发送异常响应
 * @method static mixed setProxyAuthentication($username, $password = null) 设置响应的代理认证信息
 * @method static mixed jsonp($data, $callback = null) 发送 JSONP 响应
 * @method static mixed sendLargeFile($file) 发送大文件响应
 * @method static mixed setServerInfo($serverName, $serverVersion = null) 设置响应的服务器信息
 * @method static mixed setInterfaceInfo($interfaceName, $version = null) 设置响应的接口信息
 * @method static mixed setLastModified($lastModified) 设置响应的Last-Modified时间
 * @method static resource send() 发送响应并终止当前脚本执行
 * @method static resource download(string $file = '', mixed $filename = null) 下载文件
 * @method static resource csv(array $data, $filename = 'csv_file') 浏览器下载csv数据
 * @method static resource image($imagePath) 直接展示图片内容到浏览器
 */
class Response extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Http\Response::class;
    }
}
