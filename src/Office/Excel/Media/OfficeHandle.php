<?php

namespace zxf\Office\Excel\Media;

use Closure;
use Exception;
use ZipArchive;
use zxf\Tools\Str;
use zxf\Xml\XML2Array;

class OfficeHandle implements DriveInterface
{
    /**
     * id => imgTarget
     *
     * @example [
     *              '1'=>[ // sheet1.xml
     *                  "0" => [ // 0开始的行号
     *                      "A" => "../media/image1.png" // 列=>图片路径
     *                      "B" => "../media/image2.png" // 列=>图片路径
     *                  ]
     *               ]
     *          ]
     */
    private static array $idToImgMap = [];

    private static ZipArchive $zip;

    // 图片保存路径或闭包处理
    private static string|Closure $mediaSavePathOrFunc;

    // 是否存在 richValueRel.xml.rels 文件 ; 用来解析单元格中的图片路径
    private static bool $hasRelsFile = false;

    // 处理wps 里面的 ID_xxx 和 图片路径的映射关系
    public static function handleIdPathMap(ZipArchive $zip, string|Closure $mediaSavePathOrFunc): array
    {
        self::$zip = $zip;
        self::$mediaSavePathOrFunc = $mediaSavePathOrFunc;

        // $zip 遍历 xl/worksheets/ 目录下的所有 sheet数字.xml文件 ；例如：xl/worksheets/sheet1.xml
        $worksheetFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('#xl/worksheets/sheet(\d+)\.xml#', $filename, $matches)) {
                $sheetId = $matches[1];
                $worksheetFiles[$sheetId] = $filename;
            }
        }
        $sheetsData = [];
        foreach ($worksheetFiles as $index => $sheetFilename) {
            $sheetsData[$index] = []; // 初始化 sheet$index 表
            // 读取表格数据：例如：xl/worksheets/sheet1.xml
            $sheetTableData = XML2Array::toArray($zip->getFromName($sheetFilename));

            // 遍历出sheetData 里面的媒体文件数据
            $rows = $sheetTableData['sheetData']['row'];
            foreach ($rows as $row) {
                // 判断 $row['c'] 的 第一个键名 是数字还是字符串
                if (is_string(array_key_first($row['c']))) {
                    $row['c'] = [$row['c']];
                }

                foreach ($row['c'] as $cell) {
                    // 媒体类型
                    if ($cell['@t'] == 'e' && $cell['v'] == '#VALUE!') {
                        // $sheetsData[$index][$cell['@r']] = [
                        //     'rid_index' => $cell['@vm'], // 可能是 图片的索引；例如:$cell['@vm']的 3 对应 richValueRel.xml.rels 里面的 rId3
                        //     'location' => $cell['@r'], // sheet 表中的位置；例如 ： D1
                        //     'row_index' => $row['@r'], // sheet 表中从1开始的行号
                        // ];

                        // $cell['@r'] 的格式是 字母+数字的组合(例如A1，DB12)，需要删除 $cell['@r'] 中的数字部分
                        $column = preg_replace('#\d+#', '', $cell['@r']);
                        // $sheetsData[sheet Id][0 开始的行号][列名：例如D] = rId的数字部分;
                        $sheetsData[$index][$row['@r'] - 1][$column] = $cell['@vm'];
                    }
                }
            }
        }
        // 判断文件是否存在
        $relsFile = 'xl/richData/_rels/richValueRel.xml.rels';

        self::$hasRelsFile = $zip->locateName($relsFile) !== false;
        // 检查文件或目录是否存在
        if (self::$hasRelsFile) {
            // echo '文件或目录存在';
            // 解析表值的对应关系
            $rels = XML2Array::toArray($zip->getFromName($relsFile));

            // 对应关系 和图片映射
            foreach ($sheetsData as $sheetIndex => $sheetData) {
                foreach ($sheetData as $line => $columns) {
                    foreach ($columns as $column => $value) {
                        // 遍历 $rels
                        foreach ($rels['Relationship'] as $rel) {
                            if ($rel['@Id'] == 'rId'.$value) {
                                $sheetsData[$sheetIndex][$line][$column] = $rel['@Target'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        self::$idToImgMap = $sheetsData;

        return $sheetsData;
    }

    public function saveMediaAndReplaceImgPath(array &$sheetData, string $sheetName = 'sheet1', int $sheetIndex = 1): void
    {
        if (! self::$hasRelsFile) {
            return;
        }

        $zip = self::$zip;
        $savePath = self::$mediaSavePathOrFunc;

        foreach (self::$idToImgMap[$sheetIndex] as $line => $columns) {
            foreach ($columns as $column => $imgPath) {

                $imageData = $zip->getFromName('xl/'.trim($imgPath, '../'));
                if ($imageData) {
                    if (is_string($savePath)) {
                        // 直接保存文件
                        $sourceImage = $savePath.$column.$line.'_'.Str::random(6).'_'.basename($imgPath);
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
                        $sourceImage = $savePath($imageData, $imgPath, $column, $line, $sheetName, $sheetIndex);
                        if (! is_string($sourceImage)) {
                            // 如果返回的 $sourceImage 不是字符串，则抛出异常
                            throw new Exception('setMediaSavePathOrFunc 处理文件后没有返回字符串路径/地址');
                        }
                    }
                    $sheetData[$line][$column] = $sourceImage;
                }
            }
        }
    }
}
