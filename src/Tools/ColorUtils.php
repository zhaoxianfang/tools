<?php

namespace zxf\Tools;

use InvalidArgumentException;

/**
 * 颜色工具类
 */
class ColorUtils
{
    /**
     * 将两个颜色值进行加法处理
     *
     * @param  string  $color1  十六进制颜色值，例如 "#FF0000"
     * @param  string  $color2  十六进制颜色值，例如 "#FFFF00"
     * @return string 结果的十六进制颜色值
     *
     * @throws InvalidArgumentException 如果输入的颜色值无效
     *
     * @example ColorUtils::addColors("#FF0000", "#FFFF00"); // 结果: "#FFFF00"
     */
    public static function addColors(string $color1, string $color2): string
    {
        return self::blendColors($color1, $color2, fn ($a, $b) => min($a + $b, 255));
    }

    /**
     * 将两个颜色值进行减法处理
     *
     * @param  string  $color1  十六进制颜色值，例如 "#00FF00"
     * @param  string  $color2  十六进制颜色值，例如 "#0000FF"
     * @return string 结果的十六进制颜色值
     *
     * @throws InvalidArgumentException 如果输入的颜色值无效
     *
     * @example ColorUtils::subtractColors("#00FF00", "#0000FF"); // 结果: "#00FF00"
     */
    public static function subtractColors(string $color1, string $color2): string
    {
        return self::blendColors($color1, $color2, fn ($a, $b) => max($a - $b, 0));
    }

    /**
     * 将两个颜色按指定比例混合
     *
     * @param  string  $color1  十六进制颜色值
     * @param  string  $color2  十六进制颜色值
     * @param  float  $ratio  混合比例，0.0 到 1.0 之间
     * @return string 结果的十六进制颜色值
     *
     * @throws InvalidArgumentException 如果比例无效
     *
     * @example ColorUtils::blendColorsWithRatio("#FF0000", "#FFFF00", 0.5); // 结果: "#FFFF00"
     */
    public static function blendColorsWithRatio(string $color1, string $color2, float $ratio = 0.5): string
    {
        if ($ratio < 0 || $ratio > 1) {
            throw new InvalidArgumentException('Ratio must be between 0 and 1.');
        }

        $rgb1 = self::hexToRgb($color1);
        $rgb2 = self::hexToRgb($color2);

        $r = (int) round($rgb1['r'] * (1 - $ratio) + $rgb2['r'] * $ratio);
        $g = (int) round($rgb1['g'] * (1 - $ratio) + $rgb2['g'] * $ratio);
        $b = (int) round($rgb1['b'] * (1 - $ratio) + $rgb2['b'] * $ratio);

        return self::rgbToHex($r, $g, $b);
    }

    /**
     * 调整颜色亮度
     *
     * @param  string  $color  十六进制颜色值
     * @param  float  $factor  亮度因子，通常在 -1 到 1 之间
     * @return string 调整后的十六进制颜色值
     *
     * @throws InvalidArgumentException 如果因子无效
     *
     * @example ColorUtils::adjustBrightness("#FF0000", 0.2); // 结果: "#FF3333"
     */
    public static function adjustBrightness(string $color, float $factor): string
    {
        if ($factor < -1 || $factor > 1) {
            throw new InvalidArgumentException('Brightness factor must be between -1 and 1.');
        }

        $rgb = self::hexToRgb($color);

        $r = (int) round(min(max($rgb['r'] + 255 * $factor, 0), 255));
        $g = (int) round(min(max($rgb['g'] + 255 * $factor, 0), 255));
        $b = (int) round(min(max($rgb['b'] + 255 * $factor, 0), 255));

        return self::rgbToHex($r, $g, $b);
    }

