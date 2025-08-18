<?php

namespace zxf\Office\Excel\Media;

use Closure;
use DOMDocument;
use Exception;
use ZipArchive;
use zxf\Tools\Str;
use zxf\Xml\XML2Array;

class WpsHandle implements DriveInterface
{
    // id => imgPath
    private static array $idToImgMap = [];

    private static ZipArchive $zip;

    // 图片保存路径或闭包处理
    private static string|Closure $mediaSavePathOrFunc;

    // 处理wps 里面的 ID_xxx 和 图片路径的映射关系
    public static function handleIdPathMap(ZipArchive $zip, string|Closure $mediaSavePathOrFunc): array
    {
        self::$zip = $zip;
        self::$mediaSavePathOrFunc = $mediaSavePathOrFunc;

        $imgsXml = $zip->getFromName('xl/cellimages.xml');
        if (! $imgsXml) {
            // 没有图片
            self::$idToImgMap = [];

            return [];
        }
        // id => rid
        $xmlImgArray = XML2Array::toArray($imgsXml);
        $xmlImgMaps = self::getCellImagesMap($xmlImgArray);

        if (empty($xmlImgArray) || empty($xmlImgMaps)) {
            // 没有图片
            self::$idToImgMap = [];
        } else {
            //
            $imgsXmlRels = $zip->getFromName('xl/_rels/cellimages.xml.rels');
            if (! $imgsXmlRels) {
                throw new Exception('文件解析失败~');
            }
            $dom = new DOMDocument;
            $dom->loadXML($imgsXmlRels);

            $xmlArr = XML2Array::toArray($dom->saveXML($dom->documentElement));

            $ridMap = [];
            foreach ($xmlArr['Relationship'] as $item) {
                $ridMap[$item['@Id']] = $item['@Target'];
            }

            // 遍历 $xmlImgMaps 和 $ridMap，将 $ridMap 的值赋给 $idToImgMap
            foreach ($xmlImgMaps as $id => $rid) {
                if (! empty($ridMap[$rid])) {
                    self::$idToImgMap[$id] = $ridMap[$rid];
                }
            }
        }

        return self::$idToImgMap;
    }

    // 处理图片，并替换图片路径
    public function saveMediaAndReplaceImgPath(array &$sheetData, string $sheetName = 'sheet1', int $sheetIndex = 1): void
    {
        $zip = self::$zip;
        $savePath = self::$mediaSavePathOrFunc;

        foreach ($sheetData as $line => $rowData) {
            foreach ($rowData as $colKey => $colData) {
                if (! empty($colData) && str_starts_with($colData, '=_xlfn.DISPIMG')) {
                    // 获取 DISPIMG 的 ID
                    // $pattern = '/ID_[A-Z0-9]{32}/';
                    $pattern = '/ID_[^\s"]+/';

                    // 使用 preg_match 函数查找匹配项
                    if (preg_match($pattern, $colData, $matches)) {
                        // 输出匹配的ID
                        $imgId = $matches[0];
                    } else {
                        throw new Exception('DISPIMG 中没有找到想要匹配的ID');
                    }

                    // 读取 $imgId 对应的图片并保存为本地图片
                    $mediaPath = 'xl/'.self::$idToImgMap[$imgId];
                    $imageData = $zip->getFromName($mediaPath);

                    if ($imageData) {
                        // 这里可以自定义更智能的匹配逻辑，例如：
                        // 如果 WPS 把 ID 写在文件名中可以通过 strpos($mediaPath, $imgId)
                        // 但如果没有就只能默认第一张为该 ID 对应的图像

                        if (is_string($savePath)) {
                            // 直接保存文件
                            $sourceImage = $savePath.$colKey.$line.'_'.Str::random(6).'_'.basename($mediaPath);
                            file_put_contents($sourceImage, $imageData);
                            $sourceImage = realpath($sourceImage); // 真实路径
                        } elseif (is_callable($savePath)) {
                            // 由用户自定义函数处理图片数据
                            /**
                             * @param  string  $imageData  二进制文件流数据
                             * @param  string  $mediaPath  文件名称
                             * @param  string  $colKey  列索引
                             * @param  int  $line  行索引
                             * @param  string  $sheetName  工作表名称
                             * @param  int  $sheetIndex  工作表索引
                             */
                            $sourceImage = $savePath($imageData, $mediaPath, $colKey, $line, $sheetName, $sheetIndex);
                            if (! is_string($sourceImage)) {
                                // 如果返回的 $sourceImage 不是字符串，则抛出异常
                                throw new Exception('setMediaSavePathOrFunc 处理文件后没有返回字符串路径/地址');
                            }
                        }
                        $sheetData[$line][$colKey] = $sourceImage;
                    }
                }
            }
        }
    }

    /**
     * 上传文件
     *
     * @throws Exception
     */
    public static function uploadImg(string $file = ''): string
    {
        // 获取 $file 的真实路径
        $file = realpath($file);

        return $file;
    }

    // 获取图片 ID->rid 的映射关系
    private static function getCellImagesMap(array $cellImagesArr): array
    {
        $map = [];
        if (empty($cellImagesArr)) {
            return $map;
        }
        try {
            foreach ($cellImagesArr['cellImage'] as $key => $xmlItem) {
                if (! empty($id = $xmlItem['pic']['nvPicPr']['cNvPr']['@name'])) {
                    $map[$id] = $xmlItem['pic']['blipFill']['blip']['@embed'];
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $map;
    }
}
