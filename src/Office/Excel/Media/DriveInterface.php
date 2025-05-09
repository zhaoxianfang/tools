<?php

namespace zxf\Office\Excel\Media;

use Closure;
use ZipArchive;

interface DriveInterface
{
    // 处理 表格 里面的 ID_xxx 和 图片路径的映射关系
    public static function handleIdPathMap(ZipArchive $zip, string|Closure $mediaSavePathOrFunc): array;

    // 处理表格中的图片路径
    public function saveMediaAndReplaceImgPath(array &$sheetData, string $sheetName = 'sheet1', int $sheetIndex = 1): void;
}
