<?php

/**
 * 常用的一些函数归纳
 */

use JetBrains\PhpStorm\NoReturn;
use zxf\Tools\Collection;

if (! function_exists('i_session')) {
    /**
     * 简便操作 Session 的助手函数
     *
     * @param  string|array|null  $name  键名
     * @param  mixed|null  $value  键值
     * @param  int|null  $expiry  过期时间（秒）
     * @return mixed|null 返回值或者null
     */
    function i_session(string|array|null $name = null, mixed $value = null, ?int $expiry = null): mixed
    {
        $session = \zxf\Tools\Session::instance();

        // 判断是否传入了 $value
        $hasValue = func_num_args() > 1; // 如果参数个数大于1，说明传入了 $value

        // ===== 1、获取session  =====
        // 获取所有值 : i_session('') or i_session(null)
        if (empty($name) && func_num_args() == 1) {
            return $session->all();
        }
        // 获取指定值 : i_session('name')
        if (is_string($name) && ! $hasValue) {
            return $session->get($name);
        }

        // ===== 2、设置session =====
        // 传递了 $name 和 $value : i_session('name', 'value')
        if (is_string($name) && $hasValue && $value !== null) {
            $session->set($name, $value, $expiry);

            return null;
        }

        // $name 是一个数组，表示批量设置 Session 值 : i_session(['name1' => 'value1','name2' => 'value2'])
        if (is_array($name) && ! $hasValue) {
            // 使用数组 批量赋值
            foreach ($name as $key => $val) {
                $session->set($key, $val);
            }

            return null;
        }

        // ===== 3、删除session =====
        // 第二参数为null: i_session('name', null)
        if (is_string($name) && $hasValue && $value === null) {
            $session->delete($name);

            return null;
        }

        // 返回session对象
        return $session;
    }
}
if (! function_exists('cache')) {
    /**
     * cache 助手函数
     */
    function cache($name, $value = null, $expiry = '+1 day')
    {
        $handle = \zxf\Tools\Cache::instance();
        if (is_null($value)) {
            return $handle->delete($name);
        }
        if (! empty($value)) {
            $value = (is_array($value) || is_object($value)) ? json_encode($value) : $value;

            return $handle->set($name, $value, $expiry);
        } else {
            $val = $handle->get($name);

            return is_json($val) ? json_decode_plus($val, true) : $val;
        }
    }
}

if (! function_exists('collection')) {
    /**
     * 集合对象操作
     */
    function collection(iterable $items = []): Collection
    {
        return new Collection($items);
    }
}

if (! function_exists('truncate')) {
    /**
     * 文章去除标签截取文字
     *
     *
     * @DateTime 2018-09-12
     *
     * @param  string  $string  [被截取字符串]
     * @param  int  $start  [起始位置]
     * @param  int  $length  [长度]
     * @param  bool  $append  [是否加...]
     */
    function truncate(string $string, int $start = 0, int $length = 150, bool $append = true): string
    {
        if (empty($string)) {
            return $string;
        }
        $string = detach_html($string);
        $strLen = strlen($string);
        if ($length == 0 || $length >= $strLen) {
            return $string;
        } elseif ($length < 0) {
            $length = $strLen + $length;
            if ($length < 0) {
                $length = $strLen;
            }
        }
        if (function_exists('mb_substr')) {
            $newStr = mb_substr($string, 0, $length, 'UTF-8');
        } elseif (function_exists('iconv_substr')) {
            $newStr = iconv_substr($string, 0, $length, 'UTF-8');
        } else {
            $length = abs($length);
            $len = $start + $length;
            $newStr = '';
            for ($i = $start; $i < $len && $i < $strLen; $i++) {
                if (ord(substr($string, $i, 1)) > 0xA0) {
                    // utf8编码中一个汉字是占据3个字节的，对于其他的编码的字符串，中文占据的字节各有不同，自己需要去修改这个数a
                    $newStr .= substr($string, $i, 3); // 此处a=3;
                    $i += 2;
                    $len += 2; // 截取了三个字节之后，截取字符串的终止偏移量也要随着每次汉字的截取增加a-1;
                } else {
                    $newStr .= substr($string, $i, 1);
                }
            }
        }

        return $newStr.(($append && $string != $newStr) ? '...' : '');
    }
}

if (! function_exists('zxf_substr')) {
    /**
     * 字符串截取
     */
    function zxf_substr($string, $start = 0, $length = 5): bool|string
    {
        $string = str_ireplace(' ', '', $string); // 去除空格
        if (function_exists('mb_substr')) {
            $newstr = mb_substr($string, $start, $length, 'UTF-8');
        } elseif (function_exists('iconv_substr')) {
            $newstr = iconv_substr($string, $start, $length, 'UTF-8');
        } else {
            for ($i = 0; $i < $length; $i++) {
                $tempstring = substr($string, $start, 1);
                if (ord($tempstring) > 127) {
                    $i++;
                    if ($i < $length) {
                        $newstring[] = substr($string, $start, 3);
                        $string = substr($string, 3);
                    }
                } else {
                    $newstring[] = substr($string, $start, 1);
                    $string = substr($string, 1);
                }
            }
            $newstr = implode($newstring);
        }

        return $newstr;
    }
}

if (! function_exists('remove_str_emoji')) {
    // 移除字符串中的 emoji 表情
    function remove_str_emoji($str): string
    {
        $mbLen = mb_strlen($str);
        $strArr = [];
        for ($i = 0; $i < $mbLen; $i++) {
            $mbSubstr = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($mbSubstr) >= 4) {
                continue;
            }
            $strArr[] = $mbSubstr;
        }

        return implode('', $strArr);
    }
}

