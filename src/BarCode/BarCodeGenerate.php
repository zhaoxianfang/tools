<?php

namespace zxf\BarCode;

use GdImage;
use JetBrains\PhpStorm\NoReturn;
use zxf\BarCode\Exceptions\UnknownTypeException;

class BarCodeGenerate
{
    protected BarcodeGenerator $generator; // 条形码生成器实例
    protected int              $padding  = 10; // 条形码外部安全边距
    protected int              $width    = 2; // 条形码单条宽度
    protected int              $height   = 50; // 条形码高度
    protected string           $format   = BarcodeGenerator::TYPE_CODE_128; // 默认编码格式
    protected string           $text     = ''; // 底部文本
    protected string           $content  = '0123456789'; // 条码内容
    protected string           $fontPath = ''; // 默认字体路径
    protected int              $fontSize = 10; // 默认字体大小

    // 条形码格式
    protected array $generatorMaps = [
        'png' => BarcodeGeneratorPNG::class, // 基于像素的 PNG
        'jpg' => BarcodeGeneratorJPG::class, // 基于像素的 JPG
        // 'svg'           => BarcodeGeneratorSVG::class, // 基于矢量的 SVG
        // 'html'          => BarcodeGeneratorHTML::class, // 基于像素的 HTML
        // 'dynamic_html' => BarcodeGeneratorDynamicHTML::class, // 基于矢量的 HTML
    ];

    public function __construct(string $generatorType = 'png')
    {
        if (empty($this->generatorMaps[$generatorType])) {
            throw new UnknownTypeException('Unknown type: ' . $generatorType);
        }
        $this->generator = new $this->generatorMaps[$generatorType]();
    }

    /**
     * 设置条形码的外部安全边距
     *
     * @param int $padding
     *
     * @return $this
     */
    public function padding(int $padding = 10)
    {
        $this->padding = max(0, $padding);
        return $this;
    }

    /**
     * 设置条形码宽度
     *
     * @param int $width
     *
     * @return $this
     */
    public function width(int $width = 2)
    {
        $this->width = max(1, $width);
        return $this;
    }

    /**
     * 设置条形码高度
     *
     * @param int $height
     *
     * @return $this
     */
    public function height(int $height = 50)
    {
        $this->height = max(10, $height);
        return $this;
    }

    /**
     * 设置条形码编码格式
     *
     * @param string $format
     *
     * @return $this
     */
    public function format(string $format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * 设置底部文本内容
     *
     * @param string $text
     * @param string $fontPath
     * @param int    $textSize
     *
     * @return $this
     */
    public function text(string $text = '', int $textSize = 10, string $fontPath = '')
    {
        $this->text = $text;

        // 设置字体路径
        $fontDir = dirname(__DIR__, 1) . '/resource/font/';
        if ($fontPath) {
            $this->fontPath = file_exists($fontPath) ? $fontPath : $fontDir . $fontPath . '.ttf';
        } else {
            $this->fontPath = $fontDir . 'pmzdxx.ttf';
        }

        if ($textSize > 0) {
            $this->textSize = $textSize;
        }

        return $this;
    }

    /**
     * 设置条形码内容
     *
     * @param string $string
     *
     * @return $this
     */
    public function content(string $string = '0123456789')
    {
        $this->content = $string;
        return $this;
    }

    /**
     * 生成条形码并返回图像资源
     *
     * @param string $text
     *
     * @return resource|false
     * @throws UnknownTypeException
     */
    public function generateImageResource()
    {
        $barcodeData = $this->generator->getBarcode($this->content, $this->format, $this->width, $this->height);
        $image       = imagecreatefromstring($barcodeData);

        // 显示底部文本
        $image = $this->addTextBelow($image);

        if ($this->padding > 0) {
            $paddedImage = $this->addPadding($image);
            imagedestroy($image);
            return $paddedImage;
        }

        return $image;
    }

    /**
     * 添加外部边距
     *
     * @param resource $image
     *
     * @return resource
     */
    protected function addPadding($image)
    {
        $width     = imagesx($image);
        $height    = imagesy($image);
        $newWidth  = $width + ($this->padding * 2);
        $newHeight = $height + ($this->padding * 2);

        $paddedImage = imagecreatetruecolor($newWidth, $newHeight);
        $white       = imagecolorallocate($paddedImage, 255, 255, 255);
        imagefill($paddedImage, 0, 0, $white);

        imagecopy($paddedImage, $image, $this->padding, $this->padding, 0, 0, $width, $height);

        return $paddedImage;
    }

    /**
     * 在条形码下方添加文本内容，居中显示
     *
     * @param $image
     *
     * @return false|GdImage|resource
     */
    protected function addTextBelow($image)
    {
        if (empty($this->content)) {
            $this->content('A001');
        }
        if (empty($this->text)) {
            $this->text($this->content);
        }

        $width     = imagesx($image);
        $height    = imagesy($image);
        $offset    = 6;
        $newHeight = $height + $this->fontSize + $offset;

        $imageWithText = imagecreatetruecolor($width, $newHeight);
        $white         = imagecolorallocate($imageWithText, 255, 255, 255);
        $black         = imagecolorallocate($imageWithText, 0, 0, 0);
        imagefill($imageWithText, 0, 0, $white);

        imagecopy($imageWithText, $image, 0, 0, 0, 0, $width, $height);

        $textBox   = imagettfbbox($this->fontSize, 0, $this->fontPath, $this->text);
        $textWidth = $textBox[2] - $textBox[0];
        $textX     = ($width - $textWidth) / 2;
        $textY     = $newHeight - $offset / 2;

        imagettftext($imageWithText, $this->fontSize, 0, $textX, $textY, $black, $this->fontPath, $this->text);

        return $imageWithText;
    }

    /**
     * 直接输出到浏览器
     */
    #[NoReturn]
    public function toBrowser(): void
    {
        header('Content-Type: image/png');
        $image = $this->generateImageResource();
        imagepng($image);
        imagedestroy($image);
        die;
    }

    /**
     * 保存条形码到指定路径
     *
     * @param string $filePath
     *
     * @return string 文件的相对路径
     * @throws UnknownTypeException
     */
    public function toFile(string $filePath): string
    {
        $image = $this->generateImageResource();
        imagepng($image, $filePath);
        imagedestroy($image);
        // 返回文件的相对路径
        return relative_path($filePath);
    }

    /**
     * 返回图片资源
     *
     * @return false|GdImage|resource
     * @throws UnknownTypeException
     */
    public function toImg()
    {
        return $this->generateImageResource();
    }
}