    /**
     * 调整颜色对比度
     *
     * @param  string  $color  十六进制颜色值
     * @param  float  $factor  对比度因子，通常大于 0
     * @return string 调整后的十六进制颜色值
     *
     * @throws InvalidArgumentException 如果因子无效
     *
     * @example ColorUtils::adjustContrast("#FF0000", 1.5); // 结果: "#FF0000"
     */
    public static function adjustContrast(string $color, float $factor): string
    {
        if ($factor <= 0) {
            throw new InvalidArgumentException('Contrast factor must be greater than 0.');
        }

        $rgb = self::hexToRgb($color);

        $r = (int) round(min(max((($rgb['r'] - 128) * $factor) + 128, 0), 255));
        $g = (int) round(min(max((($rgb['g'] - 128) * $factor) + 128, 0), 255));
        $b = (int) round(min(max((($rgb['b'] - 128) * $factor) + 128, 0), 255));

        return self::rgbToHex($r, $g, $b);
    }

    /**
     * 计算颜色亮度
     *
     * @param  string  $color  十六进制颜色值
     * @return float 亮度值，范围在 0 到 1 之间
     *
     * @example ColorUtils::getLuminance("#FF0000"); // 结果: 0.2126
     */
    public static function getLuminance(string $color): float
    {
        $rgb = self::hexToRgb($color);

        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        // 使用标准亮度公式
        $r = ($r <= 0.03928) ? ($r / 12.92) : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? ($g / 12.92) : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? ($b / 12.92) : pow(($b + 0.055) / 1.055, 2.4);

        return round(0.2126 * $r + 0.7152 * $g + 0.0722 * $b, 4);
    }

