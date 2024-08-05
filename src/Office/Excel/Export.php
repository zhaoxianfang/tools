<?php

namespace zxf\Office\Excel;

use Exception;
use Maatwebsite\Excel\Excel as ExcelExtension;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use zxf\Office\Excel\Handle\ExportHandle;

/**
 * Excel 导出
 */
class Export
{
    protected array $data;
    protected array $header;

    protected string $sheetName = '表1';

    /**
     * 导出文件后缀
     *
     * @var string xlsx、xls、csv等
     */
    protected string $ext    = 'xlsx';
    protected array  $extMap = [
        'xlsx' => ExcelExtension::XLSX,
        'xls'  => ExcelExtension::XLS,
        'csv'  => ExcelExtension::CSV,
    ];

    // styles 回调函数
    protected $styleCallback = null;

    // 使用应用到多表
    protected bool $multiSheets = false;

    /**
     * 保存到哪个磁盘； eg: public、s3等
     *
     * @var string|null
     */
    protected string|null $storeDisk = null;

    public function __construct(array $data, array $header = [])
    {
        $this->data   = $data;
        $this->header = $header;
    }

    public static function init(array $data, array $header = [])
    {
        return new static($data, $header);
    }

    /**
     * 设置导出的数据
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置表头:第一行
     *
     * @param array $header
     *
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * 设置sheet名称
     *
     * @param string $sheetName
     *
     * @return $this
     */
    public function setSheetName(string $sheetName)
    {
        $this->sheetName = $sheetName;
        return $this;
    }

    /**
     * 设置导出文件后缀
     *
     * @param string $ext xlsx、xls、csv等
     *
     * @return $this
     * @throws Exception
     */
    public function setExt(string $ext = 'xlsx')
    {
        if (empty($this->extMap[$ext])) {
            throw new Exception('不支持的导出文件后缀');
        }
        $this->ext = $ext;
        return $this;
    }

    /**
     * 保存到哪个磁盘,下载到浏览器时设置为空
     *
     * @param string|null $diskName 文件系统disk名称 eg: public、s3等
     *
     * @return $this
     */
    public function setDisk(string $diskName = null)
    {
        $this->storeDisk = $diskName;
        return $this;
    }

    /**
     * 设置样式
     *      eg: ->setStyles(function ($sheet) {
     *          // 合并单元格 A1 到 C1
     *          $sheet->mergeCells('A1:C1');
     *
     *          // 设置单元格样式，例如水平居中
     *          $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal('center');
     *          // 获取最大行数
     *          $highestRow = $sheet->getHighestRow();
     *          // 设置 A 列为时间格式，从A3开始到最高行
     *          $sheet->getStyle("A3:A{$highestRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_TIME2);
     *
     *          // 设置 B4 单元格的样式为加粗、斜体和黄色背景
     *          $sheet->getStyle('B4')->applyFromArray([
     *              'font' => [
     *                  'bold'   => true, // 加粗
     *                  'italic' => true, // 斜体
     *                  'color'  => ['argb' => 'FF0000'], // 红色文字
     *              ],
     *              'fill' => [
     *                  'fillType' => 'solid',
     *                  'color'    => ['argb' => 'FFFF00'], // 黄色背景
     *              ],
     *          ]);
     *      })
     *
     * @param callable $callback
     */
    public function setStyles(callable $callback)
    {
        // 使用闭包来设置样式
        $this->styleCallback = function (Worksheet $sheet) use ($callback) {
            $callback($sheet);
        };
        return $this;
    }

    public function setMultiSheets(bool $flag = true)
    {
        $this->multiSheets = $flag;
        return $this;
    }

    /**
     * 进行下载
     */
    public function download(string $filename = '', mixed ...$args)
    {
        $filePath = (empty($filename) ? date('YmdHis') : $filename) . '.' . $this->ext;

        $export = new ExportHandle($this->data, $this->header);
        // sheet名称
        $this->sheetName && $export->sheetName = $this->sheetName;
        // 设置样式
        $this->styleCallback && $export->styleCallback = $this->styleCallback;

        // 使用应用到多表,只需要返回 ExportHandle 对象即可
        if ($this->multiSheets) {
            return $export;
        }

        if (!empty($this->storeDisk)) {
            return Excel::store($export, $filePath, $this->storeDisk, $this->extMap[$this->ext], ...$args);
        }
        return Excel::download($export, $filePath, $this->extMap[$this->ext], ...$args);
    }
}