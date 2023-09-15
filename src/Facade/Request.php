<?php

namespace zxf\Facade;
/**
 * http请求信息获取
 *
 * @method static bool isXml($data)                                             数据是否为xml格式
 * @method static string|null getContentType()                                  获取请求类型
 * @method static array body()                                                  返回包含所有输入数据的单个数组
 * @method static bool overridden()                                             检查请求方法是否已被重写
 * @method static string|array|null get($key = null, $default = null)           获取get请求方式的请求参数($key:指定字段，不传值表示全部，$default:未获取到$key时返回的默认值)
 * @method static string|array|null post($key = null, $default = null)          获取post请求方式的请求参数($key:指定字段，不传值表示获取全部$_POST参数包含xml，$default:未获取到$key时返回的默认值)
 * @method static string|array|null put($key = null, $default = null)           获取put请求方式的请求参数($key:指定字段，不传值表示全部，$default:未获取到$key时返回的默认值)
 * @method static string|array|null delete($key = null, $default = null)        获取delete请求方式的请求参数($key:指定字段，不传值表示全部，$default:未获取到$key时返回的默认值)
 * @method static mixed addPost($keys = null, $value = null)                    追加post参数($keys:需要批量添加时传入二维数组，单个添加时候传入字符串; $value 被追加的值，$keys为字符串时候生效)
 * @method static mixed addGet($keys = null, $value = null)                     追加get参数($keys:需要批量添加时传入二维数组，单个添加时候传入字符串; $value 被追加的值，$keys为字符串时候生效)
 * @method static array files($key = null, $default = null)                     获取$_FILES数据($key:指定字段，不传值表示全部，$default:未获取到$key时返回的默认值)
 * @method static string|array|null session($key = null, $default = null)       获取$_SESSION数据($key:指定字段，不传值表示全部，$default:未获取到$key时返回的默认值)
 * @method static string|array|null cookie($key = null, $default = null)        获取$_COOKIE数据($key:指定字段，不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null env($key = null, $default = null)           获取$_ENV数据($key:指定字段，不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null server($key = null, $default = null)        获取$_SERVER数据($key:指定字段，不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null headers($key = null, $default = null)       获取请求头数据($key:指定字段，不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null input($key = null, $default = null)         获取所有get、post数据($key:指定字段,不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null all($key = null, $default = null)           获取所有get、post、files数据($key:指定字段,不传值表示全部,$default:未获取到$key时返回的默认值)
 * @method static string|array|null only($keys = null)                          从input数据中过滤出$key($keys:指定字段名称字符串，多个字段可传递数组)
 * @method static string|array|null except($keys = null)                        和only相反，需要删除某个字段($keys:指定字段名称字符串，多个字段可传递数组)
 * @method static bool has($keys = null)                                        检查输入数据是否包含项或所有指定的项数组,如果任何输入项为空字符串，将返回FALSE($keys:指定字段名称字符串，多个字段可传递数组)
 * @method static string protocol($default = 'HTTP/1.1')                        获取请求的协议。例如HTTP/1.1
 * @method static string scheme($decorated = false)                             获取请求 scheme 即http或https,如果使用TRUE调用该方法，则将返回带有 :// 前缀的scheme
 * @method static bool secure()                                                 检查请求是否通过HTTPS进行。
 * @method static string method()                                               获取请求方法。例如GET、POST、PUT、DELETE
 * @method static bool safe()                                                   检查请求方法是否安全。即GET或HEAD。
 * @method static bool isAjax()                                                 检查请求是否为AJAX请求。
 * @method static bool isPjax(bool $pjax = false)                               检查请求是否为PJAX请求($pjax = true 获取原始pjax请求)。
 * @method static bool isPost()                                                 检查请求是否为post请求。
 * @method static bool isGet()                                                  检查请求是否为get请求。
 * @method static bool isMobile()                                               检测是否使用手机访问
 * @method static string time( $format = '')                                    获取当前请求的时间（$format 返回时间格式 默认 'Y-m-d H:i:s'）
 * @method static string referrer($default = null)                              获取网页是从哪个页面链接过来的($default:默认值)
 * @method static array resolvers($resolvers = [])                              重写默认URI解析器列表($resolvers: URI解析器的优先级排序列表。)
 * @method static string url()                                                  获取请求的URL
 * @method static string uri()                                                  获取请求的URI
 * @method static string query($decorated = false)                              获取请求查询字符串($decorated 添加 ? 前缀.). e.g. q=search&foo=bar
 * @method static string segment($index, $default = null)                       获取请求的特定URI段
 * @method static array segments($default = [])                                 获取请求的URI段。
 * @method static string language($default = null)                              获取客户端首选的语言。
 * @method static array languages()                                             获取客户端首选语言的有序数组。
 * @method static string accept($default = null, $strict = false)               获取客户端首选的媒体类型
 * @method static array accepts()                                               获取客户端首选的媒体类型的有序数组
 * @method static string charset($default = null)                               获取客户端首选的媒体类型。 默认 'utf-8'.
 * @method static array charsets()                                              获取客户端首选的字符集的有序数组
 * @method static mixed setProxies($proxies)                                    设置一个或多个受信任的代理服务器。
 * @method static string userAgent()                                            获取用户代理. e.g. Mozilla/5.0 (Macintosh; ...)
 * @method static bool entrusted()                                              检查是否所有代理服务器都是受信任的，或者此请求是否是通过受信任的代理服务器发送的。
 * @method static string host($default = null)                                  解析web服务器的名称。
 * @method static string domain()                                               获取当前包含协议的域名
 * @method static string ip($trusted = true)                                    获取客户端IP地址($trusted 信任客户端通过HTTP_client_IP设置的IP地址。)如果无法获得有效的IP地址。 返回 0.0.0.0
 * @method static string port($decorated = false)                               获取请求的端口号($decorated 前缀为：). e.g. 80
 */
class Request extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\http\Request::class;
    }
}