if (! function_exists('check_str_exists_emoji')) {
    // 判断字符串中是否含有 emoji 表情
    function check_str_exists_emoji($str): bool
    {
        $mbLen = mb_strlen($str);
        $strArr = [];
        for ($i = 0; $i < $mbLen; $i++) {
            $strArr[] = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($strArr[$i]) >= 4) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('is_crawler')) {
    /**
     * [isCrawler 检测是否为爬虫]
     *
     *
     * @DateTime 2019-12-24
     *
     * @param  bool  $returnName  [是否返回爬虫名称]
     * @param  array  $extendRules  [自定义额外规则：eg: ['Googlebot'=> 'Google Bot'])]
     * @return bool|string [description]
     */
    function is_crawler(bool $returnName = false, array $extendRules = []): bool|string
    {
        $userAgent = is_laravel() ? request()->userAgent() : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        if (! empty($userAgent)) {
            // 扩展的爬虫标识符列表，包括更多类型的爬虫
            $crawlers = [
                // 主流搜索引擎爬虫
                'Googlebot' => 'Google Bot',
                'Bingbot' => 'Bing Bot',
                'Slurp' => 'Yahoo Slurp',
                'DuckDuckBot' => 'DuckDuckGo Bot',
                'Baiduspider' => 'Baidu Spider',
                'YandexBot' => 'Yandex Bot',
                'Sogou web spider' => 'Sogou Web Spider', // Sogou Web 爬虫
                'Sogou' => 'Sogou Spider',
                'Exabot' => 'ExaBot',
                'ia_archiver' => 'Alexa Bot',
                '360Spider' => '360 Search Bot',
                'SeznamBot' => 'Seznam Bot',
                'YisouSpider' => 'Yisou Spider',
                'Bytespider' => 'Byte Spider',

                // 国际搜索引擎爬虫
                'Yeti' => 'Naver Yeti',
                'Coccocbot' => 'CocCoc Bot',
                'archive.org_bot' => 'Internet Archive Bot',
                'MojeekBot' => 'Mojeek Bot',
                'TroveBot' => 'Trove Bot',

                // 数据抓取和内容分析
                'SemrushBot' => 'SEMrush Bot',
                'AhrefsBot' => 'Ahrefs Bot',
                'ZoominfoBot' => 'Zoominfo Bot',
                'DotBot' => 'Moz DotBot',
                'BLEXBot' => 'BLEXBot',
                'MegaIndex' => 'MegaIndex Crawler',
                'SiteAnalyzer' => 'Site Analyzer Bot',
                'DataForSeoBot' => 'DataForSeo Bot',
                'NetcraftSurveyAgent' => 'Netcraft Survey Agent',

                // API与数据采集工具
                'axios' => 'Axios Client',
                'Scrapy' => 'Scrapy Framework',
                'curl' => 'cURL',
                'wget' => 'Wget',

                // 开发语言脚本判断
                'python-requests|python-urllib|scrapy\/|httpx\/|aiohttp\/|tornado\/|python\/[0-9.]+' => 'Python Script',
                'okhttp\/|apache-httpclient\/|jersey-client\/|unirest-java\/|java\/[0-9.]+' => 'JAVA Script',
                'node-fetch\/|axios\/|superagent\/|got\/|node\.js\/[0-9.]+|needle\/|request-promise\/|request' => 'Node.js Script',
                'httparty\/|rest-client\/|faraday\/|mechanize\/|ruby\/[0-9.]+' => 'Ruby Script',
                'guzzlehttp\/|symfony-httpclient\/|curl-php\/|http-request\/|php\/[0-9.]+' => 'PHP Script',
                'lwp::useragent\/|http-simple\/|libwww-perl\/|perl\/[0-9.]+' => 'Perl Script',
                'go-http-client\/|gorequest\/|resty\/|go\/[0-9.]+' => 'Go Script',
                'reqwest\/|hyper\/|rust\/[0-9.]+' => 'Rust Script',
                'powershell\/|invoke-webrequest|invoke-restmethod' => 'PowerShell Script',
                'alamofire\/|swift\/[0-9.]+' => 'Swift Script',
                'httpoison\/|hackney\/|elixir\/[0-9.]+' => 'Elixir Script',
                'akka-http\/|dispatch\/|scalaj-http\/|scala\/[0-9.]+' => 'Scala Script',
                'http-conduit\/|wreq\/|haskell\/[0-9.]+' => 'Haskell Script',
                'dart-http\/|dart\/[0-9.]+' => 'Dart Script',
                'clj-http\/|http-kit\/|clojure\/[0-9.]+' => 'Clojure Script',
                'R-curl\/|R-httr\/' => 'R Script',
                'lua-http\/|luasocket\/|lua\/[0-9.]+' => 'Lua Script',

                // 常用的开发与调试工具
                'Postman' => 'Postman',
                'Insomnia' => 'Insomnia REST Client',
                'RestSharp' => 'RestSharp',
                'Apipost' => 'Apipost',

                // 通用爬虫标识
                'Spider' => 'Generic Spider',
                'Crawler' => 'Generic Crawler',
                'Bot' => 'Generic Bot',
            ];

            if (! empty($extendRules)) {
                $crawlers = array_merge($crawlers, $extendRules);
            }

            // 使用不区分大小写的正则表达式匹配 User-Agent 中的爬虫关键字
            $pattern = '/'.implode('|', array_keys($crawlers)).'/i';
            preg_match_all($pattern, $userAgent, $matches);

            if (! empty($matches[0])) {
                // 返回第一个匹配的爬虫名称
                $matchedCrawler = $matches[0][0];
                $crawlerName = ! empty($crawlers[$matchedCrawler])
                    ? $crawlers[$matchedCrawler]
                    : (! empty($crawlers[ucfirst($matchedCrawler)])
                        ? $crawlers[ucfirst($matchedCrawler)]
                        : $matchedCrawler
                    );

                // 如果匹配到 "Spider" 、 "Crawler" 和 “Bot”，重新截取出前面的字符串
                if (in_array(strtolower(substr($crawlerName, 0, 7)), ['generic', 'unknown'])) {
                    $suffix = (stripos($matchedCrawler, 'Spider') !== false)
                        ? 'Spider'
                        : (stripos($matchedCrawler, 'Crawler') !== false
                            ? 'Crawler'
                            : (stripos($matchedCrawler, 'Bot') !== false
                                ? 'Bot'
                                : ''
                            )
                        );
                    if (! empty($suffix)) {
                        // 找到 "Spider" 或 "Crawler" 的位置
                        $pattern = '/\s+(\S+)'.$suffix.'/i';

                        if (preg_match($pattern, $userAgent, $subMatches)) {
                            if (! empty($subMatches[0])) {
                                return $returnName ? $subMatches[1].$suffix : true;
                            } else {
                                return $returnName ? $crawlerName : true;
                            }
                        }
                    }

                    return $returnName ? $crawlerName : true;
                }

                return $returnName ? $crawlerName : true;
            }
        }

        // 没有匹配到任何爬虫
        return $returnName ? '' : false;
    }
}

if (! function_exists('img_to_gray')) {
    /**
     * [img_to_gray 把彩色图片转换为灰度图片,支持透明色]
     *
     *
     * @DateTime 2019-06-24
     *
     * @param  string  $imgFile  [源图片地址]
     * @param  string  $saveFile  [生成目标地址,为空时直接输出到浏览器]
     * @return bool [true:成功；false:失败]
     */
    function img_to_gray(string $imgFile = '', string $saveFile = ''): bool
    {
        if (! $imgFile) {
            return false;
        }
        $imgInfo = pathinfo($imgFile);
        switch ($imgInfo['extension']) {
            // 图片后缀
            case 'png':
                $block = imagecreatefrompng($imgFile); // 从 PNG 文件或 URL 新建一图像
                break;
            case 'jpg':
                $block = imagecreatefromjpeg($imgFile); // 从 JPEG 文件或 URL 新建一图像
                break;
            default:
                return false;
        }
        $color = imagecolorallocatealpha($block, 0, 0, 0, 127); // 拾取一个完全透明的颜色
        imagealphablending($block, false);                      // 关闭混合模式，以便透明颜色能覆盖原画布
        // 生成灰度图
        if (! $block || ! imagefilter($block, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        // imagefilter($block, IMG_FILTER_BRIGHTNESS, -35);//亮度降低35
        imagefill($block, 0, 0, $color);                        // 填充
        imagesavealpha($block, true);                           // 设置保存PNG时保留透明通道信息
        // 图片后缀 生成图片
        switch ($imgInfo['extension']) {
            case 'png':
                if ($saveFile) {
                    imagepng($block, $saveFile); // 生成图片
                } else {
                    header('Content-type: image/png');
                    imagepng($block);     // 生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            case 'jpg':
                if ($saveFile) {
                    imagejpeg($block, $saveFile); // 生成图片
                } else {
                    header('Content-Type: image/jpeg');
                    imagejpeg($block);    // 生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            default:
                return false;
        }

        return true;
    }
}

if (! function_exists('del_dirs')) {
    /**
     * 删除文件夹
     *
     * @param  string  $dirname  目录
     * @param  bool  $delSelf  是否删除自身
     */
    function del_dirs(string $dirname, bool $delSelf = true): bool
    {
        if (! is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $item) {
            $todo = ($item->isDir() ? 'rmdir' : 'unlink');
            $todo($item->getRealPath());
        }
        if ($delSelf) {
            rmdir($dirname);
        }

        return true;
    }
}
if (! function_exists('del_dir')) {
    /**
     * 删除文件夹及其文件夹下所有文件
     */
    function del_dir($dir): bool
    {
        // 先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $fullPath = $dir.'/'.$file;
                is_dir($fullPath) ? del_dir($fullPath) : unlink($fullPath);
            }
        }
        closedir($dh);

        // 删除当前文件夹：
        return rmdir($dir);
    }
}

if (! function_exists('dir_is_empty')) {
    /**
     * 判断文件夹是否为空
     */
    function dir_is_empty(string $dir): bool
    {
        $res = false;
        if ($handle = opendir($dir)) {
            while (! $res && ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $res = true;
                }
            }
        }
        closedir($handle);

        return $res;
    }
}

if (! function_exists('create_dir')) {
    /**
     * 递归创建目录
     *
     * @param  string  $dir  目录
     * @param  int  $permissions  权限
     */
    function create_dir(string $dir, int $permissions = 0755): bool
    {
        return is_dir($dir) or (create_dir(dirname($dir), $permissions) and mkdir($dir, $permissions, true));
    }
}

if (! function_exists('create_dir_or_filepath')) {
    /**
     * 创建文件夹或文件
     *
     * @param  string  $path  文件夹或者文件路径
     */
    function create_dir_or_filepath(string $path = '', int $permissions = 0755): bool
    {
        // 如果路径不存在，则尝试创建它
        if (! file_exists($path)) {
            // 创建目录（如果不存在）
            $dir = dirname($path);
            if (! is_dir($dir) && ! mkdir($dir, $permissions, true) && ! is_dir($dir)) {
                // 创建文件夹失败
                return false;
            }
            // 如果不是现有目录，则尝试创建文件
            if (! is_dir($path) && ! touch($path)) {
                // 创建文件失败
                return false;
            }
        }

        // 路径已存在或成功创建
        return true;
    }
}

if (! function_exists('get_filesize')) {
    /**
     * 获取文件的大小
     *
     * @param  string  $filePath  文件路径
     */
    function get_filesize(string $filePath): string
    {
        return byteFormat(stat($filePath)['size']);
    }

}
if (! function_exists('byteFormat')) {
    /**
     * 文件字节转具体大小 array("B", "KB", "MB", "GB", "TB", "PB","EB","ZB","YB")， 默认转成M
     *
     * @param  int  $size  文件字节
     */
    function byteFormat(int $size, int $dec = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }

        return round($size, $dec).$units[$pos];
    }
}

if (! function_exists('response_and_continue')) {
    /**
     * 输出json后继续在后台执行指定方法
     *
     *
     * @DateTime 2019-01-07
     *
     * @param  array  $responseDara  立即响应的数组数据
     * @param  string|array  $backendFun  需要在后台执行的方法
     * @param  array  $backendFunArgs  给在后台执行的方法传递的参数
     * @param  int  $setTimeLimit  设置后台响应可执行时间
     *
     * @demo     ：先以json格式返回$data，然后在后台执行 $this->pushSuggestToJyblSys(array('suggId' => $id))
     *         response_and_continue($data, array($this, "pushSuggestToJyblSys"), array('suggId' => $id));
     */
    function response_and_continue(array $responseDara, string|array $backendFun, array $backendFunArgs = [], int $setTimeLimit = 0): void
    {
        ignore_user_abort(true);
        set_time_limit($setTimeLimit);
        ob_end_clean();
        ob_start();
        // Windows服务器
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo str_repeat(' ', 4096);
        }
        // 返回结果给ajax
        echo json_encode($responseDara);
        $size = ob_get_length();
        header("Content-Length: $size");
        header('Connection: close');
        header('HTTP/1.1 200 OK');
        header('Content-Encoding: none');
        header('Content-Type: application/json;charset=utf-8');
        ob_end_flush();
        if (ob_get_length()) {
            ob_flush();
        }
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        sleep(3);
        ignore_user_abort(true);
        set_time_limit($setTimeLimit);
        if (! empty($backendFun)) {
            call_user_func_array($backendFun, $backendFunArgs);
        }
    }
}

