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

## Word

### 文档

```
文档：https://phpoffice.github.io/PHPWord/
github：https://github.com/PHPOffice/PHPWord
```

### 安装

```
composer require phpoffice/phpword
```

### 使用

```
use zxf\Office\Word\Word;


$imgPath  = '/Users/linian/Pictures/1.jpeg';
$savePath = '/Users/linian/Desktop/document_write.docx';
// 创建文档并添加内容

$filePath = '/Users/linian/Desktop/document1.docx';
// 创建 Write 类的实例,传入$filePath就会加载$filePath文件，不传就新建一个对象
$write = new Word($filePath);

// 添加标题
$write->setTitle('文档标题', [
    'name'  => 'Arial',
    'size'  => 16,
    'bold'  => true,
    'color' => '0000FF',
]);

$write->addText('加粗文字：', ['bold' => true]);

// 添加文本
$write->addText('这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本。', [
    'name'  => 'Arial',
    'size'  => 12,
    'color' => '000000',
], [
    'align'       => 'left',
    'spaceBefore' => 10,
    'spaceAfter'  => 10,
]);

// 添加带下划线的文本
$write->addTextWithUnderline('这是带下划线的文本。', [
    'name'  => 'Arial',
    'size'  => 12,
    'color' => 'FF0000',
]);
$write->addText('这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本这是普通文本。', [
    'name'  => 'Arial',
    'size'  => 12,
    'color' => '000000',
]);

// 替换文本
$write->replaceText('普通文本', '替换后的文本');

// 添加有序列表
$write->addOrderedList([
    '第一项',
    '第二项',
    '第三项',
]);

// 添加无序列表
$write->addUnorderedList([
    '第一项',
    '第二项',
    '第三项',
]);

// 添加表格
$write->addTable('center', function ($table) use ($imgPath) {
    $table->addRow(400)
        ->addColSpanCell(2000, '跨两列的单元格', 2) // 添加跨两列的单元格
        ->addCell(1000, '右侧单元格')  // 普通单元格
        ->addRow()
        ->addCell(1000, '单元格 1')  // 普通单元格
        ->addCell(1000, '单元格 2')  // 普通单元格
        ->addCell(1000, '单元格 3')  // 普通单元格
        ->addRow()
        ->addRowSpanCell(1000, '跨两行', 2) // 添加跨两行的单元格
        ->addImageCell(1000, $imgPath, [
            'width'     => 50,
            'height'    => 50,
            'alignment' => 'center',
        ])
        ->addRowSpanCell(1000, '单元格 4', 2)
        ->addRow()
        ->addCell(1000, '单元格 5')
        ->addRow()
        ->addCell(1000, '单元格 6')
        ->addCell(1000, '单元格 x')
        ->addCell(1000, '单元格 x-1')
        ->addRow()
        ->addCell(1000, '单元格 7')
        ->addColSpanCell(2000, '单元格 8', 2) // 跨列单元格
    ;
});

// 添加统一的页眉
$write->addHeader('这是页眉内容');

// 添加统一的页脚
$write->addFooter('这是页脚内容');

// 或者调用自定义闭包回调操作 奇偶页页眉
$write->customCall(function ($phpWord, $section) {
    // 开启设置 奇偶不同的页眉
    $phpWord->getSettings()->setEvenAndOddHeaders(true);

    $style = array('alignment' => 'center'); // 设置页眉内容居中对齐

    // 默认页和奇数页
    $headerOdd = $section->addHeader();
    $headerOdd->addText('这是奇数页页眉', null, $style);
    // 偶数页页眉
    $headerEven = $section->addHeader('even');
    $headerEven->addText('这是偶数页页眉', null, $style);

    // 页脚和页眉设置相同
});

// 插入页码
$write->addPageNumber('center');

// 本页设置为两列
$write->setColsNum(2);
        
// 设置文本背景色
$write->setTextBackgroundColor('#FFFF00');

$write->addText('这是文本这是文本这是文本这是文本');
$write->addBr(3);
$write->addText('这是文本这是文本这是文本这是文本');


// 统一本页段落间距
$write->setParagraphSpacing(90, 90, 40);

$write->addPaper();
$write->addText('这是文本这是文本这是文本这是文本');

$write->addLink('http://weisifang.com', '测试链接');

// 添加图片
$write->addImage($imgPath, [
    'width'  => 200,
    'height' => 150,
    'align'  => 'center',
]);

// 调用PHPWord的其他操作
// 回调参数
// $phpWord: 当前文档对象
// $section: 当前页文档
// $word: 当前Word类
$write->customCall(function ($phpWord, $section, $word) {
    // 添加折线图
    $categories = array('A', 'B', 'C', 'D', 'E');
    $series     = array(1, 3, 2, 5, 4);
    // https://phpoffice.github.io/PHPWord/usage/styles/chart.html
    $section->addChart('line', $categories, $series, [
            'width'             => $word->cmToEmu(8), // 8 cm,只能使用EMU 单位
            'height'            => $word->cmToEmu(6), // 6 cm,只能使用EMU 单位
            '3d'                => false,
            'title'             => '折线图',
            'showLegend'        => false,
            'gridX'             => true, // 显示Y网格
            'gridY'             => true, // 显示Y网格
            'showAxisLabels'    => true,
            'categoryAxisTitle' => 'xxx', // X 轴标题
            'valueAxisTitle'    => 'yyy', // Y 轴标题
        ]
    );

    // 添加CheckBox
    $section->addCheckBox('CheckBox Name', 'CheckBox Text', [
        'color'   => 'FF0000',
        'bgColor' => '00FF00',
        'bold'    => false,
        'size'    => 22,
    ], []);

});

// 统一本页段落间距
$write->setParagraphSpacing(90, 90, 40);

// 保存文档到文件
$write->save($savePath);

// 直接下载文档
$write->download('example_download.docx');
```

### 模板替换

```
use zxf\Office\Word\Template;

$docPath = '/Users/linian/Desktop/document.docx';

// 创建 Template 类的实例
$template = new Template($docPath);

// 替换的内容在模板中是使用${}括起来的,在调用替换时${}是可以省略的，eg: ${name}、name 都可以
$template->replaceText([
    '${name}'    => '张三',
    '${content}' => '欢迎光临',
    'by_author'  => '威四方',
]);

$template->replaceImage('logo', [
    'path'   => '/Users/linian/Pictures/1.jpeg',
    'width'  => 100, // 设置图片宽度
    'height' => 100, // 设置图片高度
    'ratio'  => true,  // 保持图片比例
]);

// 方式一：保存
$template->save('/path/example_download.docx');

// 方式二：直接下载文档
$template->download('example_download.docx');

// 方式三：保存并下载文档
$template->saveAndDownload('/Users/linian/Desktop/document_download.docx');
```