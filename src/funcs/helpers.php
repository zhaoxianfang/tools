<?php

/**
 * 常用的一些函数归纳
 */

if (!function_exists('session')) {
    /**
     * 简易 session 助手函数
     */
    function session($name, $value = null)
    {
        $handle = \zxf\Tools\Session::instance();
        if ($value != null) {
            $value = (is_array($value) || is_object($value)) ? json_encode($value) : $value;
            return $handle->$name = $value;
        } else {
            $val = $handle->$name;
            return is_json($val) ? json_decode_plus($val, true) : $val;
        }
    }
}
if (!function_exists('cache')) {
    /**
     * cache 助手函数
     */
    function cache($name, $value = null, $expiry = '+1 day')
    {
        $handle = \zxf\Tools\Cache::instance();
        if (is_null($value)) {
            return $handle->delete($name);
        }
        if (!empty($value)) {
            $value = (is_array($value) || is_object($value)) ? json_encode($value) : $value;
            return $handle->set($name, $value, $expiry);
        } else {
            $val = $handle->get($name);
            return is_json($val) ? json_decode_plus($val, true) : $val;
        }
    }
}

if (!function_exists('truncate')) {
    /**
     * 文章去除标签截取文字
     *
     *
     * @DateTime 2018-09-12
     *
     * @param string  $string [被截取字符串]
     * @param integer $start  [起始位置]
     * @param integer $length [长度]
     * @param boolean $append [是否加...]
     *
     * @return   string
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
            $newStr = mb_substr($string, 0, $length, "UTF-8");
        } elseif (function_exists('iconv_substr')) {
            $newStr = iconv_substr($string, 0, $length, "UTF-8");
        } else {
            $length = abs($length);
            $len    = $start + $length;
            $newStr = '';
            for ($i = $start; $i < $len && $i < $strLen; $i++) {
                if (ord(substr($string, $i, 1)) > 0xa0) {
                    //utf8编码中一个汉字是占据3个字节的，对于其他的编码的字符串，中文占据的字节各有不同，自己需要去修改这个数a
                    $newStr .= substr($string, $i, 3);//此处a=3;
                    $i      += 2;
                    $len    += 2; //截取了三个字节之后，截取字符串的终止偏移量也要随着每次汉字的截取增加a-1;
                } else {
                    $newStr .= substr($string, $i, 1);
                }
            }
        }
        return $newStr . (($append && $string != $newStr) ? '...' : '');
    }
}

if (!function_exists('remove_str_emoji')) {
    // 移除字符串中的 emoji 表情
    function remove_str_emoji($str): string
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
    function check_str_exists_emoji($str): bool
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
     *
     *
     * @DateTime 2019-12-24
     *
     * @param boolean $returnName          [是否返回爬虫名称]
     * @param boolean $forbidUnknownSpider [是否阻止未知的爬虫高频访问]
     *
     * @return   boolean                  [description]
     */
    function is_crawler(bool $returnName = false, bool $forbidUnknownSpider = false)
    {
        $userAgent = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        if (!empty($userAgent)) {
            $spiderSite = [
                "TencentTraveler", "Baiduspider+", "Baiduspider", "BaiduGame",
                "Googlebot", "msnbot", "Sosospider+", "Sosoimagespider+",
                "Sogou web spider", "ia_archiver", "Yahoo! Slurp", "YoudaoBot",
                "Yahoo Slurp", "Yahoo! Slurp China", "MSNBot", "Java (Often spam bot)",
                "BaiDuSpider", "Voila", "Yandex bot", "BSpider", "twiceler",
                "Sogou Spider", "Speedy Spider", "Google AdSense", "Heritrix",
                "Python-urllib", "Alexa (IA Archiver)", "Ask", "Exabot", "Custo",
                "OutfoxBot/YodaoBot", "OutfoxBot", "YodaoBot", "yacy", "SurveyBot", "legs",
                "lwp-trivial", "Nutch", "StackRambler", "The web archive (IA Archiver)",
                "Perl tool", "MJ12bot", "Netcraft", "MSIECrawler", "WGet tools", "larbin",
                "Fish search", "360Spider", 'Bingbot', 'Baiduspider', 'YandexBot',
                'Yahoo! Slurp', 'DuckDuckGo-Favicons-Bot', 'Twitterbot', 'Facebook External Hit',
                'LinkedInBot', 'Pinterest', 'Instagram', 'Applebot', 'Sogou Spider',
                'Exabot', 'MJ12bot', 'AhrefsBot', 'DotBot', 'SemrushBot', 'Yeti',
                'BingPreview', 'Qwantify', 'Archive.org Bot', 'Embedly', 'Discordbot',
            ];
            foreach ($spiderSite as $val) {
                $str = strtolower($val);
                if (strpos($userAgent, $str) !== false) {
                    return $returnName ? $val : true;
                } else {
                    // 没有在上面定义的爬虫名称范围内，则判断为未知爬虫
                    if (strpos($userAgent, 'bot') !== false || strpos($userAgent, 'spider') !== false) {
                        // 请求者是爬虫
                        return $returnName ? 'Unnamed Spider' : true;
                    }
                    // 检查访问频率
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
     *
     *
     * @DateTime 2019-06-24
     *
     * @param string $imgFile  [源图片地址]
     * @param string $saveFile [生成目标地址,为空时直接输出到浏览器]
     *
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
        imagealphablending($block, false);                      //关闭混合模式，以便透明颜色能覆盖原画布
        //生成灰度图
        if (!$block || !imagefilter($block, IMG_FILTER_GRAYSCALE)) {
            return false;
        }
        // imagefilter($block, IMG_FILTER_BRIGHTNESS, -35);//亮度降低35
        imagefill($block, 0, 0, $color);                        //填充
        imagesavealpha($block, true);                           //设置保存PNG时保留透明通道信息
        //图片后缀 生成图片
        switch ($imgInfo['extension']) {
            case 'png':
                if ($saveFile) {
                    imagepng($block, $saveFile); //生成图片
                } else {
                    header('Content-type: image/png');
                    imagepng($block);     //生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            case 'jpg':
                if ($saveFile) {
                    imagejpeg($block, $saveFile); //生成图片
                } else {
                    header('Content-Type: image/jpeg');
                    imagejpeg($block);    //生成图片
                    imagedestroy($block); // 释放内存
                }
                break;
            default:
                return false;
        }
        return true;
    }
}

if (!function_exists('del_dirs')) {
    /**
     * 删除文件夹
     *
     * @param string $dirname 目录
     * @param bool   $delSelf 是否删除自身
     *
     * @return boolean
     */
    function del_dirs(string $dirname, bool $delSelf = true): bool
    {
        if (!is_dir($dirname)) {
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
if (!function_exists('deldir')) {
    /**
     * 删除文件夹及其文件夹下所有文件
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

if (!function_exists('dir_is_empty')) {
    /**
     * 判断文件夹是否为空
     *
     * @param string $dir
     *
     * @return bool
     */
    function dir_is_empty(string $dir): bool
    {
        $res = false;
        if ($handle = opendir($dir)) {
            while (!$res && ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    $res = true;
                }
            }
        }
        closedir($handle);
        return $res;
    }
}

if (!function_exists('create_dir')) {
    /**
     * 递归创建目录
     *
     * @param string $dir         目录
     * @param int    $permissions 权限
     *
     * @return boolean
     */
    function create_dir(string $dir, int $permissions = 0755): bool
    {
        return is_dir($dir) or (create_dir(dirname($dir), $permissions) and mkdir($dir, $permissions, true));
    }
}

if (!function_exists('get_filesize')) {
    /**
     * 获取文件的大小
     *
     * @param string $filePath 文件路径
     *
     * @return string
     */
    function get_filesize(string $filePath): string
    {
        return byteFormat(stat($filePath)['size']);
    }

}
if (!function_exists('byteFormat')) {
    /**
     * 文件字节转具体大小 array("B", "KB", "MB", "GB", "TB", "PB","EB","ZB","YB")， 默认转成M
     *
     * @param int $size 文件字节
     * @param int $dec
     *
     * @return string
     */
    function byteFormat(int $size, int $dec = 2): string
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
     *
     *
     * @DateTime 2019-01-07
     *
     * @param array        $responseDara   立即响应的数组数据
     * @param string|array $backendFun     需要在后台执行的方法
     * @param array        $backendFunArgs 给在后台执行的方法传递的参数
     * @param int          $setTimeLimit   设置后台响应可执行时间
     *
     * @return void
     *
     * @demo     ：先以json格式返回$data，然后在后台执行 $this->pushSuggestToJyblSys(array('suggId' => $id))
     *         response_and_continue($data, array($this, "pushSuggestToJyblSys"), array('suggId' => $id));
     */
    function response_and_continue(array $responseDara, string|array $backendFun, array $backendFunArgs = [], int $setTimeLimit = 0)
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

if (!function_exists('num_to_cn')) {
    /**
     * 数字转换为中文
     *
     *
     *
     * @param float|int|string $num  目标数字
     * @param bool             $mode 模式[true:金额（默认）,false:普通数字表示]
     * @param bool             $sim  使用小写（默认）
     *
     * @return string
     */
    function num_to_cn(float|int|string $num, bool $mode = true, bool $sim = true): string
    {
        if (!is_numeric($num)) {
            return '含有非数字非小数点字符！';
        }
        $char = $sim ? array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九')
            : array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        $unit = $sim ? array('', '十', '百', '千', '', '万', '亿', '兆')
            : array('', '拾', '佰', '仟', '', '萬', '億', '兆');
        // $retStr = $mode ? '元' : '点';
        $retStr = $mode ? '' : '点';
        //小数部分
        if (strpos($num, '.')) {
            list($num, $dec) = explode('.', $num);
            $dec = strval(round($dec, 2));
            if ($mode) {
                $retStr .= "{$char[$dec['0']]}角{$char[$dec['1']]}分";
            } else {
                for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                    $retStr .= $char[$dec[$i]];
                }
            }
        }
        //整数部分
        $out = [];
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
        return join('', $num_val) . $retStr;
    }
}

if (!function_exists('arr2tree')) {
    /**
     * 二维数组 转为 树形结构 和 tree2arr 互逆
     *
     * @param array  $array        二维数组
     * @param int    $superior_id  上级ID
     * @param string $superior_key 父级键名
     * @param string $primary_key  主键名
     * @param string $son_key      子级键名
     *
     * @return array
     */
    function arr2tree(array $array, int $superior_id = 0, string $superior_key = 'pid', string $primary_key = 'id', string $son_key = 'son'): array
    {
        $return = [];
        foreach ($array as $k => $v) {
            if ($v[$superior_key] == $superior_id) {
                $son = arr2tree($array, $v[$primary_key], $superior_key, $primary_key, $son_key);
                if ($son) {
                    $v[$son_key] = $son;
                }
                $return[] = $v;
            }
        }
        return $return;
    }

}

if (!function_exists('tree2arr')) {
    /**
     * 树形结构 转为 二维数组  和 arr2tree 互逆
     *
     * @param array  $array   树形数据
     * @param string $son_key 子级的键名
     * @param int    $level   层级
     *
     * @return array
     */
    function tree2arr(array $array, string $son_key = 'son', int $level = 0): array
    {
        $return = [];
        $level  += 1;
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $son = isset($value[$son_key]) ? $value[$son_key] : '';
                if ($son) {
                    unset($value[$son_key]);
                }
                $value['level'] = $level;
                array_push($return, $value);
                if ($son) {
                    $return = array_merge($return, tree2arr($son, $son_key, $level));
                }
            }
        }
        return $return;
    }
}

if (!function_exists('show_img')) {
    /*
     * 页面直接输出图片
     */
    function show_img($imgFile = '')
    {
        header('Content-type:image/png');
        die(file_get_contents($imgFile));
    }
}
if (!function_exists('string_to_utf8')) {
    /*
     * 字符串自动转utf8编码
     */
    function string_to_utf8(string $str = '')
    {
        return mb_convert_encoding($str, "UTF-8", "auto");
    }
}
if (!function_exists('string_to_gbk')) {
    /*
     * 字符串自动转gbk编码
     */
    function string_to_gbk(string $str = '')
    {
        return mb_convert_encoding($str, "GBK", "auto");
    }
}
if (!function_exists('show_json')) {
    /*
     * 对json数据格式化输入展示 [转化为json格式，并格式化样式]
     */
    function show_json(array $array = [])
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

if (!function_exists('is_idcard')) {
    /**
     * 判断是否为身份证
     *
     * @param $idCard
     *
     * @return bool
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
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum           = 0;
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
            if (in_array(substr($idcard, 12, 3), array('996', '997', '998', '999'))) {
                $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
            } else {
                $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
            }
        }
        return $idcard . idcard_verify_number($idcard);
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

if (!function_exists('detach_html')) {
    // 去除所有html标签
    function detach_html($string): string
    {
        $string = htmlspecialchars_decode($string);
        $string = html_entity_decode($string);
        $string = strip_tags($string);
        $string = trim($string);
        $string = str_replace(PHP_EOL, '', $string);                                      // 过滤换行
        $string = str_replace('&nbsp;', '', $string);                                     // 去除实体空格
        $string = preg_replace("/\s+/", " ", $string);                                    //过滤多余回车
        $string = preg_replace("/<[ ]+/si", "<", $string);                                //过滤<__("<"号后面带空格)
        $string = preg_replace("/<\!--.*?-->/si", "", $string);                           //过滤html注释
        $string = preg_replace("/<(\!.*?)>/si", "", $string);                             //过滤DOCTYPE
        $string = preg_replace("/<(\/?html.*?)>/si", "", $string);                        //过滤html标签
        $string = preg_replace("/<(\/?head.*?)>/si", "", $string);                        //过滤head标签
        $string = preg_replace("/<(\/?meta.*?)>/si", "", $string);                        //过滤meta标签
        $string = preg_replace("/<(\/?body.*?)>/si", "", $string);                        //过滤body标签
        $string = preg_replace("/<(\/?link.*?)>/si", "", $string);                        //过滤link标签
        $string = preg_replace("/<(\/?form.*?)>/si", "", $string);                        //过滤form标签
        $string = preg_replace("/cookie/si", "COOKIE", $string);                          //过滤COOKIE标签
        $string = preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si", "", $string);     //过滤applet标签
        $string = preg_replace("/<(\/?applet.*?)>/si", "", $string);                      //过滤applet标签
        $string = preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si", "", $string);       //过滤style标签
        $string = preg_replace("/<(\/?style.*?)>/si", "", $string);                       //过滤style标签
        $string = preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si", "", $string);       //过滤title标签
        $string = preg_replace("/<(\/?title.*?)>/si", "", $string);                       //过滤title标签
        $string = preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si", "", $string);     //过滤object标签
        $string = preg_replace("/<(\/?objec.*?)>/si", "", $string);                       //过滤object标签
        $string = preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si", "", $string); //过滤noframes标签
        $string = preg_replace("/<(\/?noframes.*?)>/si", "", $string);                    //过滤noframes标签
        $string = preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si", "", $string);   //过滤frame标签
        $string = preg_replace("/<(\/?i?frame.*?)>/si", "", $string);                     //过滤frame标签
        $string = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $string);     //过滤script标签
        $string = preg_replace("/<(\/?script.*?)>/si", "", $string);                      //过滤script标签
        $string = preg_replace("/javascript/si", "Javascript", $string);                  //过滤script标签
        $string = preg_replace("/vbscript/si", "Vbscript", $string);                      //过滤script标签
        $string = preg_replace("/on([a-z]+)\s*=/si", "On\\1=", $string);                  //过滤script标签
        return trim($string);
    }
}
if (!function_exists('str_rand')) {
    /**
     * 生成随机字符串
     *
     *
     * @DateTime 2017-06-28
     *
     * @param integer $length 字符串长度
     * @param string  $tack   附加值
     *
     * @return   string 字符串
     */
    function str_rand(int $length = 6, string $tack = ''): string
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
     *
     * @param $appId
     * @param $encryptedData
     * @param $iv
     * @param $sessionKey
     *
     * @return array|mixed
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

        $dataObj = json_decode_plus(obj2Arr($result), true);
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

if (!function_exists('img_to_base64')) {
    /**
     * 图片转 base64
     *
     *
     * @DateTime 2017-07-18
     *
     * @param    [type]       $image_file [description]
     *
     * @return   string                   [description]
     */
    function img_base64($image_file): string
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        return 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
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
                create_dir($new_file);
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

if (!function_exists('is_json')) {
    /**
     * [is_json 判断json]
     *
     *
     * @DateTime 2018-12-27
     *
     * @param    [type]       $string [description]
     *
     * @return   boolean              [description]
     */
    function is_json($string): bool
    {
        try {
            $data = json_decode_plus($string, false);
            if ((!empty($data) && is_object($data)) || (is_array($data) && !empty($data))) {
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }
}

if (!function_exists('get_full_path')) {
    /**
     * 根据相对路径获取绝对路径
     *
     * @param string $path 相对路径
     */
    function get_full_path($path)
    {
        $info = pathinfo($path);
        return $_SERVER['DOCUMENT_ROOT'] . '/' . $info['dirname'] . '/' . $info['basename'];
    }
}

if (!function_exists('convert_underline')) {
    /**
     * 下划线转驼峰
     *
     *
     * @DateTime 2018-08-29
     *
     * @param string $str
     *
     * @return array|string|null [type]       [description]
     */
    function convert_underline(string $str)
    {
        return preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
    }
}

if (!function_exists('underline_convert')) {
    /**
     * 驼峰转下划线
     *
     *
     * @DateTime 2018-08-29
     * @return   string       [description]
     */
    function underline_convert(string $str): string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }
}

if (!function_exists('check_pass_strength')) {
    /**
     * 验证等保测2级评密码强度
     * 验证密码强度是否符合 至少包含大小写字母、数字、特殊字符大于8个字符
     *
     *
     * @DateTime 2020-01-08
     *
     * @param string $password [description]
     *
     * @return bool
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


if (!function_exists('buildRequestFormAndSend')) {
    /**
     * 构建form表单并提交数据
     * 满足提交大量表单会被数据长度等限制的问题
     * [header 携带大量数据请求的可行性方案]
     *
     * @param string $url    数据提交跳转到的URL
     * @param array  $data   需要提交的数组,支持多维 (按照数组的键值对组装form表单数据)
     * @param string $method 提交方式 支持 post|get|put|delete
     *
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
     *
     * @param $array
     *
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
     * 根据微秒时间和随机数生成 10位 或者12 位的 uuid
     *
     * @param bool $useUpper 是否生成大写标识字符
     *                       true  : [0~9 + A~Z(不包含O)] 生成12位长度的uuid;
     *                       false : [0~9 + a~Z]         生成10位长度的uuid;
     *
     * @return string
     * @throws Exception
     */
    function uuid($useUpper = false): string
    {
        $timeArr = explode(' ', microtime());
        $timeNum = trim($timeArr[1] . str_replace('0.', '', $timeArr[0]), '00') . ($useUpper ? sprintf("%03d", random_int(0, 999)) : sprintf("%02d", random_int(0, 99)));

        $dict = $useUpper ? '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ' : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $result = '';
        $len    = strlen($dict);
        do {
            $result  = $dict[bcmod($timeNum, $len)] . $result;
            $timeNum = bcdiv($timeNum, $len, 0);
        } while ($timeNum != 0);

        if ((!$useUpper && strlen($result) != 10) || ($useUpper && strlen($result) != 12)) {
            return uuid($useUpper);
        }
        return $result;
    }
}
if (!function_exists('dict_convert_ten')) {
    /**
     * 把其他进制的字符串转换为10进制（注：针对使用自定义字典的字符串转换，普通的进制转换可以使用 base_convert_any 函数）
     *
     * @param string $string 根据自定义字典 $dict 生成的字符串
     * @param string $dict   可以传入自定义的字典字符串
     *
     * @return string
     */
    function dict_convert_ten($string = '', $dict = '')
    {
        $dict   = empty($dict) ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : $dict;
        $strLen = strlen($string);
        $dec    = 0;
        for ($i = 0; $i < $strLen; $i++) {
            //找到对应字典的下标
            $pos = strpos($dict, $string[$i]);
            $dec += $pos * pow($strLen, $strLen - $i - 1);
        }
        return number_format($dec, 0, '', '');
    }
}

if (!function_exists('base_convert_any')) {
    /**
     * 将任意进制的数值转换为另一个进制的数值
     *
     * @param string $number    待转换数值，可以是整数或浮点数，支持负数，例如："123", "-456.789"
     * @param int    $from_base 原始进制，必须在 2 到 62 之间
     * @param int    $to_base   目标进制，必须在 2 到 62 之间
     *
     * @return string|false 转换成功返回目标进制下的数值，否则返回 false
     */
    function base_convert_any(string $number, int $from_base, int $to_base): string|false
    {
        // 定义字符集，用于表示不同进制下的数字
        $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // 去掉前后空格，并将字母转换为小写，方便处理
        $number = strtolower(trim($number));
        // 是否为负数
        $is_negative = false;
        // 是否为小数
        $has_decimal = false;
        // 小数部分的位数
        $decimal_places = 0;
        //整数部分
        $integer_part = 0;

        // 验证进制范围是否合法
        if ($from_base < 2 || $to_base < 2 || $from_base > strlen($charset) || $to_base > strlen($charset)) {
            return false;
        }

        // 处理负数情况
        if (strpos($number, '-') === 0) {
            $is_negative = true;
            $number      = substr($number, 1);
        }

        // 处理小数情况
        if (strpos($number, '.') !== false) {
            $has_decimal = true;
            [$integer_part, $fractional_part] = explode('.', $number);
            $decimal_places = strlen($fractional_part);
            $number         = $integer_part . $fractional_part;
        }

        // 将原始进制下的数值转换为十进制形式
        $length = strlen($number);
        $dec    = 0;

        for ($i = 0; $i < $length; ++$i) {
            $value = strpos($charset, $number[$i]);
            if ($value >= $from_base) {
                return false;
            }
            $dec = bcadd($dec, bcmul($value, bcpow($from_base, $length - $i - 1)));
        }

        // 处理小数部分
        if ($has_decimal) {
            $fraction = bcdiv($dec, bcpow($from_base, $decimal_places));
            $result   = number_format($fraction, $decimal_places, '.', '');
            $dec      = intval($integer_part);
        } else {
            $result = '';
        }

        // 将十进制形式的数值转换为目标进制下的数值
        while ($dec > 0) {
            $remainder = bcmod($dec, $to_base);
            $result    = $charset[$remainder] . $result;
            $dec       = bcdiv(bcsub($dec, $remainder), $to_base);
        }

        // 处理负数情况
        if ($is_negative) {
            $result = '-' . $result;
        }
        return $result;
    }
}

if (!function_exists('download_url_file')) {
    /**
     * 下载url文件
     */
    function download_url_file($url = '')
    {
        $filename = !empty($url) ? $url : (!empty($_GPC['url']) ? $_GPC['url'] : '');
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
     * 字符串加密和解密
     *
     * @param string     $string         字符串
     * @param string     $operation      de(DECODE)表示解密，en(ENCODE)表示加密；
     * @param int|string $expiry         缓存生命周期 0表示永久缓存 默认99年
     *                                   支持格式:
     *                                   int 缓存多少秒，例如 90 表示缓存90秒，如果小于等于0，则用0替换
     *                                   string: 时间字符串格式,例如:+1 day、2023-01-01 09:00:02 等 strtotime 支持的格式均可
     * @param string     $key
     *
     * @return false|string
     */
    function str_en_code(string $string, string $operation = 'en', int|string $expiry = 312206400, string $key = ''): bool|string
    {
        $operation = in_array($operation, ['de', 'DECODE']) ? 'DECODE' : 'ENCODE';
        // 转换字符串
        $string = $operation == 'DECODE' ? str_replace(array("_"), array("/"), $string) : $string;
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;
        // 密匙
        $key = md5(!empty($key) ? $key : 'www.weisifang.com');
        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey   = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
        //解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $expiry        = (is_numeric($expiry) || empty($expiry)) ? time() + (int)$expiry : strtotime($expiry);
        $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result        = '';
        $box           = range(0, 255);
        $rndkey        = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a       = ($a + 1) % 256;
            $j       = ($j + $box[$a]) % 256;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            $result = $keyc . str_replace('=', '', base64_encode($result));
            // 转换字符串
            return str_replace(['/', '='], ['_', ''], $result);
        }
    }
}


if (!function_exists('get_protected_value')) {
    /**
     * 打印对象里面受保护属性的值
     *
     * @param $obj
     * @param $name
     *
     * @return mixed
     */
    function get_protected_value($obj, $name): mixed
    {
        $array  = (array)$obj;
        $prefix = chr(0) . '*' . chr(0);
        return $array[$prefix . $name];
    }
}

if (!function_exists('set_protected_value')) {
    /**
     * 使用反射 修改对象里面受保护属性的值
     *
     * @param $obj
     * @param $filed
     * @param $value
     *
     * @return void
     * @throws ReflectionException
     */
    function set_protected_value($obj, $filed, $value)
    {
        $reflectionClass    = new ReflectionClass($obj);
        $reflectionProperty = $reflectionClass->getProperty($filed);
        try {
            $reflectionClass->setStaticPropertyValue($filed, $value);
        } catch (\Exception $err) {
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($obj, $value);
        }
    }
}

if (!function_exists('json_decode_plus')) {
    /**
     * json_decode 加强版， 主要是为了了处理 json 字符串中包含了 \\" 和 \\ 转义字符导致无法解析的问题
     *
     * @param string $jsonStr json 字符串
     * @param null   $assoc
     *
     * @return mixed
     */
    function json_decode_plus(string $jsonStr, $assoc = null): mixed
    {
        try {
            $jsonStr = preg_replace('/\\"/', '"', $jsonStr);
            $jsonStr = str_replace('\\', '', $jsonStr);
            return json_decode($jsonStr, $assoc);
        } catch (\Exception $e) {
            return json_decode($jsonStr, $assoc);
        }
    }
}

if (!function_exists('is_mobile')) {
    /**
     * 判断当前浏览器是否为移动端
     */
    function is_mobile(): bool
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'vnd.wap.wml') !== false || strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'text/vnd.wap.wml') !== false)) {
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

if (!function_exists('url_conversion')) {
    /**
     * 把 ./ 和 ../ 开头的资源地址转换为绝对地址
     *
     * @param string $string       需要转换的字符串
     * @param string $prefixString 拼接的前缀字符
     * @param array  $linkAttr     需要转换的标签属性，例如：href、src、durl
     *
     * @return string
     */
    function url_conversion(string $string = '', string $prefixString = '', array $linkAttr = ['href', 'src']): string
    {
        if (empty($string) || empty($prefixString)) {
            return $string;
        }
        // 判断$string是否是 / 、./ 或者 ../ 开头的url字符串
        if (mb_substr($string, 0, 1, "utf-8") == '/' || mb_substr($string, 0, 2, "utf-8") == './' || mb_substr($string, 0, 3, "utf-8") == '../') {
            return url_conversion_to_prefix_path($string, $prefixString);
        }
        $linkAttrString = implode('|', $linkAttr); // 数组转为字符串 用 (竖线)`|` 分割，例如：href|src|durl
        // 正则查找 $linkAttr 属性中 以 ./、../、/ 和文件夹名称开头的图片或超链接的相对路径 URL 地址字符串,要求src、href等前面至少带一个空格，避免操作 src 和 oldsrc 都识别到src的情况
        // $pattern = '/\s+(href|src)\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        $pattern = '/\s+(' . $linkAttrString . ')\s*=\s*"(?:\.\/|\.\.|\/)?([^"|^\']+)"/';
        preg_match_all($pattern, $string, $matches);

        $relativeURLs    = $matches[0];
        $originalPath    = []; // 原始的相对路径数组
        $replacePath     = []; // 替换成的前缀路径数组
        $plusReplacePath = []; // 加强版替换路径数组
        foreach ($relativeURLs as $findStr) {
            // 删除 $findStr 字符串中的 href= 或者 src= durl= 字符串
            $findStr        = preg_replace('/\s+(' . $linkAttrString . ')\s*=\s*["\']/i', '', $findStr);
            $originalPath[] = $findStr;
            $replacePath[]  = url_conversion_to_prefix_path($findStr, $prefixString);
        }
        if (!empty($originalPath) && !empty($replacePath)) {
            // 批量替换地址;直接在此处替换会导致 出现相同的'link'字符串时候会被替换多次，导致出现错误的结果
            // $string = str_replace($originalPath, $replacePath, $string);

            // 加强版开始开始表演：找出 'link' 相关字符串的前缀(例如src、href等)最为批量替换的前缀，防止被多次替换
            // 强化前缀字符串
            $strengthenAttr = $matches[1];
            foreach ($originalPath as $index => $item) {
                // 判断最后一个引号是单引号还是双引号
                $lastQuotationMark = substr($relativeURLs[$index], -1);
                // 把替换结果拼上 $linkAttr 对应的前缀，例如 ` src="` 或者 ` href="等
                $plusReplacePath[$index] = ' ' . $strengthenAttr[$index] . '=' . $lastQuotationMark . $replacePath[$index];
            }
            // 批量替换地址
            $string = str_replace($relativeURLs, $plusReplacePath, $string);
        }
        return $string;
    }
}
if (!function_exists('url_conversion_to_prefix_path')) {
    /**
     * 把 $url 中的 相对路径 转换为$prefix前缀路径, 建议调用 url_conversion() 方法
     *
     * @param string $url
     * @param string $prefix
     *
     * @return string
     */
    function url_conversion_to_prefix_path(string $url = '', string $prefix = ''): string
    {
        if (empty($url) || empty($prefix)) {
            return $url;
        }
        if (mb_substr($url, 0, 4, "utf-8") != 'http') {
            //用 / 把 $prefix  拆分为数组
            $domain_prefix_arr = explode('/', trim($prefix, '/'));
            if (mb_substr($url, 0, 1, "utf-8") == '/') {
                // 处理 / 开头的路径
                if (mb_substr($prefix, 0, 4, "utf-8") == 'http') {
                    // 解析URL
                    $urlInfo = parse_url($prefix);
                    $domain  = $urlInfo['scheme'] . '://' . $urlInfo['host'] . (!empty($urlInfo['port']) ? ':' . $urlInfo['port'] : '');
                    return $domain . $url;
                } else {
                    return $domain_prefix_arr[0] . $url;
                }
            }
            // 查找 $url 字符串中出现了几次 ../ ,例如：../../ ,不要查找 ./ ，因为 ./ 表示0次
            $count = mb_substr_count($url, '../', "utf-8");
            // 从 $domain_prefix_arr 中删除 $count 个元素
            $count > 0 && array_splice($domain_prefix_arr, -$count);
            // 用 / 把 $domain_prefix_arr  拼接为字符串
            $prefix = implode('/', $domain_prefix_arr);
            // 去掉 $url 字符串中的 ../ 和 ./
            $url = str_replace(['../', './'], '', $url);
            $url = rtrim($prefix, '/') . '/' . ltrim($url, '/');
        }
        return $url;
    }
}