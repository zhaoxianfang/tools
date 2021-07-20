<?php

/**
 * 常用的一些函数归纳
 */

function test_die()
{
    die('this is a test fun');
}

if (!function_exists('zxf_substr')) {
    /**
     * 字符串截取
     */
    function zxf_substr($string, $start = 0, $length = 5)
    {
        $string = str_ireplace(' ', '', $string); // 去除空格
        if (function_exists('mb_substr')) {
            $newstr = mb_substr($string, $start, $length, "UTF-8");
        } elseif (function_exists('iconv_substr')) {
            $newstr = iconv_substr($string, $start, $length, "UTF-8");
        } else {
            for ($i = 0; $i < $length; $i++) {
                $tempstring = substr($string, $start, 1);
                if (ord($tempstring) > 127) {
                    $i++;
                    if ($i < $length) {
                        $newstring[] = substr($string, $start, 3);
                        $string      = substr($string, 3);
                    }
                } else {
                    $newstring[] = substr($string, $start, 1);
                    $string      = substr($string, 1);
                }
            }
            $newstr = join($newstring);
        }
        return $newstr;
    }
}

if (!function_exists('check_file_exists')) {
    /**
     * 判断远程资源是否存在
     * @Author   ZhaoXianFang
     * @DateTime 2019-06-26
     * @param    [type]       $url [description]
     * @return   [type]            [description]
     */
    function check_file_exists($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true); // 不取回数据
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        $result = curl_exec($curl); // 发送请求

        $found = false; // 如果请求没有发送失败
        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // 再检查http响应码是否为200
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    }
}

