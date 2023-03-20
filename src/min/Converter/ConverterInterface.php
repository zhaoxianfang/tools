<?php

namespace zxf\min\Converter;

/**
 * 转换文件路径。
 *
 */
interface ConverterInterface
{
    /**
     * Convert file paths.
     *
     * @param string $path The path to be converted
     *
     * @return string The new path
     */
    public function convert($path);
}
