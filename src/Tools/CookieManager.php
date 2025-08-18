<?php

namespace zxf\Tools;

use InvalidArgumentException;

/**
 * Cookie管理
 *
 * @author zxf
 *
 * @date   2020年05月08日
 */
// 示例用法
// try {
//    $cookies1 = CookieManager::parseCookieFile('cookie1.txt');
//    $cookies2 = CookieManager::parseCookieFile('cookie2.txt');
//
//    $mergedCookies = CookieManager::mergeCookies(true, $cookies1, $cookies2, 'cookie3.txt');
//    print_r($mergedCookies);
//
//    if (CookieManager::saveCookiesToFile($mergedCookies, 'merged_cookies.txt')) {
//        echo "Cookie 文件合并成功！\n";
//    } else {
//        echo "合并失败！\n";
//    }
// } catch (Exception $e) {
//    echo "错误: " . $e->getMessage();
// }
class CookieManager
{
    /**
     * 解析 cookie 文件
     *
     * @param  string  $filePath  cookie 文件路径
     * @return array 解析后的 cookie 数组
     *
     * @throws InvalidArgumentException
     */
    public static function parseCookieFile(string $filePath): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException("文件不存在或不可读: {$filePath}");
        }

        $cookies = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || trim($line) === '') {
                continue;
            }

            $parts = preg_split("/\s+/", $line, 7);

            if (count($parts) === 7) {
                $cookie = [
                    'domain' => strtolower($parts[0]),
                    'flag' => $parts[1] === 'TRUE',
                    'path' => $parts[2],
                    'secure' => $parts[3] === 'TRUE',
                    'expiration' => (int) $parts[4],
                    'name' => $parts[5],
                    'value' => $parts[6],
                ];
                $uniqueKey = hash('sha256', implode('|', [$cookie['domain'], $cookie['path'], $cookie['name']]));
                $cookies[$uniqueKey] = $cookie;
            }
        }

        return $cookies;
    }

    /**
     * 通用合并 cookie 方法，支持多个 cookie 数组或文件路径
     *
     * @param  bool  $overwrite  是否覆盖已有 cookie (默认 true)
     * @param  array|string  ...$sources  多个 cookie 数组或文件路径
     * @return array 合并后的 cookie 数组
     */
    public static function mergeCookies(bool $overwrite = true, ...$sources): array
    {
        $mergedCookies = [];
        foreach ($sources as $source) {
            $cookies = is_array($source) ? $source : self::parseCookieFile($source);
            foreach ($cookies as $key => $cookie) {
                if ($overwrite || ! isset($mergedCookies[$key])) {
                    $mergedCookies[$key] = $cookie;
                }
            }
        }

        return array_filter($mergedCookies, fn ($cookie) => $cookie['expiration'] === 0 || $cookie['expiration'] > time());
    }

    /**
     * 追加 cookie
     *
     * @param  array  $cookies  原有的 cookie 数组
     * @param  array  $newCookie  需要添加的 cookie 数据
     * @param  bool  $overwrite  是否覆盖已有的 cookie
     * @return array 追加后的 cookie 数组
     */
    public static function addCookie(array $cookies, array $newCookie, bool $overwrite = true): array
    {
        $uniqueKey = hash('sha256', implode('|', [$newCookie['domain'], $newCookie['path'], $newCookie['name']]));
        if ($overwrite || ! isset($cookies[$uniqueKey])) {
            $cookies[$uniqueKey] = $newCookie;
        }

        return $cookies;
    }

    /**
     * 保存 cookie 数组到文件
     */
    public static function saveCookiesToFile(array $cookies, string $outputFile): bool
    {
        // usort($cookies, fn($a, $b) => strcmp($a['domain'], $b['domain']) ?: strcmp($a['path'], $b['path']));

        $date = date('Y-m-d H:i:s');
        $output = "# Netscape HTTP Cookie File\n# This file was created by PHP\n# date: {$date}\n\n";

        foreach ($cookies as $cookie) {
            $output .= sprintf(
                "%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
                $cookie['domain'],
                $cookie['flag'] ? 'TRUE' : 'FALSE',
                $cookie['path'],
                $cookie['secure'] ? 'TRUE' : 'FALSE',
                $cookie['expiration'],
                $cookie['name'],
                $cookie['value']
            );
        }

        return file_put_contents($outputFile, $output) !== false;
    }

    /**
     * 清除 cookie 文件内容
     *
     * @param  string  ...$filePaths  cookie 文件路径(如果有多个文件，就传多个参数：eg: cleanUp('cookie1.txt')、 cleanUp('cookie1.txt',
     *                                'cookie2.txt'))
     */
    public static function cleanUp(string ...$filePaths): bool
    {
        foreach ($filePaths as $filePath) {
            $result = file_put_contents($filePath, '');
            if ($result === false) {
                return false;
            }
        }

        return true;
    }
}