if (! function_exists('num_to_cn')) {
    /**
     * 数字转换为中文
     *      支持金额转换和小数转换
     *
     * @param  float|int|string  $number  需要转换的数字
     * @param  bool  $mode  模式[true:金额（默认）,false:普通数字表示]
     * @param  bool  $sim  使用小写（默认）
     *
     * @throws Exception
     */
    function num_to_cn(float|int|string $number, bool $mode = true, bool $sim = true): string
    {
        if (! is_numeric($number)) {
            throw new \Exception('传入参数不是一个数字！');
        }
        // 数字大小写
        $char = $sim ? ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'] : ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        // 每一个数级的 四个位置
        $twoUnit = $sim ? ['', '十', '百', '千'] : ['', '拾', '佰', '仟'];
        // 每一个数级的 单位; 1古戈尔 = 10¹⁰⁰， 古戈尔也称为 不可说
        // 1恒河沙 = 10⁵²（佛教用语）
        // 1不可思议 = 10⁶⁴
        // 1腾 = 10¹²⁸
        $twoChat = [
            '', '万', '亿', '兆', '京', '垓', '秭', '穰', '沟', '涧', '正', '载', '极', '恒河沙', '阿僧祇', '那由他', '不可思议',
            '无量','大数', '无量大数', '不可称', '不可量', '不可数', '不可思', '不可议','古戈尔',
            '不可说数', '无边数', '无等数', '无等无等数', '无限数', '无限无边数', '腾',
        ];
        $moneyUnit = ['角', '分', '厘', '毫', '丝', '忽', '微', '纤', '沙'];

        // 将整数部分和小数部分分开
        [$num, $dec] = (str_contains($number, '.')) ? [substr($number, 0, strpos($number, '.')), substr($number, strpos($number, '.') + 1)] : [$number, ''];

        // 小数部分
        $decNumStr = '';
        // 整数部分
        $roundNum = [];

        // 将小数部分转换为中文
        for ($j = 0; $j < strlen($dec); $j++) {
            $decNum[$j] = $char[$dec[$j]];
            if ($mode) {
                if ($j < count($moneyUnit)) {
                    $decNumStr .= $decNum[$j].$moneyUnit[$j];
                }
            } else {
                $decNumStr .= $decNum[$j];
            }
        }

        // 反转字符串 处理整数部分
        $str = $mode ? strrev((int) ($num)) : strrev($num);

        $hasZero = false; // 数级上是否有零
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            // $str[$i] 小写数字 eg: 2
            $roundNum[$i] = $char[$str[$i]]; // 单个大写数字 eg : 贰

            // 每四位一组，处理中文单位
            if ($i % 4 == 0) {
                $hasZero = false;
                $hasValue = false; // 数级上是否有值
                // 判断数级上是否有值
                for ($k = 0; $k < 4; $k++) {
                    if (! empty($str[$i + $k])) {
                        $hasValue = true;
                        break;
                    }
                }
                if (! $hasValue) {
                    $roundNum[$i] = '';
                } else {
                    // 一个数级的单位，处理每一级的个位
                    if (empty($str[$i])) { // 零万 零亿 等 处理成 万 亿
                        $roundNum[$i] = $twoChat[floor($i / 4)]; // xx万 xx亿
                    } else {
                        $roundNum[$i] .= $twoChat[floor($i / 4)]; // xx万 xx亿
                    }
                }
            } else {
                if (! empty($str[$i])) {
                    $roundNum[$i] .= $twoUnit[$i % 4]; // 加单位 十百千
                    if ($str[$i] == 1 && $i % 4 == 1 && empty($str[$i + 1])) { // 一十 处理成 十
                        $roundNum[$i] = $twoUnit[$i % 4];
                    }
                } else {
                    if ($hasZero) {
                        $roundNum[$i] = '';
                    }
                    // 判断低一位数
                    if (isset($str[$i - 1])) {
                        $hasZero = true;
                        $roundNum[$i] = ! empty($str[$i - 1]) ? '零' : '';
                    }
                }
            }
        }
        // 拼接整数部分和小数部分
        $roundNumStr = implode('', array_reverse($roundNum)); // 整数

        return $roundNumStr.($mode ? '元' : '').((! empty($decNumStr) && ! $mode) ? '点' : '').$decNumStr;
    }
}

if (! function_exists('num_to_word')) {
    /**
     * 数字转换为英文
     *
     * @param  float|int|string  $number  需要转换的数字
     *
     * @throws Exception
     */
    function num_to_word(float|int|string $number): string
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

        // 数字转换
        return $formatter->format($number);
    }
}

if (! function_exists('num_to_zhCN')) {
    /**
     * 数字转换为英文
     *
     * @param  float|int|string  $number  需要转换的数字
     *
     * @throws Exception
     */
    function num_to_zhCN(float|int|string $number): string
    {
        $formatter = new \NumberFormatter('zh_cn', \NumberFormatter::SPELLOUT);
        // 数字转换
        $str = $formatter->format($number);

        // 把〇替换成零
        return str_replace('〇', '零', $str);
    }
}

if (! function_exists('tree_to_array')) {
    /**
     * 树形结构 转为 二维数组 [tip:和 array_to_tree 可互相转化]
     *
     * @param  array  $array  树形数据
     * @param  string  $childField  子级的键名
     * @param  int  $rootId  定义根ID的值
     * @param  string  $keyField  定义主键的字段名
     * @param  string  $pidField  定义父级字段名
     */
    function tree_to_array(array $array, string $childField = 'children', int $rootId = 0, string $keyField = 'id', string $pidField = 'pid'): array
    {
        foreach ($array as $tempKey => $tempVal) {
            $array = is_numeric($tempKey) ? reset($array) : $array;
            break;
        }
        // 已经定义指定主键的直接使用，否则使用随机数
        $array[$keyField] = $array[$keyField] ?? mt_rand(1, 1000).sprintf('%03d', mt_rand(1, 100));
        $array[$pidField] = $rootId;
        $children = [];
        if (isset($array[$childField]) && is_array($array[$childField])) {
            $children = tree_to_array($array[$childField], $childField, $array[$keyField], $keyField, $pidField);
        }
        unset($array[$childField]);

        return ! empty($children) ? array_merge([$array], $children) : [$array];
    }
}

