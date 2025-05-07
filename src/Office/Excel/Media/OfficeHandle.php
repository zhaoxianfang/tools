<?php

namespace zxf\Office\Excel\Media;

use Closure;
use ZipArchive;

class OfficeHandle implements DriveInterface
{
    // id => imgPath
    private static array $idToImgMap = [];

    // 处理wps 里面的 ID_xxx 和 图片路径的映射关系
    public static function handleIdPathMap(ZipArchive $zip, string|Closure $filePath = ''): array
    {
        // TODO: 暂未实现 Office 的 excel 图片处理
        return [];
    }

    public static function saveMediaAndReplaceImgPath(ZipArchive $zip, array &$sheetData, string|Closure $savePath, string $sheetName = 'sheet1', int $sheetIndex = 0): void
    {
        // TODO: 暂未实现 Office 的 excel 图片处理
    }
}
