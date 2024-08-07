<?php

namespace zxf\Office\Word;


use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Paragraph as ParagraphStyle;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * 读写Word 文档
 */
class Word
{
    private $phpWord;
    private $section;
    private $table; // 保存当前表格对象的引用
    private $header;
    private $footer;
    public  $rowSpans; // 记录跨行的单元格
    public  $currentRowSpanCells; // 当前行跨行单元格的信息

    public function __construct($file = null)
    {
        if (!empty($file) && is_file($file)) {
            // 加载现有的 Word 文档
            $this->phpWord = IOFactory::load($file);

            // 遍历文档中的所有段落
            // $sections = $this->phpWord->getSections();
        } else {
            $this->phpWord = new PhpWord();
        }


        $this->section  = $this->phpWord->addSection();
        $this->table    = null; // 初始化为 null
        $this->header   = null;
        $this->footer   = null;
        $this->rowSpans = [];


    }

    /**
     * 添加文字到文档中
     *
     * @param string $text             文字内容
     * @param array  $fontOptions      字体样式选项（可选），包括字号、颜色等
     * @param array  $paragraphOptions 段落样式选项（可选），包括对齐方式、缩进等
     */
    public function addText($text, $fontOptions = [], $paragraphOptions = [])
    {
        $this->section->addText($text, $fontOptions, $paragraphOptions);
    }

    /**
     * 添加图片到文档中
     *
     * @param string $imagePath    图片文件路径
     * @param array  $imageOptions 图片样式选项（可选），包括宽度、高度、对齐方式等
     *
     * @throws Exception 如果图片文件不存在则抛出异常
     */
    public function addImage($imagePath, $imageOptions = [])
    {
        if (!file_exists($imagePath)) {
            throw new Exception("图片文件不存在: " . $imagePath);
        }

        $options = array_merge($this->getDefaultImageOptions(), $imageOptions);
        list($width, $height) = getimagesize($imagePath);
        $imageOptions = $this->calculateImageSize($width, $height, $options);

        $this->section->addImage($imagePath, $imageOptions);
    }

    /**
     * 获取跨行单元格信息
     *
     * @return array
     */
    public function getRowSpans()
    {
        return $this->rowSpans;
    }

    /**
     * 更新跨行单元格信息
     *
     * @param array $rowSpans
     */
    public function setRowSpans($rowSpans)
    {
        $this->rowSpans = $rowSpans;
    }

