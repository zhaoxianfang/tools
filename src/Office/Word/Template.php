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

    /**
     * 单个替换
     *
     *
     * @return $this
     */
    public function setValue($filed, $value)
    {

        $this->template->setValue($filed, $value);

        return $this;
    }

    /**
     * 批量替换
     *
     * @param  array  $array  eg:['name'=>'张三', 'age'=>'18']
     * @return $this
     */
    public function replaceText($array = [])
    {
        $this->template->setValues($array);

        return $this;
    }

    /**
     * 设置复选框  模板 ${checkbox}
     *
     * @param  string  $filed  模板中的字段名
     * @param  bool  $checked  是否选中
     * @return $this
     */
    public function setCheckbox(string $filed, bool $checked = true)
    {
        $this->template->setCheckbox($filed, $checked);

        return $this;
    }

    /**
     * 设置图片
     *
     * @param  string  $filed  模板中的字段名
     * @param  array|string  $image  图片信息 eg:
     *                               'path/to/your/image.jpg'
     *                               ['path' => 'path/to/your/image.jpg', 'width' => 100, 'height' => 100, 'ratio' => true]
     * @return $this
     */
    public function replaceImage(string $filed = '', array|string $image = [])
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
     * 替换块
     *      模板：
     *          ${block_name}
     *          This block content will be replaced
     *          ${/block_name}
     *
     *
     * @return Template
     */
    public function replaceBlock(string $filed = '', string $content = '')
    {
        $this->template->replaceBlock($filed, $content);

        return $this;
    }

    /**
     * 删除块
     *
     *
     * @return $this
     */
    public function deleteBlock(string $filed = '')
    {
        $this->template->deleteBlock($filed);

        return $this;
    }

    /**
     * 自定义闭包回调操作
     *
     *
     * @return $this
     */
    public function customCall(callable $callback)
    {
        // 回调参数
        // $this->template: 当前加载的模板
        // $this: 当前类
        $callback($this->template, $this);

        return $this;
    }

    /**
     * 保存编辑后的文档
     *
     * @param  string  $filePath  文件路径
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
        header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
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
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($filePath));

            // 输出文件内容到浏览器
            readfile($filePath);
        } else {
            throw new Exception('文件保存失败。');
        }
        exit;
    }
}
