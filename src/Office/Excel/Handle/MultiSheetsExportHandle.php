<?php

namespace zxf\Office\Excel\Handle;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use zxf\Office\Excel\Export;

/**
 * Excel 多表 sheets 导出
 */
class MultiSheetsExportHandle implements WithMultipleSheets
{
    use Exportable;

    /**
     * 多表sheet的数据
     *
     * @var array
     *            eg:  [
     *            // sheet1 表格
     *            [
     *            'header'=>['列1','列2','列3'],
     *            'data'=>[
     *            [
     *            '1-1','1-2','1-3'
     *            ],[
     *            '2-1','2-2','2-3'
     *            ]
     *            ],
     *            'title' = '表格一'
     *            ],
     *            // sheet2 表格
     *            [
     *            ...
     *            ]
     *            ]
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $sheetData) {
            $export = new Export($sheetData['data'], $sheetData['header']);
            ! empty($sheetData['title']) && $export->setSheetName($sheetData['title']);
            $sheets[] = $export->setMultiSheets()->download();
        }

        return $sheets;
    }
}
