<?php

/**
 * 常用的一些函数归纳
 */

function zxf_test()
{
	die('this is a test fun');
}

if (!function_exists('zxf_substr')) {
	/**
	 * 字符串截取
	 */
	function zxf_substr($string,$start = 0,$length=5){
	    $string = str_ireplace(' ', '', $string);// 去除空格
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

if (!function_exists('zxf_check_file_exists')) {
	/**
	 * 判断远程资源是否存在
	 * @Author   ZhaoXianFang
	 * @DateTime 2019-06-26
	 * @param    [type]       $url [description]
	 * @return   [type]            [description]
	 */
	function zxf_check_file_exists($url)
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

if (!function_exists('zxf_default_img')) {
    /**
     * 判断图片是否存在，如果不存在则使用默认图 [若使用第三个参数，则用第三个参数替换第二个参数里面的固定字符串 __str__ ]
     * $imgPath 展示的图片地址
     * $defaultImgOrReplaceStr :1、如果 $imgPath 不存在且 $replaceStr 为空时候表示 默认图片地址; 2、如果 $imgPath 不存在且 $replaceStr 不为空则用 $replaceStr 替换  $defaultImgOrReplaceStr 中的 固定字符串 __str__
     */
    function zxf_default_img($imgPath='',$defaultImgOrReplaceStr='',$replaceStr='')
    {
        if (substr($imgPath, 0, 4) == 'http' && zxf_check_file_exists($imgPath)) {
            return $imgPath;
        }
        $imgPath = substr($imgPath, 0, 1) == '/' ? '.' . $imgPath : $imgPath;
        return is_file($imgPath) ? ltrim($imgPath, '.') :  str_ireplace('__str__', $replaceStr, $defaultImgOrReplaceStr);
    }
}
if (!function_exists('zxf_remove_str_emoji')) {
	// 移除字符串中的 emoji 表情
	function zxf_remove_str_emoji($str)
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

if (!function_exists('zxf_check_str_exists_emoji')) {
	// 判断字符串中是否含有 emoji 表情
	function zxf_check_str_exists_emoji($str)
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

if (!function_exists('zxf_is_crawler')) {
	/**
	 * [isCrawler 检测是否为爬虫]
	 * @Author   ZhaoXianFang
	 * @DateTime 2019-12-24
	 * @param    boolean      $returnName [是否返回爬虫名称]
	 * @return   boolean                  [description]
	 */
	function zxf_is_crawler($returnName = false)
	{
	    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
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

if (!function_exists('zxf_img_to_gray')) {
	/**
	 * [zxf_img_to_gray 把彩色图片转换为灰度图片,支持透明色]
	 * @Author   ZhaoXianFang
	 * @DateTime 2019-06-24
	 * @param    string       $imgFile      [源图片地址]
	 * @param    string       $saveFile     [生成目标地址,为空时直接输出到浏览器]
	 * @return   bool                       [true:成功；false:失败]
	 */
	function zxf_img_to_gray($imgFile = '', $saveFile = '')
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


if (!function_exists('zxf_truncate')) {
    /**
     * 文章去去除标签截取文字
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-12
     * @param    [type]       $string [被截取字符串]
     * @param    integer      $length [长度]
     * @param    boolean      $append [是否加...]
     * @return   [type]               [description]
     */
    function zxf_truncate($string, $length = 150, $append = true)
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

if (!function_exists('zxf_rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function zxf_rmdirs($dirname, $withself = true)
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