if (! function_exists('array_to_tree')) {
    /**
     * 二维数组 转为 树形结构 [tip:和 tree_to_array 可互相转化]
     *
     * @param  array  $array  二维数组
     * @param  int  $parentId  父级键的值;eg:0
     * @param  string  $keyField  父级主键字段名称;eg:id
     * @param  string  $pidField  关联父级使用的键名;eg:pid  [使用pid去关联id]
     * @param  string  $childField  定义包含子集的键名;eg:children
     */
    function array_to_tree(array $array, int $parentId = 0, string $keyField = 'id', string $pidField = 'pid', string $childField = 'children'): array
    {
        $tree = [];
        foreach ($array as $item) {
            if ($item[$pidField] == $parentId) {
                $children = array_to_tree($array, $item[$keyField], $keyField, $pidField, $childField);
                if (! empty($children)) {
                    $item[$childField] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}

if (! function_exists('show_img')) {
    /*
     * 页面直接输出图片
     */
    #[NoReturn]
    function show_img($imgFile = ''): void
    {
        header('Content-type:image/png');
        exit(file_get_contents($imgFile));
    }
}

if (! function_exists('string_to_utf8')) {
    /*
     * 字符串自动转utf8编码
     */
    function string_to_utf8(string $str = ''): array|bool|string|null
    {
        return mb_convert_encoding($str, 'UTF-8', 'auto');
    }
}

if (! function_exists('string_to_gbk')) {
    /*
     * 字符串自动转gbk编码
     */
    function string_to_gbk(string $str = ''): array|bool|string|null
    {
        return mb_convert_encoding($str, 'GBK', 'auto');
    }
}

if (! function_exists('show_json')) {
    /*
     * 对json数据格式化输入展示 [转化为json格式，并格式化样式]
     */
    function show_json(mixed $data = []): string
    {
        if (empty($data)) {
            return '';
        }
        if (is_string($data)) {
            $data = is_json($data) ? json_decode($data, true) : [];
        }
        $data = is_array($data) ? $data : obj2Arr($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('is_idcard')) {
    /**
     * 判断是否为身份证
     */
    function is_idcard($idCard): bool
    {
        $id_card = trim($idCard);
        if (strlen($id_card) == 18) {
            return idcard_checksum18($id_card);
        } elseif ((strlen($id_card) == 15)) {
            $id_card = idcard_15to18($id_card);

            return idcard_checksum18($id_card);
        }

        return false;

    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    function idcard_verify_number($idcard_base): bool|string
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        // 加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        // 校验码对应值
        $verify_number_list = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;

        return $verify_number_list[$mod];
    }

    // 将15位身份证升级到18位
    function idcard_15to18($idcard): bool|string
    {
        if (strlen($idcard) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (in_array(substr($idcard, 12, 3), ['996', '997', '998', '999'])) {
                $idcard = substr($idcard, 0, 6).'18'.substr($idcard, 6, 9);
            } else {
                $idcard = substr($idcard, 0, 6).'19'.substr($idcard, 6, 9);
            }
        }

        return $idcard.idcard_verify_number($idcard);
    }

    // 18位身份证校验码有效性检查
    function idcard_checksum18($idcard): bool
    {
        if (strlen($idcard) != 18) {
            return false;
        }
        $idcard_base = substr($idcard, 0, 17);
        if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }
}

if (! function_exists('detach_html')) {
    /**
     * 去除所有html标签,只保留纯文本
     */
    function detach_html($string): string
    {
        // 移除 BOM 字符（解决某些带 BOM 文件导致的异常问题）
        $string = preg_replace('/^\xEF\xBB\xBF/', '', $string);
        // 移除 <script> 和 <style> 标签及其内容
        $output = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $string);
        // 移除 HTML 注释，包括条件注释（如 [if IE]）
        $output = preg_replace('#<!--.*?-->#s', '', $output);
        // 移除内嵌 CSS 样式（如 style="color:red;"）
        $output = preg_replace('#\s*style=["\'][^"\']*["\']#i', '', $output);
        // 移除所有 HTML 标签，包括自定义标签
        $output = preg_replace('#<[^>]+>#', '', $output);
        // 移除多余的换行、制表符等空白符
        $output = preg_replace('/[\t\n\r]+/', ' ', $output);
        // 替换 HTML 实体为正常字符
        $output = html_entity_decode($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // 去除连续空白符
        $output = preg_replace('/\s{2,}/', ' ', $output);

        // 修整首尾空白
        return trim($output);
    }
}

if (! function_exists('str_rand')) {
    /**
     * 生成随机字符串
     *
     *
     * @DateTime 2017-06-28
     *
     * @param  int  $length  字符串长度
     * @param  string  $tack  附加值
     * @return string 字符串
     */
    function str_rand(int $length = 6, string $tack = ''): string
    {
        $chars = 'abcdefghijkmnpqrstuvwxyzACDEFGHIJKLMNOPQRSTUVWXYZ12345679'.$tack;
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $str;
    }
}

if (! function_exists('wx_decrypt_data')) {
    /**
     * 微信解密
     *
     *
     * @return array|mixed
     */
    function wx_decrypt_data($appId, $encryptedData, $iv, $sessionKey): mixed
    {
        // $appId = 'wxfd...9ce';
        if (strlen($sessionKey) != 24) {
            return [
                'code' => 500,
                'mag' => 'sessionKey 无效',
            ];
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return [
                'code' => 500,
                'mag' => 'iv 无效',
            ];
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);

        $dataObj = json_decode_plus(obj2Arr($result), true);
        if ($dataObj == null) {
            return [
                'code' => 500,
                'mag' => '解析失败',
            ];
        }
        if ($dataObj['watermark']['appid'] != $appId) {
            return [
                'code' => 500,
                'mag' => 'appid无效',
            ];
        }

        return $dataObj;
    }
}

if (! function_exists('img_to_base64')) {
    /**
     * 图片转 base64
     *
     *
     * @DateTime 2017-07-18
     *
     * @param    [type]       $image_file [description]
     * @return string [description]
     */
    function img_base64($image_file): string
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));

        return 'data:'.$image_info['mime'].';base64,'.chunk_split(base64_encode($image_data));
    }
}

if (! function_exists('base64_to_image')) {
    /**
     * base64图片转文件图片
     * base64_to_image($row['cover'],"./uploads/images")
     */
    function base64_to_image($base64_image_content, $path): bool|string
    {
        // 匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            $new_file = $path.'/'.date('Ymd', time()).'/';
            if (! file_exists($new_file)) {
                // 检查是否有该文件夹，如果没有就创建，并给予最高权限
                create_dir($new_file);
            }
            $new_file = $new_file.md5(time().mt_rand(1, 1000000)).".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return ltrim($new_file, '.');
            } else {
                return false;
            }
        }

        return false;
    }
}

if (! function_exists('is_json')) {
    /**
     * [is_json 判断json]
     *
     *
     * @DateTime 2018-12-27
     *
     * @param    [type]       $string [description]
     * @return bool [description]
     */
    function is_json($string): bool
    {
        try {
            $data = json_decode_plus($string, false);
            if ((! empty($data) && is_object($data)) || (is_array($data) && ! empty($data))) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }
}
if (! function_exists('is_xml')) {
    // 检查是否是有效的 XML 字符串
    function is_xml(string $string): bool
    {
        try {
            // 尝试加载字符串为 XML
            libxml_use_internal_errors(true); // 启用内部错误处理
            simplexml_load_string($string);

            // 如果发生解析错误，返回 false
            if (libxml_get_errors()) {
                libxml_clear_errors(); // 清除错误
                return false;
            }
            return true; // 如果没有错误，返回 true
        }catch (\Exception $e){
        }
        return false;
    }
}

if (! function_exists('get_raw_input')) {
    /**
     * 获取原始请求内容
     *
     * @param bool $returnOriginal 是否返回原始数据；默认为 true；
     *                             true:返回原始数据
     *                             false:返回解析后的数据；
     *
     * @param bool $getDataType 是否获取数据类型；默认为 false；
     *                          true:返回数据类型；
     *                          false:只返回请求数据；
     *
     * @return array|string|null
     */
    function get_raw_input(bool $returnOriginal = true, bool $getDataType = false): array|string|null
    {
        // 获取原始数据
        return \zxf\Http\Request::instance()->getRawInput($returnOriginal, $getDataType);
    }
}

if (! function_exists('parse_files')) {
    // 解析文件上传数据
    function parse_files(array $files): array
    {
        $parsedFiles = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                // 处理多个文件上传
                foreach ($file['name'] as $index => $filename) {
                    $parsedFiles[$key][] = [
                        'name'     => $filename,
                        'type'     => $file['type'][$index] ?? null,
                        'tmp_name' => $file['tmp_name'][$index] ?? null,
                        'error'    => $file['error'][$index] ?? null,
                        'size'     => $file['size'][$index] ?? null,
                    ];
                }
            } else {
                // 处理单个文件上传
                $parsedFiles[$key] = [
                    'name'     => $file['name'],
                    'type'     => $file['type'],
                    'tmp_name' => $file['tmp_name'],
                    'error'    => $file['error'],
                    'size'     => $file['size'],
                ];
            }
        }
        return $parsedFiles;
    }
}