    /**
     * 将十六进制颜色值转换为 RGB 数组
     *
     * @param  string  $hex  十六进制颜色值
     * @return array 包含 'r', 'g', 'b' 的数组
     *
     * @throws InvalidArgumentException 如果颜色值无效
     *
     * @example ColorUtils::hexToRgb("#FF0000"); // 结果: ['r' => 255, 'g' => 0, 'b' => 0]
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            throw new InvalidArgumentException("Invalid hex color: $hex");
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * 将十六进制颜色值转换为 RGBA 数组
     *
     * @param  string  $hex  十六进制颜色值
     * @return array 包含 'r', 'g', 'b', 'a' 的数组
     *
     * @throws InvalidArgumentException 如果颜色值无效
     *
     * @example ColorUtils::hexToRgba("#FF000080"); // 结果: ['r' => 255, 'g' => 0, 'b' => 0, 'a' => 0.5]
     */
    public static function hexToRgba(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        if (strlen($hex) === 8) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $a = hexdec(substr($hex, 6, 2)) / 255;
        } elseif (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $a = 1;
        } else {
            throw new InvalidArgumentException("Invalid hex color: $hex");
        }

        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $a];
    }

    /**
     * 将 RGBA 颜色值转换为十六进制颜色值（6 位或 8 位）
     *
     * @param  int  $r  红色分量
     * @param  int  $g  绿色分量
     * @param  int  $b  蓝色分量
     * @param  float  $a  透明度（0 到 1 之间）
     * @return string 十六进制颜色值（6 位或 8 位）
     *
     * @throws InvalidArgumentException 如果 RGB 或透明度数值无效
     *
     * @example ColorUtils::rgbaToHex(255, 0, 0, 0.5); // 结果: "#FF000080"
     */
    public static function rgbaToHex(int $r, int $g, int $b, float $a = 1): string
    {
        self::validateRgb($r, $g, $b);
        self::validateAlpha($a);

        // 透明度处理
        $a = round($a * 255);

        // 如果透明度为 1，返回 6 位颜色；否则返回 8 位颜色
        if ($a === 255) {
            return sprintf('#%02X%02X%02X', $r, $g, $b);
        } else {
            return sprintf('#%02X%02X%02X%02X', $r, $g, $b, $a);
        }
    }

    /**
     * 调整颜色的透明度
     *
     * @param  string  $color  十六进制颜色值
     * @param  float  $alpha  透明度，范围从 0 到 1
     * @return string 调整后的十六进制颜色值（6 位或 8 位）
     *
     * @throws InvalidArgumentException 如果透明度无效
     *
     * @example ColorUtils::adjustAlpha("#FF0000", 0.5); // 结果: "#FF000080"
     */
    public static function adjustAlpha(string $color, float $alpha): string
    {
        self::validateAlpha($alpha);

        // 将颜色值转换为 RGBA
        $rgba = self::hexToRgba($color);

        return self::rgbaToHex($rgba['r'], $rgba['g'], $rgba['b'], $alpha);
    }

    /**
     * 内部函数：按指定的混合函数混合两个颜色
     *
     * @param  string  $color1  十六进制颜色值
     * @param  string  $color2  十六进制颜色值
     * @param  callable  $blendFunction  混合函数
     * @return string 结果的十六进制颜色值
     *
     * @example ColorUtils::blendColors("#FF0000", "#00FF00", fn($a, $b) => min($a + $b, 255)); // 结果: "#FFFF00"
     */
    public static function blendColors(string $color1, string $color2, callable $blendFunction): string
    {
        $rgb1 = self::hexToRgb($color1);
        $rgb2 = self::hexToRgb($color2);

        $r = $blendFunction($rgb1['r'], $rgb2['r']);
        $g = $blendFunction($rgb1['g'], $rgb2['g']);
        $b = $blendFunction($rgb1['b'], $rgb2['b']);

        return self::rgbToHex($r, $g, $b);
    }

    /**
     * 将 RGB 数值转换为十六进制颜色值
     *
     * @param  int  $r  红色分量
     * @param  int  $g  绿色分量
     * @param  int  $b  蓝色分量
     * @return string 十六进制颜色值
     *
     * @throws InvalidArgumentException 如果 RGB 数值无效
     *
     * @example ColorUtils::rgbToHex(255, 0, 0); // 结果: "#FF0000"
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        self::validateRgb($r, $g, $b);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * 计算两个颜色之间的相似度
     *
     * @param  string  $color1  十六进制颜色值
     * @param  string  $color2  十六进制颜色值
     * @return float 相似度值，范围从 0（完全不同）到 1（完全相同）
     *
     * @throws InvalidArgumentException 如果输入的颜色值无效
     *
     * @example ColorUtils::getColorSimilarity("#FF0000", "#FF8000"); // 结果: 0.89
     */
    public static function getColorSimilarity(string $color1, string $color2): float
    {
        $rgb1 = self::hexToRgb($color1);
        $rgb2 = self::hexToRgb($color2);

        // 计算欧几里得距离
        $distance = sqrt(pow($rgb1['r'] - $rgb2['r'], 2) + pow($rgb1['g'] - $rgb2['g'], 2) + pow($rgb1['b'] - $rgb2['b'], 2));

        // 最大可能的距离是 sqrt(255^2 * 3) = 441.67
        $maxDistance = sqrt(pow(255, 2) * 3);

        // 相似度 = 1 - (距离 / 最大距离)
        return 1 - ($distance / $maxDistance);
    }

    /**
     * 验证 RGB 数值的有效性
     *
     * @param  int  $r  红色分量
     * @param  int  $g  绿色分量
     * @param  int  $b  蓝色分量
     *
     * @throws InvalidArgumentException 如果 RGB 数值无效
     */
    private static function validateRgb(int $r, int $g, int $b): void
    {
        if ($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255) {
            throw new InvalidArgumentException('RGB values must be between 0 and 255.');
        }
    }

    /**
     * 验证透明度值的有效性
     *
     * @param  float  $a  透明度值
     *
     * @throws InvalidArgumentException 如果透明度值无效
     */
    private static function validateAlpha(float $a): void
    {
        if ($a < 0 || $a > 1) {
            throw new InvalidArgumentException('Alpha value must be between 0 and 1.');
        }
    }
}
