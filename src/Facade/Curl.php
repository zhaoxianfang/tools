<?php

namespace zxf\Facade;
/**
 * Curl 网络请求
 *
 * @method static mixed setHeader($header)                              设置http header
 * @method static mixed setTimeout($time = 3)                           设置http 超时时间
 * @method static mixed setProxy($proxy)                                设置http 代理
 * @method static mixed setProxyPort($port)                             设置http 代理端口
 * @method static mixed setReferer($referer = "")                       设置来源页面
 * @method static mixed setUserAgent($agent = "")                       设置用户代理
 * @method static false|resource copyCurl()                             复制句柄
 * @method static mixed reset()                                         重置所有的预先设置的选项
 * @method static mixed showResponseHeader($show)                       http响应中是否显示header，1表示显示
 * @method static mixed setParams($params)                              设置http请求的参数,get或post, e.g. setParams( array('abc'=>'123', 'file1'=>'@/data/1.jpg'));
 * @method static mixed setCaPath($file)                                设置证书路径
 * @method static array|false version()                                 获取cURL版本数组
 * @method static mixed get($url, $data_type = 'json')                  模拟GET请求 e.g. get('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed post($url, $data_type = 'json')                 模拟POST请求 e.g. post('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed put($url, $data_type = 'json')                  模拟put请求 e.g. put('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed delete($url, $data_type = 'json')               模拟delete请求 e.g. delete('http://api.example.com/?a=123&b=456', 'json');
 * @method static mixed patch($url, $data_type = 'json')                模拟patch请求 e.g. patch('http://api.example.com/?a=123&b=456', 'json');
 * @method static bool putFileFromUrlContent($url, $saveName, $path)    异步将远程链接上的内容(图片或内容)写到本地 ($url远程地址   $saveFile 保存在服务器上的文件名(e.g. /root/a/b.jpg))
 * @method static array|mixed upload(string $url = '', string $filePath = '',array $params = [])    上传文件到某个链接地址
 * @method static array|mixed download($url = '', $filePath = '')       下载远程文件到本地
 */
class Curl extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\http\Curl::class;
    }
}