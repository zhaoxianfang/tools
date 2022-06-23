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
        $mbLen  = mb_strlen($str);
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
     * @param boolean $returnName [是否返回爬虫名称]
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
     * @param string $imgFile [源图片地址]
     * @param string $saveFile [生成目标地址,为空时直接输出到浏览器]
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
            case 'png':
                $block = imagecreatefrompng($imgFile); //从 PNG 文件或 URL 新建一图像
                break;
            case 'jpg':
                $block = imagecreatefromjpeg($imgFile); //从 JPEG 文件或 URL 新建一图像
                break;
            default:
                return false;
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
            default:
                return false;
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
     * @param integer $length [长度]
     * @param boolean $append [是否加...]
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
        return rmdir($dir) ? true : false;
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
if (!function_exists('byteFormat')) {
    /**
     * @desc 文件字节转具体大小 array("B", "KB", "MB", "GB", "TB", "PB","EB","ZB","YB")， 默认转成M
     * @param $size 文件字节
     * @return string
     */
    function byteFormat($size, $dec = 2)
    {
        $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        $pos   = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $units[$pos];
    }
}

if (!function_exists('response_and_continue')) {
    /**
     * 输出json后继续在后台执行指定方法
     * @Author   ZhaoXianFang
     * @DateTime 2019-01-07
     * @param $responseDara 立即响应的数组数据
     * @param $backendFun   需要在后台执行的方法
     * @param $backendFunArgs 给在后台执行的方法传递的参数
     * @param $setTimeLimit 设置后台响应可执行时间
     * @return void
     *
     * @demo ：先以json格式返回$data，然后在后台执行 $this->pushSuggestToJyblSys(array('suggId' => $id))
     *         response_and_continue($data, array($this, "pushSuggestToJyblSys"), array('suggId' => $id));
     */
    function response_and_continue($responseDara, $backendFun, $backendFunArgs = array(), $setTimeLimit = 0)
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
        sleep(3);
        ignore_user_abort(true);
        set_time_limit($setTimeLimit);
        if (!empty($backendFun)) {
            call_user_func_array($backendFun, $backendFunArgs);
        }
    }
}