    /**
     * 添加表格
     *
     * @param string   $alignment       表格的水平对齐方式
     * @param callable $tableOperations 闭包函数，用于定义表格的内容和样式
     *
     * @return $this
     */
    public function addTable($alignment = Jc::CENTER, callable $tableOperations)
    {
        $table = $this->section->addTable(['alignment' => $alignment]);

        $tableOperations(new class($table, $this) {
            private $table;
            private $builder;
            private $currentRowIndex = 0;
            private $currentColIndex = 1;

            public function __construct($table, $builder)
            {
                $this->table   = $table;
                $this->builder = $builder;
            }

            /**
             * 添加行
             *
             * @param int|null $height 行高
             *
             * @return $this
             */
            public function addRow($height = null)
            {
                $this->table->addRow($height);
                $this->currentRowIndex++;
                $this->currentColIndex = 1;

                // 处理之前行的跨行单元格
                $rowSpans = $this->builder->getRowSpans();
                foreach ($rowSpans as $key => &$span) {
                    if ($span['remainingRows'] > 0) {
                        if ($span['initialColIndex'] == 1) {
                            // 处理跨行单元格
                            $this->table->addCell(null, [
                                'vMerge'       => 'continue',
                                'borderBottom' => $this->currentRowIndex === $span['initialRowIndex'] ? 'single' : 'none',
                            ]);
                            $span['remainingRows']--;
                        }
                    } else {
                        unset($rowSpans[$key]);
                    }
                }
                $this->currentColIndex = 1;
                $this->builder->setRowSpans($rowSpans);

                return $this;
            }

            /**
             * 添加单元格
             *
             * @param int    $width   单元格宽度
             * @param string $text    单元格文本
             * @param array  $options 单元格选项
             *
             * @return $this
             */
            public function addCell($width, $text = '', $options = [])
            {
                $this->currentColIndex++;
                $defaultOptions = ['valign' => VerticalJc::CENTER];

                // 处理之前行的跨行单元格
                $rowSpans = $this->builder->getRowSpans();
                foreach ($rowSpans as $key => &$span) {
                    if ($span['remainingRows'] > 0) {
                        if ($span['initialColIndex'] == $this->currentColIndex) {
                            // 处理跨行单元格
                            $this->table->addCell(null, [
                                'vMerge'       => 'continue',
                                'borderBottom' => $this->currentRowIndex <= $span['initialRowIndex'] + $span['remainingRows'] ? 'single' : 'none',
                            ]);
                            $span['remainingRows']--;
                        }

                    } else {
                        unset($rowSpans[$key]);
                    }
                }
                $this->builder->setRowSpans($rowSpans);
                $cell = $this->table->addCell($width, array_merge($defaultOptions, $options));
                if (!empty($text)) {
                    $cell->addText($text);
                }
                return $this;
            }

            /**
             * 添加跨列的单元格
             *
             * @param int    $width   单元格宽度
             * @param string $text    单元格文本
             * @param int    $colSpan 跨列数
             * @param array  $options 单元格选项
             *
             * @return $this
             */
            public function addColSpanCell($width, $text = '', $colSpan = 1, $options = [])
            {
                $options = array_merge(['gridSpan' => $colSpan, 'valign' => VerticalJc::CENTER], $options);
                return $this->addCell($width, $text, $options);
            }

            /**
             * 添加跨行的单元格
             *
             * @param int    $width   单元格宽度
             * @param string $text    单元格文本
             * @param int    $rowSpan 跨行数
             * @param array  $options 单元格选项
             *
             * @return $this
             */
            public function addRowSpanCell($width, $text = '', $rowSpan = 1, $options = [])
            {
                $noBorder = [];
                if ($rowSpan > 1) {
                    $noBorder = [
                        'borderBottom'      => 'none',
                        'borderBottomSize'  => 0,
                        'borderBottomColor' => 'FFFFFF',
                    ];
                }
                $options = array_merge([
                    'rowSpan'      => $rowSpan,
                    'valign'       => VerticalJc::CENTER,
                    'borderBottom' => 'single',
                ], $options, $noBorder);
                $this->addCell($width, $text, $options);

                // 记录跨行单元格信息
                if ($rowSpan > 1) {
                    $rowSpans   = $this->builder->getRowSpans();
                    $rowSpans[] = [
                        'remainingRows'   => $rowSpan - 1,
                        'initialRowIndex' => $this->currentRowIndex,
                        'initialColIndex' => $this->currentColIndex - 1,
                    ];
                    $this->builder->setRowSpans($rowSpans);
                }

                return $this;
            }

            /**
             * 在单元格中添加图片
             *
             * @param int    $width     单元格宽度
             * @param string $imagePath 图片路径
             * @param array  $options   图片选项
             *
             * @return $this
             */
            public function addImageCell($width, $imagePath, $options = [])
            {
                $this->currentColIndex++;
                $cell = $this->table->addCell($width, ['valign' => VerticalJc::CENTER]);
                $cell->addImage($imagePath, $options);
                return $this;
            }
        });

        return $this;
    }

    /**
     * 计算图片大小
     *
     * @param int   $originalWidth  原始宽度
     * @param int   $originalHeight 原始高度
     * @param array $options        图片样式选项
     *
     * @return array 计算后的图片样式选项
     */
    private function calculateImageSize($originalWidth, $originalHeight, $options)
    {
        if ($options['width'] && $options['height']) {
            return [
                'width'  => $options['width'],
                'height' => $options['height'],
                'align'  => $options['align'],
            ];
        } elseif ($options['width']) {
            return [
                'width'  => $options['width'],
                'height' => $originalHeight * ($options['width'] / $originalWidth),
                'align'  => $options['align'],
            ];
        } elseif ($options['height']) {
            return [
                'width'  => $originalWidth * ($options['height'] / $originalHeight),
                'height' => $options['height'],
                'align'  => $options['align'],
            ];
        } else {
            return ['align' => $options['align']];
        }
    }

