<?php

namespace zxf\Office\Excel\Media;

use Closure;
use Exception;
use ZipArchive;

/**
 * 媒体数据处理类;
 *  eg:wps单元格中的图片处理
 */
class MediaHandle
{
    // id => imgPath
    private static array $idToImgMap = [];

    private static ZipArchive $zip;

    /**
     * 处理 excel 文件中的图片数据
     *
     * @param  string  $filePath  excel 文件路径
     * @param  array  $sheetData  需要处理的数组数据
     * @param  string|Closure  $savePath  图片保存路径或闭包处理
     * @param  string  $sheetName  sheet 表名
     * @param  int  $sheetIndex  sheet 表索引
     *
     * @throws Exception
     */
    public static function handleCellMediaFile(string $filePath, array &$sheetData, string|Closure $savePath, string $sheetName = 'sheet1', int $sheetIndex = 0): void
    {
        if (empty($sheetData) || empty($savePath)) {
            return;
        }
        if (! is_file($filePath)) {
            throw new Exception("文件不存在：$filePath");
        }

        // 先判断 二维数组 $sheetData 中是否包含 wps/office 图片数据
        $isCellMedia = collect($sheetData)
            ->filter(function ($rows, $key) {
                // 再遍历过滤行数据
                return array_filter($rows, function ($cell) {
                    return is_string($cell) && (str_starts_with($cell, '=_xlfn.DISPIMG') || str_starts_with($cell, '#VALUE!'));
                });
            });
        // 没有包含图片数据
        if ($isCellMedia->isEmpty()) {
            return;
        }

        $zip = new ZipArchive;
        if (! $zip->open($filePath)) {
            throw new Exception("无法打开文件：$filePath");
        }
        self::$zip = $zip;
        // 处理文档中的 ID 和 图片路径的映射
        if (self::isWps($zip)) {
            self::$idToImgMap = WpsHandle::handleIdPathMap($zip);
            WpsHandle::saveMediaAndReplaceImgPath($zip, $sheetData, $savePath, $sheetName, $sheetIndex);
        } else {
            self::$idToImgMap = OfficeHandle::handleIdPathMap($zip, $filePath);
            OfficeHandle::saveMediaAndReplaceImgPath($zip, $sheetData, $savePath, $sheetName, $sheetIndex);
        }
    }

    // 判断是否为WPS格式的Excel文件
    public static function isWps(ZipArchive $zip): bool
    {
        // 查找文件或文件夹
        return $zip->locateName('xl/cellimages.xml') !== false;
        // return $zip->locateName('xl/sharedStrings.xml') !== false;
    }

    // 判断是否为Microsoft：Office格式的Excel文件
    public static function isOffice(ZipArchive $zip)
    {
        // 查找文件或文件夹
        return $zip->locateName('xl/metadata.xml') !== false;
    }
}
