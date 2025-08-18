<?php

namespace zxf\Tools;

use Exception;

/**
 * 文字生成图片
 *
 * // 创建一个实例
 * $TextToImg = new TextToImg(1200, 800);
 *
 * $TextToImg->setFontFile('./arial.ttf'); // 设置自定义字体路径
 * $TextToImg->setFontStyle('foxi'); // 选择本库中支持的一种字体
 * $TextToImg->setText('这是<br>一段<br>测试文字'); // 设置文字内容，支持使用 <br> 换行
 * $TextToImg->setColor('FF00FF'); // 设置文字颜色
 * $TextToImg->setBgColor('00FF00'); // 设置图片背景色
 * $TextToImg->setAngle(90);// 设置文字旋转
 * $TextToImg->setSize(20);// 设置文字固定字号为20【提示：本库默认会自动计算字体大小，如果设置该属性就使用传入的固定值】
 * $TextToImg->render();// 显示图片到浏览器
 * $TextToImg->render('test.png');// 将图片保存至本地
 */
class TextToImg
{
    private $image;

    private $fontFile = '';

    private $angle = 0; // 旋转角度

    private $text = 'hello'; // 文字内容

    private $size = null; // 设置了值就使用设置的值，未设置就自动计算

    private $textColor = [0, 0, 0]; // 文字颜色

    private $backgroundColor = [255, 255, 255];   // 图片背景颜色

    private $width = 800;   // 图片宽度

    private $height = 600;   // 图片高度

    protected static $instance;

    public function __construct(int $width = 800, int $height = 600)
    {
        $this->width = $width;
        $this->height = $height;

        // 初始化一种字体
        $this->fontFile = dirname(__FILE__, 2).'/resource/font/pmzdxx.ttf';

        // 创建一张新图片，并设置背景色
        $this->image = imagecreatetruecolor($this->width, $this->height);
    }

    public static function instance(int $width = 800, int $height = 600)
    {
        self::$instance = new static($width, $height);

        return self::$instance;
    }

    /**
     * 设置字体文件 路径
     *
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setFontFile(string $file = '')
    {
        if (! is_file($file)) {
            throw new Exception('字体文件不存在:'.$file);
        }
        $this->fontFile = $file;

        return $this;
    }

    /**
     * 选择本库中已有的字体
     *
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setFontStyle(string $style = 'pmzdxx')
    {
        $this->fontFile = dirname(__FILE__, 2).'/resource/font/'.$style.'.ttf';
        if (! is_file($this->fontFile)) {
            throw new Exception('不支持的字体:'.$style);
        }

        return $this;
    }

    /**
     * 设置旋转角度
     *
     *
     * @return $this
     */
    public function setAngle(int $angle = 0)
    {
        // 角度转换到0-360度内
        $angle = $angle > 0 ? ($angle % 360) : ($angle % -360);
        $angle = $angle > 0 ? $angle : (360 + $angle);
        $this->angle = (int) $angle;

        return $this;
    }

    /**
     * 设置文字颜色
     *
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setColor(string $hexVal = '000000')
    {
        $this->transformColor($hexVal, false);

        return $this;
    }

    /**
     * 设置图片背景色
     *
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setBgColor(string $hexVal = 'FFFFFF')
    {
        $this->transformColor($hexVal, true);

        return $this;
    }

    /**
     * 颜色值转换
     *
     * @param  string  $hexVal  颜色值（支持1位，3位，6位）
     * @return $this
     *
     * @throws Exception
     */
    private function transformColor(string $hexVal = '0000FF', bool $isBackground = true)
    {
        $colorLeng = strlen($hexVal);
        $colorArr = [];
        switch ($colorLeng) {
            case '6':
                $colorArr[0] = hexdec('0x'.substr($hexVal, 0, 2)); // 截取两位
                $colorArr[1] = hexdec('0x'.substr($hexVal, 2, 2)); // 截取两位
                $colorArr[2] = hexdec('0x'.substr($hexVal, 4, 2)); // 截取两位
                break;
            case '3':
                $colorArr[0] = hexdec('0x'.substr($hexVal, 0, 1).substr($hexVal, 0, 1)); // 截取一位
                $colorArr[1] = hexdec('0x'.substr($hexVal, 1, 1).substr($hexVal, 1, 1)); // 截取一位
                $colorArr[2] = hexdec('0x'.substr($hexVal, 2, 1).substr($hexVal, 2, 1)); // 截取一位
                break;
            case '1':
                $colorArr[0] = hexdec('0x'.$hexVal.$hexVal);
                $colorArr[1] = hexdec('0x'.$hexVal.$hexVal);
                $colorArr[2] = hexdec('0x'.$hexVal.$hexVal);
                break;
            default:
                throw new Exception(($isBackground ? '背景' : '前景').'色有误');
        }
        if ($isBackground) {
            $this->backgroundColor = $colorArr;
        } else {
            $this->textColor = $colorArr;
        }

        return $this;
    }