if (! function_exists('get_full_path')) {
    /**
     * 根据相对路径获取绝对路径
     *
     * @param  string  $path  相对路径
     */
    function get_full_path($path): string
    {
        $info = pathinfo($path);

        return $_SERVER['DOCUMENT_ROOT'].'/'.$info['dirname'].'/'.$info['basename'];
    }
}

if (! function_exists('convert_underline')) {
    /**
     * 下划线转驼峰
     *
     *
     * @DateTime 2018-08-29
     *
     * @return array|string|null [type]       [description]
     */
    function convert_underline(string $str): array|string|null
    {
        return preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
    }
}

if (! function_exists('underline_convert')) {
    /**
     * 驼峰转下划线
     *
     *
     * @DateTime 2018-08-29
     *
     * @return string [description]
     */
    function underline_convert(string $str): string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }
}

if (! function_exists('check_pass_strength')) {
    /**
     * 验证等保测2级评密码强度
     * 验证密码强度是否符合 至少包含大小写字母、数字、特殊字符大于8个字符
     *
     *
     * @DateTime 2020-01-08
     *
     * @param  string  $password  [description]
     */
    function check_pass_strength(string $password = ''): bool
    {
        // 检测密码强度 至少包含大小写字母、数字、特殊字符至少3个组合大于8个字符
        $expression = '/^(?![A-Za-z]+$)(?![A-Z\\d]+$)(?![A-Z\\W]+$)(?![a-z\\d]+$)(?![a-z\\W]+$)(?![\\d\\W]+$)\\S{8,}$/';
        if (preg_match($expression, $password)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('buildRequestFormAndSend')) {
    /**
     * 构建form表单并提交数据
     * 满足提交大量表单会被数据长度等限制的问题
     * [header 携带大量数据请求的可行性方案]
     *
     * @param  string  $url  数据提交跳转到的URL
     * @param  array  $data  需要提交的数组,支持多维 (按照数组的键值对组装form表单数据)
     * @param  string  $method  提交方式 支持 post|get|put|delete
     * @return string 组装提交表单的HTML文本
     *
     * @throws Exception
     */
    function buildRequestFormAndSend(string $url, array $data = [], string $method = 'post'): string
    {
        $method = $method ? strtolower($method) : 'post';
        $methodIsMorph = in_array($method, ['put', 'delete']) ? strtoupper($method) : ''; // 变形
        $method = in_array($method, ['put', 'delete', 'post']) ? 'post' : 'get';

        $data = obj2Arr($data);
        $method = strtolower($method) == 'post' ? 'POST' : 'GET';
        $formId = 'requestForm_'.time().'_'.random_int(2383280, 14776335);
        $html = "<form id='".$formId."' action='".$url."' method='".$method."'>";
        $html .= ! empty($methodIsMorph) ? '<input type="hidden" name="_method" value="'.$methodIsMorph.'" />' : '';
        // 遍历子数组
        function traverseChildArr($arr, $namePrefix = ''): string
        {
            $arr = obj2Arr($arr);
            $htmlStr = '';
            foreach ($arr as $key => $item) {
                $name = empty($namePrefix) ? $key : $namePrefix.'['.$key.']';
                $htmlStr .= is_array($item) ? traverseChildArr($item, $name) : "<input type='hidden' name='".$name."' value='".$item."' />";
            }

            return $htmlStr;
        }

        $html .= traverseChildArr($data, '');
        $html .= "<input type='submit' value='确定' style='display:none;'></form>";
        $html .= "<script>document.forms['".$formId."'].submit();</script>";

        return $html;
    }
}

if (! function_exists('obj2Arr')) {
    /**
     * 对象转数组
     *
     *
     * @return array|mixed
     */
    function obj2Arr($array): mixed
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = obj2Arr($value);
            }
        }

        return $array;
    }
}

if (! function_exists('uuid')) {
    /**
     * 根据微秒时间和随机数生成 10位 或者 11位的 uuid
     *
     * @param  bool  $useUpper  是否生成大写标识字符
     *                          true  : [0~9 + A~Z(不包含O)] 生成10位长度的uuid;
     *                          false : [0~9 + a~z + A~Z]   生成11位长度的uuid;
     *
     * @throws Exception
     */
    function uuid(bool $useUpper = false): string
    {
        return \zxf\Tools\Str::uuid($useUpper);
    }
}

if (! function_exists('from60to10')) {
    /**
     * 60进制转10进制
     */
    function from60to10($str): string
    {
        // (去掉oO)
        $dict = '0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $len = strlen($str);
        $dec = 0;
        for ($i = 0; $i < $len; $i++) {
            // 找到对应字典的下标
            $pos = strpos($dict, $str[$i]);
            $dec += $pos * pow(60, $len - $i - 1);
        }

        return number_format($dec, 0, '', '');
    }
}

if (! function_exists('from10to60')) {
    /**
     * 10进制转60进制
     */
    function from10to60($dec): string
    {
        // (去掉oO,因为和0很像)
        $dict = '0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $result = '';
        do {
            $result = $dict[$dec % 60].$result;
            $dec = intval($dec / 60);
        } while ($dec != 0);

        return $result;
    }
}

if (! function_exists('dict_convert_ten')) {
    /**
     * 把其他进制的字符串转换为10进制（注：针对使用自定义字典的字符串转换，普通的进制转换可以使用 base_convert_any 函数）
     *
     * @param  string  $string  根据自定义字典 $dict 生成的字符串
     * @param  int  $fromBase  源进制
     *
     * @throws Exception
     */
    function dict_convert_ten(string $string = '', int $fromBase = 16): int|string
    {
        return base_convert_any($string, $fromBase, 10);
    }
}

if (! function_exists('base_convert_any')) {
    /**
     * 将任意进制的数值转换为另一个进制的数值,
     *      支持 2 到 62 进制之间的转换
     *      支持负数
     *
     * @param  string  $number  待转换数值，可以是整数或浮点数，支持负数，例如："123", "-456.789"
     * @param  int  $fromBase  源进制
     * @param  int  $toBase  目标进制
     * @return string|int 转换成功返回目标进制下的数值
     *
     * @throws Exception
     */
    function base_convert_any(string $number, int $fromBase = 10, int $toBase = 62): string|int
    {
        // 判断是否是小数
        $isDecimal = (str_contains($number, '.'));
        if ($isDecimal) {
            throw new \Exception('暂不支持小数转换');
        }
        // 处理负数符号
        $isNegative = ($number[0] === '-');
        if ($isNegative) {
            $number = substr($number, 1); // 去除负号
        }

        // 使用 base_convert 进行转换
        $convertedNumber = base_convert($number, $fromBase, $toBase);

        // 重新添加负数符号
        if ($isNegative) {
            $convertedNumber = '-'.$convertedNumber;
        }

        return $convertedNumber;
    }
}

if (! function_exists('download_url_file')) {
    /**
     * 下载url文件
     */
    #[NoReturn]
    function download_url_file($url = ''): void
    {
        $filename = ! empty($url) ? $url : (! empty($_GPC['url']) ? $_GPC['url'] : '');
        $title = substr($filename, strrpos($filename, '/') + 1);
        $file = fopen($filename, 'rb');
        header('Content-type:application/octet-stream');
        header('Accept-Ranges:bytes');
        header("Content-Disposition:attachment;filename=$title");
        while (! feof($file)) {
            echo fread($file, 8192);
            ob_flush();
            flush();
        }
        fclose($file);
        exit;
    }
}