    /**
     * 获取图片默认选项
     *
     * @return array 默认图片选项
     */
    private function getDefaultImageOptions()
    {
        return [
            'width'  => null,
            'height' => null,
            'ratio'  => true,
            'align'  => 'left',
        ];
    }

    /**
     * 添加换行
     *
     * @param int $line
     *
     * @return void
     */
    public function addBr(int $line = 1)
    {
        $this->section->addTextBreak($line); // 添加$line个换行
    }

    /**
     * 添加一页纸张
     *
     * @return void
     */
    public function addPaper()
    {
        // 添加页面
        $this->section = $this->phpWord->addSection();
    }

    /**
     * 添加分页符
     */
    public function addPageBreak()
    {
        $this->section->addPageBreak(); // 添加分页符
    }

    /**
     * 获取单元格默认样式
     *
     * @return array 默认单元格样式
     */
    private function getDefaultCellStyle()
    {
        return [
            'font'      => [
                'name'   => 'Arial',
                'size'   => 12,
                'color'  => '000000',
                'bold'   => false,
                'italic' => false,
            ],
            'alignment' => 'left',
            'border'    => [
                'width' => 1,
                'color' => '000000',
                'style' => 'single',
            ],
            'padding'   => 100,
        ];
    }

    /**
     * 添加下划线文本
     *
     * @param string $text             文本内容
     * @param array  $fontOptions      字体样式选项（可选），包括字号、颜色等
     * @param array  $paragraphOptions 段落样式选项（可选）
     */
    public function addTextWithUnderline($text, $fontOptions = [], $paragraphOptions = [])
    {
        $fontOptions['underline'] = 'single'; // 设置下划线样式
        $this->section->addText($text, $fontOptions, $paragraphOptions);
    }

    /**
     * 设置段落样式
     *
     * @param array $paragraphOptions 段落样式选项（包括对齐方式、缩进等）
     */
    public function setParagraphStyle($paragraphOptions = [])
    {
        $this->section->addText('', null, $paragraphOptions);
    }

    /**
     * 设置文档标题
     *
     * @param string $title 标题内容
     * @param array  $style 标题样式选项（可选），包括字体、对齐方式等
     */
    public function setTitle($title, $style = [])
    {
        $this->phpWord->addTitleStyle(1, $style);
        $this->section->addTitle($title, 1);
    }

    /**
     * 添加页眉到文档中
     *
     * @param string $headerText 页眉内容
     */
    public function addHeader($headerText, $align = 'center')
    {
        // 获取当前部分的页眉
        $header = $this->section->addHeader();
        $style  = array('alignment' => $align); // 设置页眉内容居中对齐
        $header->addText($headerText, null, $style);
        $this->header = $header;
    }

    /**
     * 添加页脚到文档中
     *
     * @param string $footerText 页脚内容
     */
    public function addFooter($footerText, $align = 'center')
    {
        // 获取当前部分的页脚
        $footer = $this->section->addFooter();
        $style  = array('alignment' => $align); // 设置页眉内容居中对齐
        $footer->addText($footerText, null, $style);
        $this->footer = $footer;
    }

    /**
     * 添加超链接到文档中
     *
     * @param string $url  链接地址
     * @param string $text 链接显示文本
     */
    public function addLink($url, $text)
    {
        $textRun = $this->section->addTextRun();
        $textRun->addLink($url, $text, ['color' => '0000FF', 'underline' => 'single']);
    }

    /**
     * 添加有序列表到文档中
     *
     * @param array $items 列表项数组
     */
    public function addOrderedList($items)
    {
        foreach ($items as $item) {
            /* @var \PhpOffice\PhpWord\Style\ListItem */
            // 1:方形实心
            // 3:圆点实心
            // 5:圆点空心
            // 7:编号
            // 8:数字（左边顶格）
            // 9:数字（左边有缩进）
            $this->section->addListItem($item, 0, null, ['listType' => 7]);
        }
    }

