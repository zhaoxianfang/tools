# 办公文档

## Excel

### 文档

```
文档：https://laravel-excel.com/
github：https://github.com/SpartnerNL/Laravel-Excel
```

### 安装

> Laravel-Excel

```
composer require maatwebsite/excel
```

### 导出单表

```
$header = ['列1', '列2', '列3', '列4'];
$data   = [
    [
        '列1' => '数据1',
        '列2' => '数据2',
        '列3' => '数据3',
        '列4' => '数据4',
    ],
    [
        '列1' => '数据1',
        '列2' => '数据2',
        '列3' => '数据3',
        '列4' => '数据4',
    ],
    [
        '列11' => '12345678901234567890',
        '列21' => '12345678901234567891',
        '列03' => '12345678901234567892',
        '列04' => '12345678901234567893',
    ],
];
$export = \zxf\Office\Excel\Export::init($data, $header);
return $export->setSheetName('表1')
    ->setStyles(function ($sheet) {
        // 合并单元格 A1 到 C1
        $sheet->mergeCells('A1:C1');
        // 再设置居中
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // 设置单元格样式，例如水平居中
        // 设置 B4 单元格的样式为加粗、斜体和黄色背景
        $sheet->getStyle('B4')->getAlignment()->setHorizontal('center')->applyFromArray([
            'font' => [
                'bold'   => true, // 加粗
                'italic' => true, // 斜体
                'color'  => ['argb' => 'FF0000'], // 红色文字
            ],
            'fill' => [
                'fillType' => 'solid',
                'color'    => ['argb' => 'FFFF00'], // 黄色背景
            ],
        ]);
    })
    ->download('表_'.date('YmdHis'));
```

### 导出多表

```
$sheetData = [
    [
        'header'  => ['列1', '列2', '列3', '列4'],
        'data'  => [
            ['数据1', '数据2', '数据3', '数据4'],
            ['数据1', '数据2', '数据3', '数据4'],
        ],
        'title' => '表1',
    ],
    [
        'header'  => ['列1', '列2', '列3', '列4', '列5'],
        'data'  => [
            [
                '12345678901234567890',
                '12345678901234567891',
                '12345678901234567892',
                '12345678901234567893',
                '12345678901234567894',
            ],
            [
                '22345678901234567890',
                '22345678901234567891',
                '22345678901234567892',
                '22345678901234567893',
                '22345678901234567894',
            ],
            [
                '32345678901234567890',
                '32345678901234567891',
                '32345678901234567892',
                '32345678901234567893',
                '32345678901234567894',
            ],
            [
                '42345678901234567890',
                '42345678901234567891',
                '42345678901234567892',
                '42345678901234567893',
                '42345678901234567894',
            ],
        ],
        'title' => '表2',
    ],
];
return \zxf\Office\Excel\SheetsExport::download($sheetData, $filename = '多sheet表.xlsx');
```

### 导入EXCEL为数组

```
$file = $request->file('file');

// $file 参数可以是$request->file('xxx'),也可以是文件路径 /path/to/file.xlsx
$export = \zxf\Office\Excel\Import::init($file);

$export->validate(function () {
    $rule     = [
        'file' => 'required|file|max:2048|mimes:xlsx,xls,csv', // 文件最大2MB，仅限 xlsx, xls,csv 格式
    ];
    $messages = [
        'file.required' => '文件不能为空',
        'file.max'      => '文件最大1MB',
    ];
    return [$rule, $messages];
});

// 是否使用Excel列名作为数组的键名,默认为false，如果调用了setColumnMapping()方法，会自动设置为true
$export->useExcelColumnName(true);

// 使用列名映射 把A列映射为name，B列映射为email, 使用字段映射时没有设置映射的列会被忽略
$export->setColumnMapping([
    'A' => 'name', 
    'B' => 'email'
]);

return response()->json(['data' => $export->toArray()]);
```