if (! function_exists('str_en_code')) {
    /**
     * 字符串加密和解密
     *
     * @param  string  $string  字符串
     * @param  string  $operation  de(DECODE)表示解密，en(ENCODE)表示加密；
     * @param  int|string  $expiry  缓存生命周期 0表示永久缓存 默认99年
     *                              支持格式:
     *                              int 缓存多少秒，例如 90 表示缓存90秒，如果小于等于0，则用0替换
     *                              string: 时间字符串格式,例如:+1 day、2023-01-01 09:00:02 等 strtotime 支持的格式均可
     * @return false|string
     */
    function str_en_code(string $string, string $operation = 'en', int|string $expiry = 312206400, string $key = ''): bool|string
    {
        $operation = in_array($operation, ['de', 'DECODE']) ? 'DECODE' : 'ENCODE';
        // 转换字符串
        $string = $operation == 'DECODE' ? str_replace(['_'], ['/'], $string) : $string;
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5(! empty($key) ? $key : 'wei_si_fang');
        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        // 解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $expiry = (is_numeric($expiry) || empty($expiry)) ? time() + (int) $expiry : strtotime($expiry);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = [];
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            $result = $keyc.str_replace('=', '', base64_encode($result));

            // 转换字符串
            return str_replace(['/', '='], ['_', ''], $result);
        }
    }
}

if (! function_exists('get_protected_value')) {
    /**
     * 打印对象里面受保护属性的值
     */
    function get_protected_value($obj, $name): mixed
    {
        $array = (array) $obj;
        $prefix = chr(0).'*'.chr(0);

        return $array[$prefix.$name];
    }
}

if (! function_exists('set_protected_value')) {
    /**
     * 使用反射 修改对象里面受保护属性的值
     *
     *
     * @throws ReflectionException
     */
    function set_protected_value($obj, $filed, $value): void
    {
        $reflectionClass = new ReflectionClass($obj);
        try {
            $reflectionClass->setStaticPropertyValue($filed, $value);
        } catch (\Exception $err) {
            $reflectionProperty = $reflectionClass->getProperty($filed);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($obj, $value);
        }
    }
}

if (! function_exists('json_decode_plus')) {
    /**
     * json_decode 加强版， 主要是为了了处理 json 字符串中包含了 \\" 和 \\ 转义字符导致无法解析的问题
     *
     * @param  array|string|null  $jsonStr  json 字符串
     * @param  null  $assoc
     */
    function json_decode_plus(array|string|null $jsonStr, $assoc = null): mixed
    {
        if (empty($jsonStr) || is_array($jsonStr) || (json_last_error() !== JSON_ERROR_NONE)) {
            return $jsonStr;
        }
        try {
            $jsonStr = preg_replace('/\\"/', '"', $jsonStr);
            $jsonStr = str_replace('\\', '', $jsonStr);

            return json_decode($jsonStr, $assoc);
        } catch (\Exception $e) {
            return json_decode($jsonStr, $assoc);
        }
    }
}

if (! function_exists('is_mobile')) {
    /**
     * 判断当前浏览器是否为移动端
     */
    function is_mobile(): bool
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && (str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'vnd.wap.wml') || str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'text/vnd.wap.wml'))) {
            // 判断 HTTP_ACCEPT 是否包含 vnd.wap.wml 或 text/vnd.wap.wml 关键字
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|samsung|windows ce|windows phone|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('url_conversion')) {
    /**
     * 把 ./ 和 ../ 开头的资源地址转换为绝对地址
     *
     * @param  string  $string  需要转换的字符串
     * @param  string  $prefixString  拼接的前缀字符
     * @param  array  $linkAttr  需要转换的标签属性，例如：href、src、durl
     */
    function url_conversion(string $string = '', string $prefixString = '', array $linkAttr = ['href', 'src']): string
    {
        if (empty($string) || empty($prefixString)) {
            return $string;
        }
        // 判断$string是否是 / 、./ 或者 ../ 开头的url字符串
        if (mb_substr($string, 0, 1, 'utf-8') == '/' || mb_substr($string, 0, 2, 'utf-8') == './' || mb_substr($string, 0, 3, 'utf-8') == '../') {
            return url_conversion_to_prefix_path($string, $prefixString);
        }
        $linkAttrString = implode('|', $linkAttr); // 数组转为字符串 用 (竖线)`|` 分割，例如：href|src|durl
        // 正则查找 $linkAttr 属性中 以 ./、../、/ 和文件夹名称开头的图片或超链接的相对路径 URL 地址字符串,要求src、href等前面至少带一个空格，避免操作 src 和 oldsrc 都识别到src的情况
        // $pattern = '/\s+(href|src)\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        $pattern = '/\s+('.$linkAttrString.')\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        preg_match_all($pattern, $string, $matches);

        $relativeURLs = $matches[0];
        $originalPath = []; // 原始的相对路径数组
        $replacePath = []; // 替换成的前缀路径数组
        $plusReplacePath = []; // 加强版替换路径数组
        foreach ($relativeURLs as $findStr) {
            // 删除 $findStr 字符串中的 href= 或者 src= durl= 字符串
            $findStr = preg_replace('/\s+('.$linkAttrString.')\s*=\s*["\']/i', '', $findStr);
            $originalPath[] = $findStr;
            $replacePath[] = url_conversion_to_prefix_path($findStr, $prefixString);
        }
        if (! empty($originalPath) && ! empty($replacePath)) {
            // 批量替换地址;直接在此处替换会导致 出现相同的'link'字符串时候会被替换多次，导致出现错误的结果
            // $string = str_replace($originalPath, $replacePath, $string);

            // 加强版开始开始表演：找出 'link' 相关字符串的前缀(例如src、href等)最为批量替换的前缀，防止被多次替换
            // 强化前缀字符串
            $strengthenAttr = $matches[1];
            foreach ($originalPath as $index => $item) {
                // 判断最后一个引号是单引号还是双引号
                $lastQuotationMark = substr($relativeURLs[$index], -1);
                // 把替换结果拼上 $linkAttr 对应的前缀，例如 ` src="` 或者 ` href="等
                $plusReplacePath[$index] = ' '.$strengthenAttr[$index].'='.$lastQuotationMark.$replacePath[$index];
            }
            // 批量替换地址
            $string = str_replace($relativeURLs, $plusReplacePath, $string);
        }

        return $string;
    }
}

if (! function_exists('url_conversion_to_prefix_path')) {
    /**
     * 把 $url 中的 相对路径 转换为$prefix前缀路径, 建议调用 url_conversion() 方法
     */
    function url_conversion_to_prefix_path(string $url = '', string $prefix = ''): string
    {
        if (empty($url) || empty($prefix)) {
            return $url;
        }
        if (mb_substr($url, 0, 4, 'utf-8') != 'http') {
            // 用 / 把 $prefix  拆分为数组
            $domain_prefix_arr = explode('/', trim($prefix, '/'));
            if (mb_substr($url, 0, 1, 'utf-8') == '/') {
                // 处理 / 开头的路径
                if (mb_substr($prefix, 0, 4, 'utf-8') == 'http') {
                    // 解析URL
                    $urlInfo = parse_url($prefix);
                    $domain = $urlInfo['scheme'].'://'.$urlInfo['host'].(! empty($urlInfo['port']) ? ':'.$urlInfo['port'] : '');

                    return $domain.$url;
                } else {
                    return $domain_prefix_arr[0].$url;
                }
            }
            // 查找 $url 字符串中出现了几次 ../ ,例如：../../ ,不要查找 ./ ，因为 ./ 表示0次
            $count = mb_substr_count($url, '../', 'utf-8');
            // 从 $domain_prefix_arr 中删除 $count 个元素
            $count > 0 && array_splice($domain_prefix_arr, -$count);
            // 用 / 把 $domain_prefix_arr  拼接为字符串
            $prefix = implode('/', $domain_prefix_arr);
            // 去掉 $url 字符串中的 ../ 和 ./
            $url = str_replace(['../', './'], '', $url);
            $url = rtrim($prefix, '/').'/'.ltrim($url, '/');
        }

        return $url;
    }
}

if (! function_exists('aes_encrypt')) {
    /**
     * AES加密
     *
     * @param  mixed  $data  需要加密的数据 string|array
     * @param  string  $key  // 32字节的密钥
     * @param  $iv  // 16字节的向量
     * @return string
     */
    function aes_encrypt($data, string $key = 'WEISIFANG_AES_KEY_01234567ABCDEF', string $iv = 'WEISIFANG_AES_IV')
    {
        $data = is_string($data) ? $data : json_encode($data);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($encrypted);
    }
}

