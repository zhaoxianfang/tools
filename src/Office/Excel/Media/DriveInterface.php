<?php

namespace zxf\Office\Excel\Media;

use Closure;
use ZipArchive;

interface DriveInterface
{
    // 处理 表格 里面的 ID_xxx 和 图片路径的映射关系
    public static function handleIdPathMap(ZipArchive $zip): array;

    // 处理表格中的图片路径
    public static function saveMediaAndReplaceImgPath(ZipArchive $zip, array &$sheetData, string|Closure $savePath): void;
}