if (!function_exists('default_img')) {
    /**
     * 判断图片是否存在，如果不存在则使用默认图 [若使用第三个参数，则用第三个参数替换第二个参数里面的固定字符串 __str__ ]
     * $imgPath 展示的图片地址
     * $defaultImgOrReplaceStr :1、如果 $imgPath 不存在且 $replaceStr 为空时候表示 默认图片地址; 2、如果 $imgPath 不存在且 $replaceStr 不为空则用 $replaceStr 替换  $defaultImgOrReplaceStr 中的 固定字符串 __str__
     */
    function default_img($imgPath = '', $defaultImgOrReplaceStr = '', $replaceStr = '')
    {
        if (substr($imgPath, 0, 4) == 'http' && check_file_exists($imgPath)) {
            return $imgPath;
        }
        $imgPath = substr($imgPath, 0, 1) == '/' ? '.' . $imgPath : $imgPath;
        return is_file($imgPath) ? ltrim($imgPath, '.') : str_ireplace('__str__', $replaceStr, $defaultImgOrReplaceStr);
    }
}
if (!function_exists('remove_str_emoji')) {
    // 移除字符串中的 emoji 表情
    function remove_str_emoji($str)
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

if (!function_exists('check_str_exists_emoji')) {
    // 判断字符串中是否含有 emoji 表情
    function check_str_exists_emoji($str)
    {
        $mbLen  = mb_strlen($str);
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

if (!function_exists('is_crawler')) {
    /**
     * [isCrawler 检测是否为爬虫]
     * @Author   ZhaoXianFang
     * @DateTime 2019-12-24
     * @param    boolean      $returnName [是否返回爬虫名称]
     * @return   boolean                  [description]
     */
    function is_crawler($returnName = false)
    {
        $agent = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        if (!empty($agent)) {
            $spiderSite = array(
                "TencentTraveler",
                "Baiduspider+",
                "Baiduspider",
                "BaiduGame",
                "Googlebot",
                "msnbot",
                "Sosospider+",
                "Sosoimagespider+",
                "Sogou web spider",
                "ia_archiver",
                "Yahoo! Slurp",
                "YoudaoBot",
                "Yahoo Slurp",
                "Yahoo! Slurp China",
                "MSNBot",
                "Java (Often spam bot)",
                "BaiDuSpider",
                "Voila",
                "Yandex bot",
                "BSpider",
                "twiceler",
                "Sogou Spider",
                "Speedy Spider",
                "Google AdSense",
                "Heritrix",
                "Python-urllib",
                "Alexa (IA Archiver)",
                "Ask",
                "Exabot",
                "Custo",
                "OutfoxBot/YodaoBot",
                "yacy",
                "SurveyBot",
                "legs",
                "lwp-trivial",
                "Nutch",
                "StackRambler",
                "The web archive (IA Archiver)",
                "Perl tool",
                "MJ12bot",
                "Netcraft",
                "MSIECrawler",
                "WGet tools",
                "larbin",
                "Fish search",
                "360Spider",
            );
            foreach ($spiderSite as $val) {
                $str = strtolower($val);
                if (strpos($agent, $str) !== false) {
                    return $returnName ? $val : true;
                }
            }
        } else {
            return $returnName ? "" : false;
        }
    }
}

if (!function_exists('img_to_gray')) {
    /**
     * [img_to_gray 把彩色图片转换为灰度图片,支持透明色]
     * @Author   ZhaoXianFang
     * @DateTime 2019-06-24
     * @param    string       $imgFile      [源图片地址]
     * @param    string       $saveFile     [生成目标地址,为空时直接输出到浏览器]
     * @return   bool                       [true:成功；false:失败]
     */
    function img_to_gray($imgFile = '', $saveFile = '')
    {
        if (!$imgFile) {
            return false;
        }
        $imgInfo = pathinfo($imgFile);
        switch ($imgInfo['extension']) {
            //图片后缀
            case 'png':$block = imagecreatefrompng($imgFile); //从 PNG 文件或 URL 新建一图像
                break;
            case 'jpg':
                $block = imagecreatefromjpeg($imgFile); //从 JPEG 文件或 URL 新建一图像
                break;
            default:return false;
                break;
        }
        $color = imagecolorallocatealpha($block, 0, 0, 0, 127); //拾取一个完全透明的颜色
        imagealphablending($block, false); //关闭混合模式，以便透明颜色能覆盖原画布
        //生成灰度图
        if (!$block || !imagefilter($block, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        // imagefilter($block, IMG_FILTER_BRIGHTNESS, -35);//亮度降低35
        imagefill($block, 0, 0, $color); //填充
        imagesavealpha($block, true); //设置保存PNG时保留透明通道信息
        //图片后缀 生成图片
        switch ($imgInfo['extension']) {
            case 'png':
                if ($saveFile) {
                    imagepng($block, $saveFile); //生成图片
                } else {
                    header('Content-type: image/png');
                    imagepng($block); //生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            case 'jpg':
                if ($saveFile) {
                    imagejpeg($block, $saveFile); //生成图片
                } else {
                    header('Content-Type: image/jpeg');
                    imagejpeg($block); //生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            default:return false;
                break;
        }
        return true;
    }
}

if (!function_exists('truncate')) {
    /**
     * 文章去去除标签截取文字
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-12
     * @param    [type]       $string [被截取字符串]
     * @param    integer      $length [长度]
     * @param    boolean      $append [是否加...]
     * @return   [type]               [description]
     */
    function truncate($string, $length = 150, $append = true)
    {
        $string    = html_entity_decode($string);
        $string    = trim(strip_tags($string, '<em>'));
        $strlength = strlen($string);
        if ($length == 0 || $length >= $strlength) {
            return $string;
        } elseif ($length < 0) {
            $length = $strlength + $length;
            if ($length < 0) {
                $length = $strlength;
            }
        }
        if (function_exists('mb_substr')) {
            $newstr = mb_substr($string, 0, $length, "UTF-8");
        } elseif (function_exists('iconv_substr')) {
            $newstr = iconv_substr($string, 0, $length, "UTF-8");
        } else {
            for ($i = 0; $i < $length; $i++) {
                $tempstring = substr($string, 0, 1);
                if (ord($tempstring) > 127) {
                    $i++;
                    if ($i < $length) {
                        $newstring[] = substr($string, 0, 3);
                        $string      = substr($string, 3);
                    }
                } else {
                    $newstring[] = substr($string, 0, 1);
                    $string      = substr($string, 1);
                }
            }
            $newstr = join($newstring);
        }
        if ($append && $string != $newstr) {
            $newstr .= '...';
        }
        return $newstr;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('deldir')) {

    /**
     * 删除文件夹
     * @param string $dir 目录
     * @return boolean
     */
    function deldir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

}

if (!function_exists('create_folders')) {

    /**
     * 递归创建目录
     * @param string $dir 目录
     * @return boolean
     */
    function create_folders($dir)
    {
        return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
    }

}

if (!function_exists('getfilesize')) {

    /**
     * 获取文件的大小
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    //获取文件的大小
    function getfilesize($file, $DataDir)
    {
        $perms = stat($DataDir . $file);
        $size  = $perms['size'];
        // 单位自动转换函数
        $kb = 1024; // Kilobyte
        $mb = 1024 * $kb; // Megabyte
        $gb = 1024 * $mb; // Gigabyte
        $tb = 1024 * $gb; // Terabyte
        if ($size < $kb) {
            return $size . " B";
        } else if ($size < $mb) {
            return round($size / $kb, 2) . " KB";
        } else if ($size < $gb) {
            return round($size / $mb, 2) . " MB";
        } else if ($size < $tb) {
            return round($size / $gb, 2) . " GB";
        } else {
            return round($size / $tb, 2) . " TB";
        }
    }

}

if (!function_exists('response_and_continue')) {
    /**
     * @Author   ZhaoXianFang
     * @DateTime 2019-01-07
     * @demo 案例：先以json格式返回$data，然后在后台执行 $this->pushSuggestToJyblSys(array('suggId' => $id))
     * response_and_continue($data, array($this, "pushSuggestToJyblSys"), array('suggId' => $id));
     */
    function response_and_continue($responseDara, $backendFun, $backendFunArgs = array(), $setTimeLimit = 0, $completeFun, $completeFunArgs = array())
    {
        ignore_user_abort(true);
        set_time_limit($setTimeLimit);
        ob_end_clean();
        ob_start();
        //Windows服务器
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo str_repeat(" ", 4096);
        }
        //返回结果给ajax
        echo json_encode($responseDara);
        $size = ob_get_length();
        header("Content-Length: $size");
        header('Connection: close');
        header("HTTP/1.1 200 OK");
        header("Content-Encoding: none");
        header("Content-Type: application/json;charset=utf-8");
        ob_end_flush();
        if (ob_get_length()) {
            ob_flush();
        }
        flush();
        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request();
        }
        sleep(2);
        ignore_user_abort(true);
        set_time_limit($setTimeLimit);
        if (!empty($backendFun)) {
            $call = call_user_func_array($backendFun, $backendFunArgs);
            if (!empty($completeFun)) {
                call_user_func_array($completeFun, $completeFunArgs);
            }
        }
    }
}

if (!function_exists('num_to_zhcn')) {
    /**
     * 数字转换为中文
     * @Author   ZhaoXianFang
     * @param  string|integer|float  $num  目标数字
     * @param  integer $mode 模式[true:金额（默认）,false:普通数字表示]
     * @param  boolean $sim 使用小写（默认）
     * @return string
     */
    function num_to_zhcn($num, $mode = true, $sim = true)
    {
        if (!is_numeric($num)) {
            return '含有非数字非小数点字符！';
        }
        $char = $sim ? array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九')
        : array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        $unit = $sim ? array('', '十', '百', '千', '', '万', '亿', '兆')
        : array('', '拾', '佰', '仟', '', '萬', '億', '兆');
        // $retval = $mode ? '元' : '点';
        $retval = $mode ? '' : '点';
        //小数部分
        if (strpos($num, '.')) {
            list($num, $dec) = explode('.', $num);
            $dec             = strval(round($dec, 2));
            if ($mode) {
                $retval .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";
            } else {
                for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                    $retval .= $char[$dec[$i]];
                }
            }
        }
        //整数部分
        $str = $mode ? strrev(intval($num)) : strrev($num);
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            $out[$i] = $char[$str[$i]];
            if ($mode) {
                $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
                if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                    $out[$i] = '';
                }
                if ($i % 4 == 0) {
                    $out[$i] .= $unit[4 + floor($i / 4)];
                }
            }
        }
        if (count($out) == 2 && $out['1'] == '一十') {
            $out['1'] = '十';
            if ($out['0'] == '零') {
                unset($out['0']);
            }
        }
        $num_val = array_reverse($out);
        $retval  = join('', $num_val) . $retval;
        return $retval;
    }
}

if (!function_exists('object_to_array')) {
    //对象转数组
    function object_to_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_to_array($value);
            }
        }
        return $array;
    }
}

