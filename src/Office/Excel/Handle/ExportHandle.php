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
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
// 展示数字
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel 导出
 */
class ExportHandle extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private array $list = [];

    private array $headings = [];

    public string $sheetName = '表1';

    /**
     * styles 回调函数
     */
    public $styleCallback;

    public function __construct(array $data, array $head = [])
    {
        if (! class_exists(Excel::class)) {
            throw new Exception('依赖于excel，请先安装「composer require maatwebsite/excel」后再使用');
        }
        $this->list = $data;
        $this->headings = $head;
    }

    /**
     * 重写 DefaultValueBinder 的bindValue 方法，解决数字0在表格中不显示的问题
     */
    public function bindValue(Cell $cell, $value)
    {
        // 展示数字，特别是数字0在表格中不显示的情况
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        return $this->list;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @param  mixed  $row
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * sheet名称
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
        $highestRow = $sheet->getHighestRow();

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
