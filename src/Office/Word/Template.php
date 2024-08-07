<?php

namespace zxf\Office\Word;

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * 模板内容替换
 *      说明：被替换的文本用${}括起来，eg: ${name}, ${img_logo}
 *      调用替换内容时：替换的内容在模板中是使用${}括起来的,在调用替换时${}是可以省略的，eg: ${name}、name 都可以
 */
class Template
{
    private $template;

    public function __construct($filePath)
    {
        $this->template = new TemplateProcessor($filePath);
    }

    public function replaceText($array = [])
    {
        foreach ($array as $field => $content) {
            // Replace placeholders with actual values
            $this->template->setValue($field, $content);
        }
        return $this;
    }

    public function replaceImage($filed = '', $image)
    {
        //   $image=[
        //        'path' => 'path/to/your/image.jpg',
        //        'width' => 100, // 设置图片宽度
        //        'height' => 100, // 设置图片高度
        //        'ratio' => true  // 保持图片比例
        //   ];

        // 替换占位符为图片
        $this->template->setImageValue($filed, $image);

        return $this;
    }


    /**
     * 保存编辑后的文档
     *
     * @param string $filePath 文件路径
     */
    public function save($filePath)
    {
        $this->template->saveAs($filePath);
        return $this;
    }

    public function download($fileName = 'example_download.docs')
    {
        // 设置 HTTP 头信息以强制浏览器下载文件
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // 将文档直接输出到浏览器
        $this->template->saveAs('php://output');

        exit;
    }

    public function saveAndDownload($filePath)
    {
        $this->template->saveAs($filePath);

        // 检查文件是否成功保存
        if (file_exists($filePath)) {
            // 设置 HTTP 头信息以强制浏览器下载文件
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            // 输出文件内容到浏览器
            readfile($filePath);
        } else {
            throw new Exception('文件保存失败。');
        }
        exit;
    }
}