if (! function_exists('aes_decrypt')) {
    /**
     * AES解密
     *
     * @param  string  $data  需要解密的数据 string
     * @param  string  $key  // 32字节的密钥
     * @param  $iv  // 16字节的向量
     * @return false|string
     */
    function aes_decrypt(string $data, string $key = 'WEISIFANG_AES_KEY_01234567ABCDEF', string $iv = 'WEISIFANG_AES_IV')
    {
        try {
            $data = base64_decode($data);

            return openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            // return json_decode($res, true);
        } catch (\Exception $err) {
            return false;
        }
    }
}

if (! function_exists('array_keys_search')) {
    /**
     * 从二维数组中搜索指定的键名，返回键名对应的值
     *
     * @param  array  $array  二维数组
     * @param  array  $keys  键名数组
     * @param  bool  $onlyExists  是否只返回存在的键名对应的值
     */
    function array_keys_search(array $array = [], array $keys = [], bool $onlyExists = false): mixed
    {
        $result = [];
        if (empty($array) || empty($keys)) {
            return $result;
        }
        if ($onlyExists) {
            // 方式一：只返回存在的键名对应的值
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $result[$key] = $value;
                }
            }
        } else {
            // 方式二：返回所有指定键名对应的值，不存在的键名返回null
            foreach ($keys as $key) {
                $result[$key] = $array[$key] ?? null;
            }
        }

        return $result;
    }
}

if (! function_exists('is_qq_browser')) {
    /**
     * 判断来源是否为QQ浏览器
     *
     * @return bool true|false
     */
    function is_qq_browser(): bool
    {
        // 获取所有的header信息
        $headers = getallheaders();
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return str_contains($http_user_agent, 'MQQBrowser') || str_contains($http_user_agent, 'QQ') || isset($headers['X-QQ-From']);
    }
}

if (! function_exists('is_wechat_browser')) {
    /**
     * 判断来源是否为微信浏览器
     *
     * @return bool true|false
     */
    function is_wechat_browser(): bool
    {
        // 获取所有的header信息
        $headers = getallheaders();
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return str_contains($http_user_agent, 'MicroMessenger') || str_contains($http_user_agent, 'WeChat') || isset($headers['X-Weixin-From']);
    }
}

if (! function_exists('is_weibo_browser')) {
    /**
     * 判断来源是否为微博浏览器
     *
     * @return bool true|false
     */
    function is_weibo_browser(): bool
    {
        // 获取所有的header信息
        $headers = getallheaders();
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return str_contains($http_user_agent, 'Weibo') || isset($headers['X-Weibo-From']);
    }
}

if (! function_exists('is_alipay_browser')) {
    /**
     * 判断来源是否为支付宝浏览器
     *
     * @return bool true|false
     */
    function is_alipay_browser(): bool
    {
        // 获取所有的header信息
        $headers = getallheaders();
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return str_contains($http_user_agent, 'AlipayClient') || str_contains($http_user_agent, 'Alipay') || isset($headers['X-Weibo-From']);
    }
}

if (! function_exists('json_string_to_array')) {
    // 判断一个字符串是否为json格式,并返回json数组
    function json_string_to_array($string)
    {
        if (is_array($string)) {
            return $string;
        }
        $data = json_decode($string, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $data;
        }

        return $string;
    }
}

if (! function_exists('json_array_to_string')) {
    // 判断json格式转换为字符串
    function json_array_to_string($array)
    {
        return is_array($array) ? json_encode($array) : $array;
    }
}

if (! function_exists('before_calling_methods')) {
    /**
     * 核心功能
     *  class 类在调用方法之前，先执行指定的初始化方法$method,并解析和传入$method方法中的依赖关系参数
     *
     * 适用场景：
     *   在路由调用控制器方法之前，先执行 initialize 方法，并传入依赖关系参数，需要在构造函数中调用本方法
     *      eg:
     *          class WebBaseController
     *              public function __construct(Request $request)
     *              {
     *                  parent::__construct($request);
     *                  // 路由执行被调用方法之前，先执行 initialize 方法
     *                  before_calling_methods($this, 'initialize');
     *                  // 路由执行被调用方法之前，先执行 test 方法 ,且传入参数
     *                  before_calling_methods($this, 'test',[ $name='张三',$age = 18]);
     *               }
     *
     *               public function initialize(Request $request,...其他的自定义依赖注入)
     *
     *               public function test(...自定义依赖注入或者不传入参数)
     *          }
     *
     * @param  object  $class  类对象 eg: $this、MyClass、MyController
     * @param  string  $method  方法名称 默认为 initialize
     * @param  array  ...$args  可以给被调用函数传参； eg:[ $name='张三',$age = 18], 数组中参数下标N对应被调用函数的第N个参数
     *
     * @throws Exception
     */
    function before_calling_methods(object $class, string $method = 'initialize', array ...$args): void
    {
        try {
            // 判断 $class 是不是一个class 或者 $method 是不是一个方法
            if (! is_object($class) || ! method_exists($class, $method)) {
                return;
            }
            // 1、获取 $class 中 $method 方法的依赖关系(参数列表)

            // 使用反射获取方法信息
            $reflectionMethod = new \ReflectionMethod($class, $method);

            // 获取$args的参数
            $paramsArgs = ! empty($args) ? reset($args) : [];
            $index = -1;

            // 获取参数类型名，形成数组返回
            $dependencies = array_map(function ($parameter) use (&$index, $paramsArgs) {
                $index++;
                $paramName = $parameter->getType()?->getName(); // 参数类型名, eg: int、string、array
                // 类 或者 函数
                if (! empty($paramName) && (class_exists($paramName) || is_callable($paramName))) {
                    return $paramName;
                }
                $argIndex = $index + 1;
                // 有传入值就使用传入值
                if (! empty($paramsArgs[$index])) {
                    // 没有定义参数类型 || 参数类型不匹配
                    if (empty($paramName) || (call_user_func('is_'.$paramName, $paramsArgs[$index]))) {
                        return $paramsArgs[$index];
                    }
                    throw new \Exception("第{$argIndex}个参数的类型不是指定的「{$paramName}」类型");
                }
                // 检查是否有默认值
                if ($parameter->isDefaultValueAvailable()) {
                    // 有默认值直接返回默认值
                    return $parameter->getDefaultValue();
                }
                // 没有默认参数的普通参数
                throw new \Exception("第{$argIndex}个参数「\${$parameter->getName()}」不能为空");
            }, $reflectionMethod->getParameters());

            // 2、 解析依赖注入对象
            $resolvedDependencies = array_map(function ($parameter) {
                // 如果参数是类名，则尝试解析依赖注入
                if (is_string($parameter) && class_exists($parameter)) {
                    // 如果是 Laravel 则使用 app 函数实例化，否则直接 new 一个类
                    return (function_exists('is_laravel') && is_laravel()) ? app($parameter) : new $parameter;
                }

                return $parameter;
            }, $dependencies);

            // 3、 通过反射 $method 方法并传入解析后的依赖注入对象或普通参数
            $reflectionMethod->invokeArgs($class, $resolvedDependencies);
        } catch (\ReflectionException $e) {
            return;
        }
    }
}

if (! function_exists('class_basename')) {
    /**
     * 获取类名
     *
     * @param  string  $className  类名 eg: \Test\Abc, get_class($this) 等
     */
    function class_basename(string $className): string
    {
        // 使用 DIRECTORY_SEPARATOR 确保跨平台兼容性
        $fullClassName = str_replace('/', '\\', $className); // 确保类名中的分隔符统一为反斜线

        // 使用 basename 函数提取路径的最后一部分，相当于提取类名
        return basename($fullClassName, '.php'); // 如果类名字符串以 ".php" 结尾，这会移除它
    }
}

if (! function_exists('relative_path')) {
    /**
     * 获取文件相对于项目根目录的相对路径
     */
    function relative_path(string $filePath): string
    {
        // 相对路径
        $dir = dirname(__DIR__);
        $prefixPath = substr($dir, 0, strpos($dir, 'vendor'));
        $realPath = realpath($filePath); // 获取真实路径

        return str_starts_with($realPath, $prefixPath) ? ltrim(substr($realPath, strlen($prefixPath)), 'public/') : $realPath;
    }
}