if (!function_exists('array_to_tree')) {
    //二维数组转树tree型结构
    function array_to_tree($items, $pid = 'pid', $id = 'id', $child = 'childlist')
    {
        $tree = array(); //格式化好的树
        foreach ($items as $item) {
            if (isset($items[$item[$pid]])) {
                $items[$item[$pid]][$child][] = &$items[$item[$id]];
            } else {
                $tree[] = &$items[$item[$id]];
            }
        }
        return $tree;
    }
}
if (!function_exists('show_img')) {
    /*
     * 页面直接输出图片
     */
    function show_img($imgFile = '')
    {
        header('Content-type:image/png');
        echo file_get_contents($imgFile);
        die;
    }
}
if (!function_exists('string_to_utf8')) {
    /*
     * 字符串自动转utf8编码
     */
    function string_to_utf8($str = '')
    {
        return mb_convert_encoding($str, "UTF-8", "auto");
    }
}
if (!function_exists('string_to_gbk')) {
    /*
     * 字符串自动转gbk编码
     */
    function string_to_gbk($str = '')
    {
        return mb_convert_encoding($str, "GBK", "auto");
    }
}
if (!function_exists('show_json')) {
    /*
     * 对json数据格式化输入展示 [转化为json格式，并格式化样式]
     */
    function show_json($array = [])
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

if (!function_exists('get_laravel_route')) {
    /**
     * 获取 laravel 模块 控制器 方法名
     */
    function get_laravel_route()
    {
        try {
            list($class, $method) = explode('@', request()->route()->getActionName());

            # 模块名
            $modules = str_replace(
                '\\',
                '.',
                str_replace(
                    'App\\Http\\Controllers\\',
                    '',
                    trim(
                        implode('\\', array_slice(explode('\\', $class), 0, -1)),
                        '\\'
                    )
                )
            );

            # 控制器名称
            $controller = str_replace(
                'Controller',
                '',
                substr(strrchr($class, '\\'), 1)
            );
            # 方法名
            // $method = strtolower($method);

            return [strtolower($modules), strtolower($controller), strtolower($method)];
        } catch (Exception $e) {
            try {
                $uriParams  = explode('/', request()->route()->uri);
                $modules    = $uriParams['0'];
                $controller = $uriParams['1'];
                $method     = $uriParams['2'];
                return [strtolower($modules), strtolower($controller), strtolower($method)];
            } catch (Exception $e) {
                return ['index', 'index', 'index'];
            }

        }
    }
}