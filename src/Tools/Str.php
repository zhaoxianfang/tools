<?php

namespace zxf\Tools;

use DOMDocument;
use DOMXPath;
use Exception;

class Str
{
    /**
     * 拼接字符串
     *
     * @param string ...$strings 要拼接的字符串
     *
     * @return string 拼接后的字符串
     */
    public static function concatenate(...$strings): string
    {
        return implode('', $strings);
    }

    /**
     * 分割字符串
     *
     * @param string $string    要分割的字符串
     * @param string $delimiter 分隔符
     *
     * @return array 分割后的字符串数组
     */
    public static function split(string $string, string $delimiter = ','): array
    {
        return explode($delimiter, $string);
    }

    /**
     * 查找子字符串位置
     *
     * @param string $haystack 原始字符串
     * @param string $needle   要查找的子字符串
     * @param int    $offset   起始搜索位置，默认为0
     *
     * @return int 子字符串的位置，未找到返回false
     */
    public static function find(string $haystack, string $needle, int $offset = 0)
    {
        $position = strpos($haystack, $needle, $offset);
        return ($position !== false) ? $position : false;
    }

    /**
     * 替换字符串中的部分字符
     *
     * @param string   $search  要被替换的字符串
     * @param string   $replace 替换后的字符串
     * @param string   $subject 原始字符串
     * @param int|null $count   替换次数，如果为null则替换所有匹配项
     *
     * @return string 替换后的字符串
     */
    public static function replace(string $search, string $replace, string $subject, ?int $count = null): string
    {
        return str_replace($search, $replace, $subject, $count);
    }

    /**
     * 转换字符串为大写
     *
     * @param string $string 原始字符串
     *
     * @return string 全部大写的字符串
     */
    public static function toUpperCase(string $string): string
    {
        return strtoupper($string);
    }

    /**
     * 转换字符串为小写
     *
     * @param string $string 原始字符串
     *
     * @return string 全部小写的字符串
     */
    public static function toLowerCase(string $string): string
    {
        return strtolower($string);
    }

    /**
     * 删除字符串首尾空白字符
     *
     * @param string $string 原始字符串
     *
     * @return string 删除空白字符后的字符串
     */
    public static function trim(string $string): string
    {
        return trim($string);
    }

    /**
     * 获取字符串长度
     *
     * @param string $string 原始字符串
     *
     * @return int 字符串长度
     */
    public static function length(string $string): int
    {
        return strlen($string);
    }

    /**
     * 截取字符串
     *
     * @param string   $string 原始字符串
     * @param int      $start  开始位置
     * @param int|null $length 截取长度，如果为null，则截取到字符串末尾
     *
     * @return string 截取后的字符串
     */
    public static function substring(string $string, int $start, ?int $length = null): string
    {
        if ($length === null) {
            return substr($string, $start);
        } else {
            return substr($string, $start, $length);
        }
    }

    /**
     * 去除字符串中的HTML标签
     *
     * @param string $string 含有HTML标签的字符串
     *
     * @return string 去除标签后的字符串
     */
    public static function stripHtmlTags(string $string): string
    {
        return strip_tags($string);
    }

    /**
     * 校验是否为邮箱格式
     *
     * @param string $email 需要校验的字符串
     *
     * @return bool 是否符合邮箱格式
     */
    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 校验是否为URL格式
     *
     * @param string $url 需要校验的字符串
     *
     * @return bool 是否符合URL格式
     */
    public static function isUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 计算字符串中某个字符出现的次数
     *
     * @param string $haystack 原始字符串
     * @param string $needle   目标字符
     *
     * @return int 字符出现的次数
     */
    public static function countCharacter(string $haystack, string $needle): int
    {
        return substr_count($haystack, $needle);
    }