if (! function_exists('stream_output')) {

    /**
     * 数据流 方式操作数据，不用等待操作结束才打印数据
     *
     * @param  Closure  $callback  ($next)
     *                             $next() 执行下一个回调函数
     *
     * @throws Exception
     *
     * @example     stream_output(function ($next){
     *                  // 打印或处理数据
     *                  $next();
     *                  $next('这是输出的string');
     *                  $next->info('这是输出的string');
     *                  $next->error('这是输出的string');
     *                  $next->warning('这是输出的string');
     *                  $next->success('这是输出的string');
     *              });
     */
    function stream_output(\Closure $callback): void
    {
        // 防止 CLI 进程被 kill
        if (PHP_SAPI === 'cli') {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, function () {
                echo "进程被终止。\n";
                exit;
            });
        } else {
            if (headers_sent()) {
                throw new \Exception('在调用「'.__FUNCTION__.'」函数前，已经发送了HTTP的响应，请删除之前的HTTP响应或者在发送HTTP响应之前添加“ob_start();”代码才能继续运行');
            }

            // 取消 PHP 超时限制
            ini_set('max_execution_time', 0);
            set_time_limit(0);
            // 允许即使用户断开连接，PHP 仍然运行
            ignore_user_abort(true);

            // 重新设置 Header
            // 如果使用的是 Apache 或 Nginx，设置适当的头部信息以避免服务器端缓冲
            header('Content-Type: text/plain; charset=UTF-8');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // 禁用 nginx 的输出缓冲
        }

        // 关闭 PHP 的输出缓冲
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(true); // 每次输出都自动刷新

        // 方式一：最简实现
        // $next = function (string ...$outputString) {
        //    // 换行符号
        //    $break = (PHP_SAPI === 'cli') ? PHP_EOL : '<br>';
        //    foreach ($outputString as $output) {
        //        echo $output . $break;
        //    }
        //    // 强制刷新输出缓冲区
        //    flush();
        // };

        // 方式二：支持通过 $next->info('hello','hello 2') 等样式调用
        $next = new class
        {
            private bool $isCli = false;

            public function __construct()
            {
                $this->isCli = PHP_SAPI === 'cli';
            }

            // 支持直接调用 $next('hello')
            public function __invoke(string ...$string): void
            {
                foreach ($string as $text) {
                    $this->isCli && $this->cliPrintColorText($text, "\033[37m", "\033[0m", true);
                    $this->isCli || $this->browserPrintColorText($text, $color = '#000', true);
                }
                // 强制刷新输出缓冲区
                flush();
            }

            // 支持通过 $next->info('hello','hello 2') 调用
            public function __call(string $name, array $args)
            {
                $cliColor = match ($name) {
                    'info' => "\033[37m", // \033[37m:亮灰色; \033[90m:亮黑色(灰色)
                    'error' => "\033[31m", // 红色
                    'warning' => "\033[33m", // 黄色
                    'success' => "\033[32m", // 绿色
                    default => "\033[90m", // \033[37m:亮灰色; \033[90m:亮黑色(灰色)
                };
                $color = match ($name) {
                    'info' => '#000', // 黑色
                    'error' => '#FF3300', // 红色
                    'warning' => '#FFCC00', // 黄色
                    'success' => '#009900', // 绿色
                    default => '#3366FF', // 蓝色
                };

                foreach ($args as $text) {
                    $this->isCli && $this->cliPrintColorText($text, $cliColor, "\033[0m", true);
                    $this->isCli || $this->browserPrintColorText($text, $color, true);
                }
                // 强制刷新输出缓冲区
                flush();
            }

            /**
             * cli 打印带有颜色和样式的文本。
             *
             * @param  string  $text  要打印的文本。
             * @param  string  $color  颜色代码。
             * @param  string  $style  样式代码，默认为重置样式。
             * @param  bool  $needWrap  是否需要换行
             */
            public function cliPrintColorText(string $text, string $color, string $style = "\033[0m", bool $needWrap = false): void
            {
                echo $style.$color.$text."\033[0m";
                if ($needWrap) {
                    echo "\n";
                }
            }

            /**
             * browser 打印带有颜色的文本。
             *
             * @param  string  $text  要打印的文本。
             * @param  string  $color  颜色代码。
             * @param  bool  $needWrap  是否需要换行
             */
            public function browserPrintColorText(string $text, string $color = '#c0c0c0', bool $needWrap = false): void
            {
                echo '<span style="color: '.$color.';">'.$text.'</span>';
                if ($needWrap) {
                    echo '<br>';
                }
            }
        };
        $callback && $callback($next);
        // 强制刷新输出缓冲区
        flush();
    }
}

if (! function_exists('escape')) {
    /**
     * 把字符串转义成 带u格式的 ASCII 字符
     *
     * @param  string  $str  需要转换的字符串，eg:威舍,
     * @return string eg:%u5A01%u820D%2C
     */
    function escape(string $str): string
    {
        return implode('', array_map(function ($char) {
            $ascii = ord($char);
            if ($ascii <= 0x7F) {
                return rawurlencode($char);
            } else {
                $utf16 = mb_convert_encoding($char, 'UTF-16BE', 'UTF-8');

                return '%u'.strtoupper(bin2hex($utf16));
            }
        }, preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY)));
    }
}

if (! function_exists('is_resource_file')) {
    /**
     * 判断是否是资源文件[文件后缀判断]
     *
     * @param  bool|array  $simpleOrCustomExt  仅判断简单的几种资源文件
     *                                         true(默认): 仅判断简单的几种资源文件
     *                                         false: 会判断大部分的资源文件
     *                                         array: 仅判断自定义的这些后缀
     */
    function is_resource_file(string $url, bool|array $simpleOrCustomExt = true): bool
    {
        // 解析 URL
        $path = parse_url($url, PHP_URL_PATH);
        // 获取文件扩展名
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        // bool: 使用预定义的后缀和特殊规则进行判断
        if (is_bool($simpleOrCustomExt)) {
            // 是否简单判断
            $resourceExtList = $simpleOrCustomExt
                ? ['js', 'css', 'ico', 'ttf', 'jpg', 'jpeg', 'png', 'webp']
                : [
                    'js', 'css', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'ico', 'webp', 'ttf', 'woff', 'woff2',
                    'eot', 'otf', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'avi', 'mpg', 'mpeg', 'rm', 'rmvb', 'flv',
                    'swf', 'mkv', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip',
                    'rar', '7z', 'tar', 'gz', 'bz2', 'tgz', 'tbz', 'tbz2', 'tb2', 't7z', 'jar', 'war', 'ear', 'zipx',
                    'apk', 'ipa', 'exe', 'dmg', 'pkg', 'deb', 'rpm', 'msi', 'md', 'txt', 'log',
                ];
            if (! empty($ext)) {
                // 检查扩展名是否属于资源文件类型
                return in_array(strtolower($ext), $resourceExtList);
            }

            // 或者一些特殊路由前缀资源：captcha/: 验证码；tn_code/: 滑动验证码
            return str_starts_with(trim($path, '/'), 'captcha/') || str_starts_with(trim($path, '/'), 'tn_code/');
        }

        // array: 全部采用自定义传入的扩展名进行判断
        // 传值不为空?检查扩展名是否属于资源文件类型:false
        return ! empty($ext) && in_array(strtolower($ext), $simpleOrCustomExt);
    }
}

if (! function_exists('is_string_value_array')) {
    /**
     * 检查是否为['字符串键名'=>'不是数组也不是对象格式类型的值']格式的数组
     *      eg:['name'=>'foo']:true
     *         ['name'=>['foo']]:false
     *         [['name','foo']]:false
     *         ['name'=>new stdClass()]:false
     *
     * @param array $array
     *
     * @return bool
     */
    function is_string_value_array(array $array): bool
    {
        return ! array_is_list($array) && array_reduce($array, fn ($carry, $value) => $carry && is_scalar($value), true);
    }
}

if (! function_exists('power_tower')) {
    /**
     * 幂塔（Power tower）,也称为重幂 或者 超幂(a↑↑b) (共b层)
     * 形如 ((2²)²)² 表示为 2^2^2^2 也记为 ⁴2 表示 或者2↑↑4 (共4层)
     * 的表达式被称为 指数塔（Exponential tower） 或 幂塔
     *
     * @param string $a 底数
     * @param int $b 幂塔层数
     *
     * @return string
     */
    function power_tower(string $a, int $b): string {
        return \zxf\Tools\BigNumberCalculator::tetration($a, $b);
    }
}
