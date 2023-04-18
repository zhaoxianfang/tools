<?php

namespace zxf\tools;

// 使用php8实现的文字生成图片的高级封装类，可以动态设置文字颜色、图片背景色、可以根据图片大小自动计算文字的字体大小，图片空白区不能超过70%、文字可以换行和旋转角度

class TextToImg
{
    private $image; // 图片资源
    private $font_size  = 14; // 字体大小
    private $font_color; // 字体颜色
    private $bg_color; // 背景颜色
    private $font_angle = 0; // 字体旋转角度
    private $font_file; // 字体文件
    private $text; // 文字内容
    private $textArea   = 0.9; // 文字区域

    public function __construct($width, $height)
    {
        $this->image = imagecreatetruecolor($width, $height); // 创建指定大小的图片资源
    }

    public function setFontSize($size)
    {
        $this->font_size = $size; // 设置字体大小
        return $this;
    }

    public function setFontColor($color)
    {
        $this->font_color = $color; // 设置字体颜色
        return $this;
    }

    public function setBgColor($color)
    {
        $this->bg_color = $color; // 设置背景颜色
        return $this;
    }

    public function setTextArea($area = 0.9)
    {
        $this->textArea = $area; // 设置文字宽度最多不能超过多少比例，例如 0.8 表示文字的最大宽度为图片宽度的 80%
        return $this;
    }

    public function setFontAngle($rotate = 0)
    {
        // 角度转换到0-360度内
        $rotate           = $rotate > 0 ? ($rotate % 360) : ($rotate % -360);
        $rotate           = $rotate > 0 ? $rotate : (360 + $rotate);
        $this->font_angle = (int)$rotate; // 设置字体旋转角度
        return $this;
    }

    public function setFontFile($file)
    {
        $this->font_file = $file; // 设置字体文件
        return $this;
    }

    public function setText($text)
    {
        $this->text = $text; // 设置文字内容
        return $this;
    }

    //计算一个字符串占用的宽高
    private function getWordArea($size = 12, $angle = 0)
    {
        // 中文
        $cnText   = imagettfbbox($size, $angle, $this->font_file, '我');
        $cnWidth  = $cnText[2] - $cnText[0]; //字符串所占宽度
        $cnHeight = $cnText[5] - $cnText[3]; //字符串所占高度

        // 字母
        $enText   = imagettfbbox($size, $angle, $this->font_file, 'W');
        $enWidth  = $enText[2] - $enText[0]; //字符串所占宽度
        $enHeight = $enText[5] - $enText[3]; //字符串所占高度
        return [
            'cn' => [
                'width'  => $cnWidth,
                'height' => $cnHeight,
            ],
            'en' => [
                'width'  => $enWidth,
                'height' => $enHeight,
            ],
        ];

    }

    public function generate()
    {
        $bg_color = imagecolorallocate($this->image, $this->bg_color[0], $this->bg_color[1], $this->bg_color[2]); // 创建背景颜色
        imagefill($this->image, 0, 0, $bg_color); // 填充背景颜色

        $font_color = imagecolorallocate($this->image, $this->font_color[0], $this->font_color[1], $this->font_color[2]); // 创建字体颜色

//        $box         = imagettfbbox($this->font_size, $this->font_angle, $this->font_file, $this->text); // 获取字体的边界框
//        $text_width  = abs($box[4] - $box[0]); // 计算字体的宽度
//        $text_height = abs($box[5] - $box[1]); // 计算字体的高度

        $max_text_width  = $this->getWidth() * $this->textArea; // 计算最大的字体宽度，不能超过图片宽度的 70%
        $max_text_height = $this->getHeight() * $this->textArea; // 计算最大的字体高度，不能超过图片宽度的 70%
        $maxArea         = bcmul($max_text_width, $max_text_height, 2);

        list($cnWord, $enWord) = $this->getWordArea($this->font_size);
//        $max_text_width
        $rowCnWordNum = floor(bcdiv($max_text_width, $cnWord['width'], 1)); // 一行能容纳多少个汉字
        $rowEnWordNum = floor(bcdiv($max_text_width, $enWord['width'], 1)); // 一行能容纳多少个字母

        $columnCnWordNum = floor(bcdiv($max_text_width, $cnWord['height'])); // 一列能容纳多少个汉字
        $columnEnWordNum = floor(bcdiv($max_text_width, $enWord['height'])); // 一列能容纳多少个字母

        $maxCnArea = bcmul($rowCnWordNum, $this->text, 2);// 平均每个字的最大面积

//        imagefontheight
        if ($text_width > $max_text_width) {
            $this->font_size = $this->font_size * ($max_text_width / $text_width); // 根据最大宽度调整字体大小
            $box             = imagettfbbox($this->font_size, $this->font_angle, $this->font_file, $this->text); // 重新获取字体的边界框
            $text_width      = abs($box[4] - $box[0]); // 重新计算字体的宽度
            $text_height     = abs($box[5] - $box[1]); // 重新计算字体的高度
        }

        $words = explode(' ', $this->text); // 将文字内容按空格分割成单词
        $lines = [];
        $line  = '';
        foreach ($words as $word) {
            $box        = imagettfbbox($this->font_size, $this->font_angle, $this->font_file, $line . ' ' . $word); // 获取当前行加上下一个单词后的边界框
            $line_width = abs($box[4] - $box[0]); // 计算当前行加上下一个单词后的宽度
            if ($line_width > $max_text_width) { // 如果当前行加上下一个单词后的宽度超过最大宽度
                $lines[] = $line; // 将当前行加入行数组
                $line    = $word; // 开始新的一行
            } else {
                $line .= ' ' . $word; // 继续在当前行添加单词
            }
        }
        $lines[] = $line; // 将最后一行加入行数组

        foreach ($lines as $i => $line) {
            $box        = imagettfbbox($this->font_size, $this->font_angle, $this->font_file, $line); // 获取当前行的边界框
            $text_width = abs($box[4] - $box[0]); // 计算当前行的宽度
            $x          = ($this->getWidth() - $text_width) / 2; // 计算当前行的横向位置
            $y          = ($this->getHeight() - $text_height) / 2 + $text_height + $i * $text_height * 1.2; // 计算当前行的纵向位置
            imagettftext($this->image, $this->font_size, $this->font_angle, (int)$x, (int)$y, $font_color, $this->font_file, $line); // 在图片上绘制文字
        }
        return $this;
    }

    public function getWidth()
    {
        return imagesx($this->image); // 获取图片宽度
    }

    public function getHeight()
    {
        return imagesy($this->image); // 获取图片高度
    }

    public function output()
    {
        header('Content-Type: image/png'); // 设置输出类型为 PNG 图片
        imagepng($this->image); // 输出图片
        imagedestroy($this->image); // 销毁图片资源
        die;
    }
}

// 使用这个类，可以按照以下步骤生成文字图片：

$textToImage = new TextToImage(400, 200); // 创建一个 400x400 的图片
$textToImage->setFontSize(35); // 设置字体大小为 24
$textToImage->setFontColor([255, 255, 255]); // 设置字体颜色为白色
$textToImage->setBgColor([0, 0, 0]); // 设置背景颜色为黑色
// $textToImage->setFontAngle(45); // 设置字体旋转角度为 45 度
$textToImage->setFontFile('./ali_puhui.ttf'); // 设置文字字体
$textToImage->setText('Hello, World!'); // 设置内容
$textToImage->generate(); // 生成图片
$textToImage->output(); // 输出图片
// 这个示例代码会生成一个 400x400 的黑色背景的图片，上面写着 "Hello, World!"，字体颜色为白色，字体旋转角度为 45 度。