if (!function_exists('num_to_zhcn')) {
    /**
     * 数字转换为中文
     * @Author   ZhaoXianFang
     * @param string|integer|float $num 目标数字
     * @param integer $mode 模式[true:金额（默认）,false:普通数字表示]
     * @param boolean $sim 使用小写（默认）
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
            $dec = strval(round($dec, 2));
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
            $array = (array)$array;
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
            $modules = str_replace('\\', '.', str_replace('App\\Http\\Controllers\\', '', trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\')));

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

if (!function_exists('is_idcard')) {
    /**
     * 赵先方
     * 判断是否为身份证
     *code BEGIN
     */
    function is_idcard($idcard)
    {
        $id_card = trim($idcard);
        if (strlen($id_card) == 18) {
            return idcard_checksum18($id_card);
        } elseif ((strlen($id_card) == 15)) {
            $id_card = idcard_15to18($id_card);
            return idcard_checksum18($id_card);
        } else {
            return false;
        }
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    function idcard_verify_number($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum           = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod           = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    // 将15位身份证升级到18位
    function idcard_15to18($idcard)
    {
        if (strlen($idcard) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
                $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
            } else {
                $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
            }
        }
        $idcard = $idcard . idcard_verify_number($idcard);
        return $idcard;
    }

    // 18位身份证校验码有效性检查
    function idcard_checksum18($idcard)
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

if (!function_exists('cutstr_html')) {
    // 去除所有html标签
    function cutstr_html($string)
    {
        $string = htmlspecialchars_decode($string);
        $string = strip_tags($string);
        $string = trim($string);
        $string = str_replace(PHP_EOL, '', $string); // 过滤换行
        $string = str_replace('&nbsp;', '', $string); // 去除实体空格
        $string = preg_replace("/\s+/", " ", $string);//过滤多余回车
        $string = preg_replace("/<[ ]+/si", "<", $string); //过滤<__("<"号后面带空格)
        $string = preg_replace("/<\!--.*?-->/si", "", $string); //过滤html注释
        $string = preg_replace("/<(\!.*?)>/si", "", $string); //过滤DOCTYPE
        $string = preg_replace("/<(\/?html.*?)>/si", "", $string); //过滤html标签
        $string = preg_replace("/<(\/?head.*?)>/si", "", $string); //过滤head标签
        $string = preg_replace("/<(\/?meta.*?)>/si", "", $string); //过滤meta标签
        $string = preg_replace("/<(\/?body.*?)>/si", "", $string); //过滤body标签
        $string = preg_replace("/<(\/?link.*?)>/si", "", $string); //过滤link标签
        $string = preg_replace("/<(\/?form.*?)>/si", "", $string); //过滤form标签
        $string = preg_replace("/cookie/si", "COOKIE", $string); //过滤COOKIE标签
        $string = preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si", "", $string); //过滤applet标签
        $string = preg_replace("/<(\/?applet.*?)>/si", "", $string); //过滤applet标签
        $string = preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si", "", $string); //过滤style标签
        $string = preg_replace("/<(\/?style.*?)>/si", "", $string); //过滤style标签
        $string = preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si", "", $string); //过滤title标签
        $string = preg_replace("/<(\/?title.*?)>/si", "", $string); //过滤title标签
        $string = preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si", "", $string); //过滤object标签
        $string = preg_replace("/<(\/?objec.*?)>/si", "", $string); //过滤object标签
        $string = preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si", "", $string); //过滤noframes标签
        $string = preg_replace("/<(\/?noframes.*?)>/si", "", $string); //过滤noframes标签
        $string = preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si", "", $string); //过滤frame标签
        $string = preg_replace("/<(\/?i?frame.*?)>/si", "", $string); //过滤frame标签
        $string = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $string); //过滤script标签
        $string = preg_replace("/<(\/?script.*?)>/si", "", $string); //过滤script标签
        $string = preg_replace("/javascript/si", "Javascript", $string); //过滤script标签
        $string = preg_replace("/vbscript/si", "Vbscript", $string); //过滤script标签
        $string = preg_replace("/on([a-z]+)\s*=/si", "On\\1=", $string); //过滤script标签
        return trim($string);
    }
}
if (!function_exists('str_rand')) {
    /**
     * 生成随机字符串
     * @Author   ZhaoXianFang
     * @DateTime 2017-06-28
     * @param integer $length 字符串长度
     * @param string $tack 附加值
     * @return   [type]               字符串
     */
    function str_rand($length = 6, $tack = '')
    {
        $chars = 'abcdefghijkmnpqrstuvwxyzACDEFGHIJKLMNOPQRSTUVWXYZ12345679' . $tack;
        $str   = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
}

if (!function_exists('wx_decrypt_data')) {
    /**
     * 微信解密
     * @Author   ZhaoXianFang
     * @DateTime 2020-10-20
     * @param    [type]       $encryptedData [description]
     * @param    [type]       $iv            [description]
     * @param    [type]       $sessionKey    [description]
     * @return   [type]                      [description]
     */
    function wx_decrypt_data($appId, $encryptedData, $iv, $sessionKey)
    {
        // $appId = 'wxfd...9ce';
        if (strlen($sessionKey) != 24) {
            return array(
                'code' => 500,
                'mag'  => 'sessionKey 无效',
            );
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return array(
                'code' => 500,
                'mag'  => 'iv 无效',
            );
        }
        $aesIV     = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result    = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        // $dataObj = object_array(json_decode($result,true) );
        $dataObj = json_decode(object_array($result), true);
        if ($dataObj == null) {
            return array(
                'code' => 500,
                'mag'  => '解析失败',
            );
        }
        if ($dataObj['watermark']['appid'] != $appId) {
            return array(
                'code' => 500,
                'mag'  => 'appid无效',
            );
        }
        return $dataObj;
    }
}

if (!function_exists('img_base64')) {
    /**
     * 图片转 base64
     * @Author   ZhaoXianFang
     * @DateTime 2017-07-18
     * @param    [type]       $image_file [description]
     * @return   [type]                   [description]
     */
    function img_base64($image_file)
    {
        $base64_image = '';
        $image_info   = getimagesize($image_file);
        $image_data   = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}

if (!function_exists('is_json')) {
    /**
     * [is_json 判断json]
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-27
     * @param    [type]       $string [description]
     * @return   boolean              [description]
     */
    function is_json($string)
    {
        try {
            json_decode($string);
            // return (json_last_error() == JSON_ERROR_NONE);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('get_full_path')) {
    /**
     * 根据相对路径获取绝对路径
     * @param string $path 相对路径
     */
    if (!function_exists('get_full_path')) {
        function get_full_path($path)
        {
            $info      = pathinfo($path);
            $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $info['dirname'] . '/' . $info['basename'];
            return $full_path;
        }
    }
}

if (!function_exists('convert_underline')) {
    /**
     * 下划线转驼峰
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-29
     * @return   [type]       [description]
     */
    function convert_underline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }
}

if (!function_exists('underline_convert')) {
    /**
     * 驼峰转下划线
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-29
     * @return   [type]       [description]
     */
    function underline_convert($str)
    {
        $str = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
        return $str;
    }
}

if (!function_exists('check_pass_strength')) {
    /**
     * 验证等保测2级评密码强度
     * 验证密码强度是否符合 至少包含大小写字母、数字、特殊字符大于8个字符
     * @Author   ZhaoXianFang
     * @DateTime 2020-01-08
     * @param string $password [description]
     * @return   [type]                 [description]
     */
    function check_pass_strength($password = '')
    {
        // 检测密码强度 至少包含大小写字母、数字、特殊字符至少3个组合大于8个字符
        $expression = '/^(?![A-Za-z]+$)(?![A-Z\\d]+$)(?![A-Z\\W]+$)(?![a-z\\d]+$)(?![a-z\\W]+$)(?![\\d\\W]+$)\\S{8,}$/';
        if (preg_match($expression, $password)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('base64_to_image')) {
    /**
     * base64图片转文件图片
     * base64_to_image($row['cover'],"./uploads/images")
     */
    function base64_to_image($base64_image_content, $path)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type     = $result[2];
            $new_file = $path . "/" . date('Ymd', time()) . "/";
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0755, true);
            }
            $new_file = $new_file . md5(time() . mt_rand(1, 1000000)) . ".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return ltrim($new_file, '.');
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('buildRequestFormAndSend')) {
    /**
     * 构建form表单并提交数据
     * 满足提交大量表单会被数据长度等限制的问题
     * [header 携带大量数据请求的可行性方案]
     *
     * @param string $url 数据提交跳转到的URL
     * @param array $data 需要提交的数组,支持多维 (按照数组的键值对组装form表单数据)
     * @param string $method 提交方式 支持 post|get|put|delete
     * @return string 组装提交表单的HTML文本
     * @throws Exception
     */
    function buildRequestFormAndSend(string $url, array $data = [], string $method = 'post'): string
    {
        $method        = $method ? strtolower($method) : 'post';
        $methodIsMorph = in_array($method, ['put', 'delete']) ? strtoupper($method) : ''; // 变形
        $method        = in_array($method, ['put', 'delete', 'post']) ? 'post' : 'get';

        $data   = obj2Arr($data);
        $method = strtolower($method) == 'post' ? 'POST' : 'GET';
        $formId = 'requestForm_' . time() . '_' . random_int(2383280, 14776335);
        $html   = "<form id='" . $formId . "' action='" . $url . "' method='" . $method . "'>";
        $html   .= !empty($methodIsMorph) ? '<input type="hidden" name="_method" value="' . $methodIsMorph . '" />' : '';
        // 遍历子数组
        function traverseChildArr($arr, $namePrefix = ''): string
        {
            $arr     = obj2Arr($arr);
            $htmlStr = '';
            foreach ($arr as $key => $item) {
                $name    = empty($namePrefix) ? $key : $namePrefix . '[' . $key . ']';
                $htmlStr .= is_array($item) ? traverseChildArr($item, $name) : "<input type='hidden' name='" . $name . "' value='" . $item . "' />";
            }
            return $htmlStr;
        }

        $html .= traverseChildArr($data, '');
        $html .= "<input type='submit' value='确定' style='display:none;'></form>";
        $html .= "<script>document.forms['" . $formId . "'].submit();</script>";
        return $html;
    }
}

if (!function_exists('obj2Arr')) {
    /**
     * 对象转数组
     * @param $array
     * @return array|mixed
     */
    function obj2Arr($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = obj2Arr($value);
            }
        }
        return $array;
    }
}

if (!function_exists('uuid')) {
    /**
     * 根据微秒时间和随机数生成 12位 uuid
     */
    function uuid()
    {
        $time    = microtime(true);
        $timeStr = date('ymdHis') . substr(explode(' ', microtime())[0], 2, 6) . random_int(3600, 215999);
        return from10to60($timeStr);
    }
}

if (!function_exists('from60to10')) {
    /**
     * 60进制转10进制
     */
    function from60to10($str)
    {
        // (去掉oO)
        $dict = '0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $len  = strlen($str);
        $dec  = 0;
        for ($i = 0; $i < $len; $i++) {
            //找到对应字典的下标
            $pos = strpos($dict, $str[$i]);
            $dec += $pos * pow(60, $len - $i - 1);
        }
        return number_format($dec, 0, '', '');
    }
}

if (!function_exists('from10to60')) {
    /**
     * 10进制转60进制
     */
    function from10to60($dec)
    {
        // (去掉oO)
        $dict   = '0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $result = '';
        do {
            $result = $dict[$dec % 60] . $result;
            $dec    = intval($dec / 60);
        } while ($dec != 0);
        return $result;
    }
}

if (!function_exists('download_url_file')) {
    /**
     * 下载url文件
     */
    function download_url_file($filename = '')
    {
        $filename = !empty($filename) ? $filename : (!empty($_GPC['url']) ? $_GPC['url'] : '');
        $title    = substr($filename, strrpos($filename, '/') + 1);
        $file     = fopen($filename, "rb");
        Header("Content-type:application/octet-stream");
        Header("Accept-Ranges:bytes");
        Header("Content-Disposition:attachment;filename=$title");
        while (!feof($file)) {
            echo fread($file, 8192);
            ob_flush();
            flush();
        }
        fclose($file);
        exit;
    }
}

if (!function_exists('str_en_code')) {
    /**
     * 字符串加解密
     * @Author   ZhaoXianFang
     * @DateTime 2019-04-01
     * @param    [type]       $string [字符串]
     * @param string $action [en:加密；de:解密]
     * @return   [type]               []
     */
    function str_en_code($string, $action = 'en')
    {
        $action != 'en' && $string = base64_decode($string);
        $code   = '';
        $key    = 'str_en_de_code';
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k    = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'de' ? base64_encode($code) : $code);
    }
}