    /**
     * 添加无序列表到文档中
     *
     * @param array $items 列表项数组
     */
    public function addUnorderedList($items)
    {
        foreach ($items as $item) {
            /* @var \PhpOffice\PhpWord\Style\ListItem */
            // 1:方形实心
            // 3:圆点实心
            // 5:圆点空心
            // 7:编号
            // 8:数字（左边顶格）
            // 9:数字（左边有缩进）
            $this->section->addListItem($item, 0, null, ['listType' => 5]);
        }
    }

    /**
     * 设置段落间距 「只对当前页生效」
     *
     * @param int $spaceBefore 段落前间距（以磅为单位）
     * @param int $spaceAfter  段落后间距（以磅为单位）
     * @param int $firstLine   首行缩进（以磅为单位）
     */
    public function setParagraphSpacing($spaceBefore = 10, $spaceAfter = 10, $firstLine = 16)
    {
        // 定义全局段落样式
        $paragraphStyle = [
            'alignment'   => 'left',    // 对齐方式
            'spaceBefore' => $spaceBefore,       // 段落前间距（以磅为单位）
            'spaceAfter'  => $spaceAfter,        // 段落后间距（以磅为单位）
            'indentation' => [
                'firstLine' => 360,     // 首行缩进（以磅为单位）
            ],
        ];

        // 遍历所有段落并应用样式
        foreach ($this->section->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                // 这里添加样式到段落
                $element->setParagraphStyle($paragraphStyle);
            }
        }
    }

    /**
     * 设置文本背景色
     *
     * @param string $color 背景色（如 'FFFF00'）
     */
    public function setTextBackgroundColor($color)
    {
        $fontStyle = ['bgColor' => $color];
        $this->section->addText('', $fontStyle);
    }

    /**
     * 插入页码到文档中
     *
     * @param string $position 页码位置（'left', 'center', 'right'）
     */
    public function addPageNumber($position = 'center')
    {
        // 获取当前部分的页脚
        $footer = $this->section->addFooter();
        // 设置页码格式和对齐方式
        // $footer->addPreserveText('第 {PAGE} 页',['size' => 12], ['align' => $position]);
        $footer->addPreserveText('第 {PAGE} 页,共 {NUMPAGES} 页', null, ['align' => $position]);
    }

    /**
     * 替换文档中的文本
     *
     * @param string $search  要查找的文本
     * @param string $replace 替换成的文本
     */
    public function replaceText($search, $replace)
    {
        foreach ($this->phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (method_exists($element, 'getText') && method_exists($element, 'setText')) {
                    $text = $element->getText();
                    if (strpos($text, $search) !== false) {
                        $text = str_replace($search, $replace, $text);
                        $element->setText($text);
                    }
                } elseif (method_exists($element, 'getElements')) {
                    $this->replaceTextInElements($element->getElements(), $search, $replace);
                }
            }
        }
    }

    /**
     * 递归替换元素中的文本
     *
     * @param array  $elements 元素数组
     * @param string $search   要查找的文本
     * @param string $replace  替换成的文本
     */
    private function replaceTextInElements($elements, $search, $replace)
    {
        foreach ($elements as $element) {
            if (method_exists($element, 'getText') && method_exists($element, 'setText')) {
                $text = $element->getText();
                if (strpos($text, $search) !== false) {
                    $text = str_replace($search, $replace, $text);
                    $element->setText($text);
                }
            } elseif (method_exists($element, 'getElements')) {
                $this->replaceTextInElements($element->getElements(), $search, $replace);
            }
        }
    }

    /**
     * 保存文档到文件
     *
     * @param string $filePath 文件路径
     *
     * @throws Exception 如果保存文档失败则抛出异常
     */
    public function save($filePath)
    {
        try {
            $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
            $objWriter->save($filePath);
        } catch (Exception $e) {
            throw new Exception("保存文档失败: " . $e->getMessage());
        }
    }

    /**
     * 直接下载文档
     *
     * @param string $fileName 文件名
     *
     * @throws Exception 如果下载文档失败则抛出异常
     */
    public function download($fileName)
    {
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        try {
            $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
            $objWriter->save('php://output');
        } catch (Exception $e) {
            throw new Exception("下载文档失败: " . $e->getMessage());
        }
        exit;
    }
}
