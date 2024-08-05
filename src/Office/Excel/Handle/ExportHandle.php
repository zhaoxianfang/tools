<?php

namespace zxf\Office\Excel\Handle;

use Exception;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel 导出
 */
class ExportHandle implements WithHeadings, WithMapping, FromArray, ShouldAutoSize, WithTitle, WithStyles
{

    private array $list      = [];
    private array $headings  = [];
    public string $sheetName = '表1';

    /**
     * styles 回调函数
     */
    public $styleCallback;

    public function __construct(array $data, array $head = [])
    {
        if (!class_exists(Excel::class)) {
            throw new Exception('依赖于excel，请先安装「composer require maatwebsite/excel」后再使用');
        }
        $this->list     = $data;
        $this->headings = $head;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->list;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * sheet名称
     *
     * @return string
     */
    public function title(): string
    {
        return $this->sheetName;
    }

    /**
     * 设置样式；例如跨行跨列等
     */
    public function styles(Worksheet $sheet)
    {
        // =============================
        // 默认设置整表格式都为文本格式  开始
        // =============================
        // 获取最大列号和最大行号
        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        // 设置整个工作表为文本格式（从左上角A1 到最大的右下角）
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        // =============================
        // 默认设置整表格式都为文本格式  结束
        // =============================

        // 调用外部传入的样式回调函数
        if (is_callable($this->styleCallback)) {
            call_user_func($this->styleCallback, $sheet);
        }

        // 返回默认样式
        return [];

//        // 合并单元格 A1 到 C1
//        $sheet->mergeCells('A1:C1');
//
//        // 设置单元格样式，例如水平居中
//        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal('center');
//
//        return [
//            // 设置 A1 单元格的样式
//            1 => ['font' => ['bold' => true, 'size' => 14]],
//        ];
    }

}