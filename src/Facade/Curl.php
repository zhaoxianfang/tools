<?php

namespace zxf\Facade;
/**
 * Curl 网络请求
 *
 * @method static mixed setHeader(array $header, bool $isAppend = true, bool $setLength = false)                        设置http header
 * @method static mixed setTimeout(int $time = 3)                                                                       设置http 超时时间
 * @method static mixed setProxy(string $proxy)                                                                         设置http 代理
 * @method static mixed setProxyPort(int $port)                                                                         设置http 代理端口
 * @method static mixed setReferer(string $referer = '')                                                                设置来源页面
 * @method static mixed setUserAgent(string $agent = '')                                                                设置用户代理
 * @method static false|resource copyCurl()                                                                             复制句柄
 * @method static mixed reset()                                                                                         重置所有的预先设置的选项
 * @method static mixed showResponseHeader($show)                                                                       http响应中是否显示header，1表示显示
 * @method static mixed setParams(array $params, string $data_type = 'array', bool $excludeZhCN = false)                设置http请求的参数,get或post, e.g. setParams( array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
 * @method static mixed setCaPath(string $file)                                                                         设置证书路径
 * @method static array|false version()                                                                                 获取cURL版本数组
 * @method static mixed get(string $url, string $data_type = 'json')                                                    模拟GET请求 e.g. get('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed post(string $url, string $data_type = 'json')                                                   模拟POST请求 e.g. post('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed put(string $url, string $data_type = 'json')                                                    模拟put请求 e.g. put('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed delete(string $url, string $data_type = 'json')                                                 模拟delete请求 e.g. delete('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed patch(string $url, string $data_type = 'json')                                                  模拟patch请求 e.g. patch('http://api.example.com/?a=123&b=456', 'json');
 * @method static bool putFileFromUrlContent(string $url, string $saveFile)                                             异步将远程链接上的内容(图片或内容)写到本地 ($url远程地址   $saveFile 保存在服务器上的文件名(e.g. /root/a/b.jpg))
 * @method static array|mixed upload(string $url = '', string $filePath = '', string $name = '', array $params = [])    上传文件到某个链接地址
 * @method static array|mixed download(string $url = '', string $filePath = '')                                         下载远程文件到本地
 * @method static mixed inject(\Closure $func)                                                                          闭包方式注入Curl
 */
class Curl extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Http\Curl::class;
    }
}