    /**
     * 设置文字
     *
     *
     * @return $this
     */
    public function setText(string $text = '')
    {
        $this->text = $text;

        return $this;
    }

    /**
     * 设置文字固定字号为 $size【提示：本库默认会自动计算字体大小，如果设置该属性就使用传入的固定值】
     *
     *
     * @return $this
     */
    public function setSize($size = null)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * 绘制文字
     *
     * @param  string  $text  文字 支持使用 <br>换行
     * @param  int  $angle  旋转角度
     * @param  int|null  $size  设置文字字号，不设置时候 回自动计算文字字体大小
     * @return $this
     */
    private function drawText(string $text, int $angle = 0, $size = null)
    {
        // 图片背景色
        $bgColor = imagecolorallocate(
            $this->image,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        imagefill($this->image, 0, 0, $bgColor);

        $text = str_ireplace('<br>', PHP_EOL, $text);
        // 获取前景色
        $foregroundColor = imagecolorallocate(
            $this->image,
            $this->textColor[0],
            $this->textColor[1],
            $this->textColor[2]
        );

        if (empty($size)) {
            // 如果没有设置字号时候 动态设置字体大小
            $size = $this->calculateFontSize($text, $size, $angle);
        }

        $textInfo = $this->getTextDimensions($text, (int) $size, $angle);

        // 计算居中文字的偏移量
        // $offsetX = ($this->width - $textInfo['width']) / 2;
        // $offsetY = ($this->height - $textInfo['height']) / 2;
        // // 文字开始位置 + 偏移
        // $x           = abs($textInfo['position'][0] - abs($textInfo['min_x'])) + $offsetX;
        // $y           = abs(abs($textInfo['position'][1]) - abs($textInfo['min_y'])) + $offsetY;
        // $offsetAngle = abs(abs($textInfo['position'][5]) - abs($textInfo['position'][7]));// 角度产生的偏移值
        // $y           = $y + $offsetAngle;
        // $y           = $y + ($offsetAngle > 0 ? 0 : $textInfo['word_height'] * 0.8);

        $textWidth = bcsub($textInfo['max_x'], $textInfo['min_x']);
        $textHeight = bcsub($textInfo['max_y'], $textInfo['min_y']);
        // 居中显示
        $x = ($this->width - $textWidth) / 2 + abs($textInfo['min_x']);
        $y = ($this->height - $textHeight) / 2 + abs($textInfo['min_y']);
        if ($x >= $this->width || $y >= $this->height) {
            $size = max($size - 4, 14);
            $x = min($x, $this->width - 20);
            $y = min($y, $this->height - 20);
        }
        // 在图片上添加文字
        imagettftext($this->image, $size, $angle, (int) $x, (int) $y, $foregroundColor, $this->fontFile, $text);

        return $this;
    }

    /**
     * 渲染图片
     *
     *
     * @return void
     */
    public function render($fileName = null)
    {
        $this->drawText($this->text, $this->angle, $this->size);
        if (! empty($fileName)) {
            create_dir(dirname(realpath($fileName)));
            // 保存图片到文件
            imagepng($this->image, $fileName);
        } else {
            // 输出图片到浏览器
            header('Content-Type: image/png');
            imagepng($this->image);
        }
        // 销毁图片
        imagedestroy($this->image);
    }

    /**
     * 计算文字大小
     *
     *
     * @return float
     */
    private function calculateFontSize($text, $size = null, $angle = 0)
    {
        // 如果用户没有指定字体大小，根据文本长度动态计算
        if (empty((int) $size) || ! is_numeric($size)) {
            $size = min($this->height, $this->width) / strlen($text);
        }

        // 获取文字的宽度和高度
        $dimensions = $this->getTextDimensions($text, (int) $size, $angle);

        // 计算字号调整比例
        $widthRatio = $this->width / $dimensions['width'];
        $heightRatio = $this->height / $dimensions['height'];
        $ratio = min($widthRatio, $heightRatio) * 0.95;

        // 重新计算最终的字体大小
        return $size * $ratio;
    }

    /**
     * 获取绘制文字的宽度和高度
     *
     * @param  string  $text  文字
     * @param  int  $size  字号
     * @param  int  $angle  旋转角度
     */
    private function getTextDimensions(string $text = 'hello', $size = 12, int $angle = 0): array
    {
        // 获取文字的宽度和高度
        $bbox = imagettfbbox($size, $angle, $this->fontFile, $text);

        $minX = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $minY = min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
        // 计算文字区域的宽高
        $maxX = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $maxY = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]);

        $wordBox = imagettfbbox($size, 0, $this->fontFile, mb_substr($text, 0, 1, 'utf-8')); // 单个字符

        return [
            'width' => abs($maxX - $minX),
            'height' => abs($maxY - $minY),
            'min_x' => $minX,
            'min_y' => $minY,
            'max_x' => $maxX,
            'max_y' => $maxY,
            'position' => $bbox, // 四个角的坐标
            'word_height' => abs($wordBox[7] - $wordBox[1]), // 单个字符高度
        ];
    }
}
