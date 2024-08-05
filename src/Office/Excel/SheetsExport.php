<?php

namespace zxf\Office\Excel;

use Maatwebsite\Excel\Facades\Excel;
use zxf\Office\Excel\Handle\MultiSheetsExportHandle;

/**
 * 导出多表
 */
class SheetsExport
{
    /**
     * 导出多表
     *
     * @param array  $sheetData
     * @param string $filename 文件名 eg 'test.xlsx','test.csv'
     * @param mixed  ...$args
     */
    public static function download(array $sheetData, string $filename, mixed ...$args)
    {
        return Excel::download(new MultiSheetsExportHandle($sheetData), $filename, ...$args);
    }
}