    /**
     * 生成指定长度的随机字符串
     *
     * @param int    $length  字符串长度
     * @param string $charset 可选字符集，默认为字母和数字
     *
     * @return string 随机字符串
     * @throws Exception
     */
    public static function generateRandomString(int $length, string $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $result   = '';
        $maxIndex = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[random_int(0, $maxIndex)];
        }
        return $result;
    }

    /**
     * 根据微秒时间和随机数生成 10位 或者 11位的 uuid
     *
     * @param bool $useUpper 是否生成大写标识字符
     *                       true  : [0~9 + A~Z(不包含O)] 生成10位长度的uuid;
     *                       false : [0~9 + a~z + A~Z]   生成11位长度的uuid;
     *
     * @return string
     * @throws Exception
     */
    public static function uuid(bool $useUpper = false): string
    {
        $timeArr = explode(' ', microtime());
        $decimal = $timeArr[1] . str_pad(substr($timeArr[0], 2, 6), 6, '0', STR_PAD_RIGHT);
        $decimal .= $useUpper ? '' : sprintf("%03d", random_int(0, 999));
        // 转35进制(不包含大写字母O) 或者 转62进制
        $characters = $useUpper ? '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ' : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base       = strlen($characters);
        $result     = '';

        while ($decimal > 0) {
            $remainder = $decimal % $base;
            $result    = $characters[$remainder] . $result;
            $decimal   = intdiv($decimal, $base);
        }

        return (string)($result === '' ? 'A000000000' : $result);
    }


    /**
     * 将字符串转换为snake_case格式
     *
     * @param string $camelCaseStr 驼峰命名的字符串
     *
     * @return string 转换后的snake_case字符串
     */
    public static function camelToSnake(string $camelCaseStr): string
    {
        $snakeCaseStr = preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseStr);
        return strtolower($snakeCaseStr);
    }

    /**
     * 将字符串转换为CamelCase格式
     *
     * @param string $snakeCaseStr snake_case命名的字符串
     *
     * @return string 转换后的CamelCase字符串
     */
    public static function snakeToCamel(string $snakeCaseStr): string
    {
        $components = explode('_', $snakeCaseStr);
        return implode('', array_map('ucfirst', $components));
    }

    /**
     * 检查字符串是否只包含字母和数字
     *
     * @param string $str 需要检查的字符串
     *
     * @return bool 如果字符串只包含字母和数字则返回true，否则返回false
     */
    public static function isAlphaNumeric(string $str): bool
    {
        return ctype_alnum($str);
    }

    /**
     * 给定字符串左侧填充指定字符到指定长度
     *
     * @param string $str    原始字符串
     * @param int    $length 目标长度
     * @param string $padStr 填充字符，默认为空格
     *
     * @return string 填充后的字符串
     */
    public static function padLeft(string $str, int $length, string $padStr = ' '): string
    {
        return str_pad($str, $length, $padStr, STR_PAD_LEFT);
    }

    /**
     * 给定字符串右侧填充指定字符到指定长度
     *
     * @param string $str    原始字符串
     * @param int    $length 目标长度
     * @param string $padStr 填充字符，默认为空格
     *
     * @return string 填充后的字符串
     */
    public static function padRight(string $str, int $length, string $padStr = ' '): string
    {
        return str_pad($str, $length, $padStr, STR_PAD_RIGHT);
    }

    /**
     * 检查字符串是否为空或仅包含空白字符
     *
     * @param string $str 待检查的字符串
     *
     * @return bool 如果字符串为空或只含空白字符则返回true，否则返回false
     */
    public static function isEmpty(string $str): bool
    {
        return trim($str) === '';
    }

    /**
     * 判断字符串是否以指定前缀开始
     *
     * @param string $str    原始字符串
     * @param string $prefix 前缀字符串
     *
     * @return bool 如果字符串以指定前缀开始则返回true，否则返回false
     */
    public static function startsWith(string $str, string $prefix): bool
    {
        return str_starts_with($str, $prefix);
    }

    /**
     * 判断字符串是否以指定后缀结束
     *
     * @param string $str    原始字符串
     * @param string $suffix 后缀字符串
     *
     * @return bool 如果字符串以指定后缀结束则返回true，否则返回false
     */
    public static function endsWith(string $str, string $suffix): bool
    {
        $length = strlen($suffix);
        if ($length == 0) {
            return true;
        }
        return substr($str, -$length) === $suffix;
    }

    /**
     * 判断字符串的文件后缀是否在指定的数组中
     *
     * @param string $string
     * @param array  $extensionArray
     *
     * @return bool
     */
    public static function endsWithArr(string $string, array $extensionArray = []): bool
    {
        // 使用 pathinfo() 函数获取文件信息
        $file_info = pathinfo($string);
        // 获取文件后缀
        return in_array($file_info['extension'], $extensionArray);
    }

    /**
     * 重复字符串指定次数
     *
     * @param string $str   被重复的字符串
     * @param int    $times 重复次数
     *
     * @return string 重复后的字符串
     */
    public static function repeat(string $str, int $times): string
    {
        return str_repeat($str, $times);
    }

    /**
     * 字符串替换，支持正则表达式
     *
     * @param string       $pattern     正则表达式模式
     * @param array|string $replacement 替换后的字符串或数组（用于preg_replace_callback）
     * @param string       $subject     原始字符串
     *
     * @return string 替换后的字符串
     */
    public static function regexReplace(string $pattern, array|string $replacement, string $subject): string
    {
        return preg_replace($pattern, $replacement, $subject);
    }

    /**
     * 字符串按指定宽度自动换行
     *
     * @param string $str   原始字符串
     * @param int    $width 每行的最大宽度
     * @param string $break 用于换行的字符串，默认为"\n"
     *
     * @return string 自动换行后的字符串
     */
    public static function wordWrap(string $str, int $width, string $break = "\n"): string
    {
        return wordwrap($str, $width, $break, true);
    }

    /**
     * 计算字符串的Levenshtein编辑距离
     *
     * @param string $str1 第一个字符串
     * @param string $str2 第二个字符串
     *
     * @return int 编辑距离
     */
    public static function levenshteinDistance(string $str1, string $str2): int
    {
        return levenshtein($str1, $str2);
    }

    /**
     * 字符串全词匹配高亮
     *
     * @param string $text    原始文本
     * @param string $keyword 高亮关键词
     * @param string $tag     开始标签，默认为'<strong>'
     * @param string $endTag  结束标签，默认为'</strong>'
     *
     * @return string 高亮后的文本
     */
    public static function highlightWords(string $text, string $keyword, string $tag = '<strong>', string $endTag = '</strong>'): string
    {
        $pattern = '/' . preg_quote($keyword, '/') . '/i';
        return preg_replace($pattern, $tag . '$0' . $endTag, $text);
    }

    /**
     * 过滤字符串中的敏感词
     *
     * @param string $text           原始文本
     * @param array  $sensitiveWords 敏感词数组
     * @param string $replacement    敏感词替换后的字符串，默认为"*"
     *
     * @return string 过滤后的文本
     */
    public static function filterSensitiveWords(string $text, array $sensitiveWords, string $replacement = "*"): string
    {
        foreach ($sensitiveWords as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/i';
            $text    = preg_replace($pattern, str_repeat($replacement, strlen($word)), $text);
        }
        return $text;
    }

    /**
     * 将字符串中的HTML实体解码
     *
     * @param string $htmlEncodedStr HTML实体编码的字符串
     *
     * @return string 解码后的字符串
     */
    public static function htmlDecode(string $htmlEncodedStr): string
    {
        return htmlspecialchars_decode($htmlEncodedStr, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * 将字符串中的特殊字符转换为HTML实体
     *
     * @param string $str 原始字符串
     *
     * @return string 转换后的字符串
     */
    public static function htmlEncode(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * 字符串自然排序键生成器
     *
     * @param string $str 需要生成排序键的字符串
     *
     * @return string 排序键字符串
     */
    public static function naturalSortKey(string $str): string
    {
        // 自然排序算法，将数字作为整数对待，而非字符串
        $convert = function ($text) {
            $ret = '';
            $len = strlen($text);
            for ($i = 0; $i < $len; $i++) {
                $char = $text[$i];
                if (ctype_digit($char)) {
                    $ret .= intval($char);
                } else {
                    $ret .= strtolower($char);
                }
            }
            return $ret;
        };

        return $convert($str);
    }

    /**
     * 检查两个字符串是否相等，忽略大小写
     *
     * @param string $str1 第一个字符串
     * @param string $str2 第二个字符串
     *
     * @return bool 如果两个字符串（忽略大小写）相等则返回true，否则返回false
     */
    public static function equalsIgnoreCase(string $str1, string $str2): bool
    {
        return strtolower($str1) === strtolower($str2);
    }

    /**
     * 提取字符串中的所有数字并返回数组
     *
     * @param string $str 包含数字的字符串
     *
     * @return array 包含所有找到的数字的数组
     */
    public static function extractNumbers(string $str): array
    {
        preg_match_all('/\d+/', $str, $matches);
        return array_map('intval', $matches[0]);
    }

    /**
     * 清理字符串，去除前后空白及多余的空格
     *
     * @param string $str 待清理的字符串
     *
     * @return string 清理后的字符串
     */
    public static function normalizeSpace(string $str): string
    {
        return preg_replace('/\s+/', ' ', trim($str));
    }

    /**
     * 检查字符串是否包含指定的子序列，忽略大小写
     *
     * @param string $haystack 主字符串
     * @param string $needle   子序列
     *
     * @return bool 如果找到子序列则返回true，否则返回false
     */
    public static function containsIgnoreCase(string $haystack, string $needle): bool
    {
        return stripos($haystack, $needle) !== false;
    }

    /**
     * 计算字符串中特定字符或子串出现的次数
     *
     * @param string $str    目标字符串
     * @param string $search 要搜索的字符或子串
     *
     * @return int 出现的次数
     */
    public static function countOccurrences(string $str, string $search): int
    {
        return substr_count(strtolower($str), strtolower($search));
    }

    /**
     * 使用XPath从HTML字符串中提取数据
     *
     * @param string $html            HTML字符串
     * @param string $xpathExpression XPath表达式
     *
     * @return array 提取的数据数组
     */
    public static function extractFromHtmlByXPath(string $html, string $xpathExpression): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html); // 使用@抑制可能的警告，因为loadHTML可能遇到错误的HTML格式
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query($xpathExpression);

        $results = [];
        foreach ($nodes as $node) {
            $results[] = $node->nodeValue;
        }

        return $results;
    }

    /**
     * 将字符串从一种编码转换为另一种编码
     *
     * @param string $str          要转换的字符串
     * @param string $fromEncoding 原始编码
     * @param string $toEncoding   目标编码
     *
     * @return string 转换后的字符串
     */
    public static function convertEncoding(string $str, string $fromEncoding, string $toEncoding): string
    {
        return mb_convert_encoding($str, $toEncoding, $fromEncoding);
    }

    /**
     * 生成一个版本4的UUID
     *
     * @return string UUID字符串
     * @throws Exception
     */
    public static function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * 计算两个字符串的相似度（使用Jaccard相似度作为简单示例）
     *
     * @param string $str1 第一个字符串
     * @param string $str2 第二个字符串
     *
     * @return float 相似度分数，范围从0到1
     */
    public static function similarity(string $str1, string $str2): float
    {
        $set1 = count_chars($str1, 1);
        $set2 = count_chars($str2, 1);

        $intersection = array_intersect_key($set1, $set2);
        $union        = array_merge($set1, $set2);

        return count($intersection) / count($union);
    }

    /**
     * 将字符串按句子分割
     *
     * @param string $text 要分割的文本
     *
     * @return array 句子组成的数组
     */
    public static function splitIntoSentences(string $text): array
    {
        // 这里使用简单的.!?作为句子结束标志的假设，实际应用可能需要更复杂的逻辑
        return preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 检查字符串中是否包含至少一个中文字符
     *
     * @param string $str 待检查的字符串
     *
     * @return bool 是否包含中文字符
     */
    public static function containsChineseCharacter(string $str): bool
    {
        return preg_match('/[\x{4e00}-\x{9fff}]+/u', $str) > 0;
    }

    /**
     * 使用关联数组对字符串模板进行替换
     *  eg: $template = "Hello, {name}! Today is {day}.";
     *      $data = ['name' => 'Alice', 'day' => 'Monday'];
     *      echo StringUtils::templateReplace($template, $data) . "\n"; // 输出: Hello, Alice! Today is Monday.
     *
     * @param string $template     字符串模板，包含形如 {key} 的占位符
     * @param array  $replacements 关联数组，键为模板中的占位符名称，值为替换的内容
     *
     * @return string 替换后的字符串
     */
    public static function templateReplace(string $template, array $replacements): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($replacements) {
            $key = $matches[1];
            return $replacements[$key] ?? $matches[0];
        }, $template);
    }

    /**
     * 返回字符串的逆序
     *
     * @param string $str 原始字符串
     *
     * @return string 逆序后的字符串
     */
    public static function reverse(string $str): string
    {
        return strrev($str);
    }

    /**
     * 检查字符串是否符合给定的正则表达式模式
     *
     * @param string $str     要检查的字符串
     * @param string $pattern 正则表达式模式
     *
     * @return bool 如果字符串符合模式则返回true，否则返回false
     */
    public static function matchesPattern(string $str, string $pattern): bool
    {
        return preg_match($pattern, $str) === 1;
    }

    /**
     * 计算字符串中的单词数量
     *
     * @param string $text 待统计的文本
     *
     * @return int 单词数量
     */
    public static function countWords(string $text): int
    {
        $words = preg_split('/\s+/', trim($text));
        return count(array_filter($words, 'strlen'));
    }

    /**
     * 统计字符串中每个字符的出现次数
     *
     * @param string $str 待统计的字符串
     *
     * @return array 字符及其出现次数的映射
     */
    public static function characterFrequency(string $str): array
    {
        $frequency = array();
        $length    = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];
            if (!isset($frequency[$char])) {
                $frequency[$char] = 1;
            } else {
                $frequency[$char]++;
            }
        }
        return $frequency;
    }
}
