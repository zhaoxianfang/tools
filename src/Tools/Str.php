<?php

namespace zxf\Tools;

class Str
{
    /**
     * 判断字符串的文件后缀是否在指定的数组中
     *
     * @param string $string
     * @param array  $extensionArray
     *
     * @return bool
     */
    public static function endsWith(string $string, array $extensionArray = []): bool
    {
        // 使用 pathinfo() 函数获取文件信息
        $file_info = pathinfo($string);
        // 获取文件后缀
        return in_array($file_info['extension'], $extensionArray);
    }
}