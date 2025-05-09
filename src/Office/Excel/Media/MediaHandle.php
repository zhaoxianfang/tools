<?php

namespace zxf\Office\Excel\Media;

use Closure;
use Exception;
use ZipArchive;
use zxf\Xml\XML2Array;

/**
 * 媒体数据处理类;
 *  eg:wps单元格中的图片处理
 */
class MediaHandle
{
    private ZipArchive $zip;

    // 图片保存路径或闭包处理
    private string|Closure $mediaSavePathOrFunc;

    // 处理通道 wps 、office
    private $handelChannel;

    // 文件路径
    private string $filePath;

    /**
     * @var object 对象实例
     */
    protected static object $instance;

    public static function init()
    {
        if (! isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * 初始化文件
     *
     * @param  string  $filePath  excel 文件路径
     * @param  string|Closure  $mediaSavePathOrFunc  图片保存路径或闭包处理
     *
     * @throws Exception
     */
    public function initFile(string $filePath, string|Closure $mediaSavePathOrFunc)
    {
        if (! is_file($filePath)) {
            throw new Exception("文件不存在：$filePath");
        }
        $this->filePath = $filePath;
        $this->mediaSavePathOrFunc = is_string($mediaSavePathOrFunc) ? rtrim($mediaSavePathOrFunc, '/').'/' : $mediaSavePathOrFunc;

        $zip = new ZipArchive;
        if (! $zip->open($filePath)) {
            throw new Exception("无法打开文件：$filePath");
        }
        $this->zip = $zip;

        $this->handelChannel = $this->isWps($zip) ? new WpsHandle : new OfficeHandle;

        // 处理文档中的 ID 和 图片路径的映射
        $this->handelChannel::handleIdPathMap($zip, $this->mediaSavePathOrFunc);

        return $this;
    }

    /**
     * 处理 excel 文件中的图片数据
     *
     * @param  array  $sheetData  需要处理的数组数据
     * @param  string  $sheetName  sheet 表名
     * @param  int  $sheetIndex  sheet 表索引
     */
    public function handleCellMediaFile(array &$sheetData, string $sheetName = 'sheet1', int $sheetIndex = 1): void
    {
        if (empty($sheetData) || empty($this->mediaSavePathOrFunc)) {
            return;
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

        // 处理文档中的 ID 和 图片路径的映射
        $this->handelChannel->saveMediaAndReplaceImgPath($sheetData, $sheetName, $sheetIndex);
    }

    // 判断是否为WPS格式的Excel文件
    public function isWps(ZipArchive $zip): bool
    {
        // 查找文件或文件夹是否存在
        // return $zip->locateName('xl/cellimages.xml') !== false;

        $ContentTypes = XML2Array::toArray($zip->getFromName('[Content_Types].xml'));
        // 判断 $firstContentType 字符串中是否包含 "extended-properties+xml" 字符串
        return ! empty($firstContentType = $ContentTypes['Override'][0]['@ContentType']) && str_contains($firstContentType, 'extended-properties+xml');
    }

    // 判断是否为Microsoft：Office格式的Excel文件
    public function isOffice(ZipArchive $zip)
    {
        $ContentTypes = XML2Array::toArray($zip->getFromName('[Content_Types].xml'));
        // 判断 $firstContentType 字符串中是否包含 "spreadsheetml.sheet.main+xml" 字符串
        return ! empty($firstContentType = $ContentTypes['Override'][0]['@ContentType']) && str_contains($firstContentType, 'spreadsheetml.sheet.main+xml');
    }

    public function __destruct()
    {
        ! empty($this->zip) && $this->zip->close();
    }
}
