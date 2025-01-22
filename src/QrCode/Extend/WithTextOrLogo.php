<?php

namespace zxf\QrCode\Extend;

use zxf\QrCode\Data\QRMatrix;
use zxf\QrCode\Output\QRGdImagePNG;
use zxf\QrCode\QRCode;
use zxf\QrCode\QROptions;
use zxf\QrCode\Settings\SettingsContainerInterface;

/**
 * 实现可以在二维码下方携带文本的Trait
 */
class WithTextOrLogo extends QRGdImagePNG
{
    /** @var string 二维码底部需要显示的文本 */
    protected string $text = '';

    /** @var int 文本大小 */
    protected int $textSize = 10;

    /** @var array 文本背景颜色 */
    protected array $textBG = [200, 200, 200];

    /** @var array 文本颜色 */
    protected array $textColor = [50, 50, 50];

    /** @var int 额外的背景高度 */
    protected int $extraBgHeight = 10;

    /** @var string 字体路径 */
    protected string $fontPath = '';

    /** @var int 行间距 */
    protected int $lineSpacing = 5;

    /** @var string logo路径 */
    protected string $logoPath = '';

    /** @var int 图片处理方式 */
    protected int $handleType = self::HANDLE_TYPE_RETURN_IMG;

    const HANDLE_TYPE_TO_BROWSER = 1; // 输出到浏览器
    const HANDLE_TYPE_TO_PATH    = 2; // 保存到文件
    const HANDLE_TYPE_RETURN_IMG = 3; // 返回图片资源

    /**
     * 重写构造函数，允许传入文本配置选项
     *
     * @param SettingsContainerInterface|QROptions $options       选项
     * @param QRMatrix                             $matrix        矩阵
     * @param int                                  $textSize      文本大小
     * @param array                                $textBG        文本背景颜色
     * @param array                                $textColor     文本颜色
     * @param int                                  $extraBgHeight 额外的背景高度
     * @param string                               $fontPath      字体路径
     */

    /**
     * 设置字体路径
     *
     * @param string $text
     * @param string $fontPath
     * @param int    $textSize
     *
     * @return $this
     */
    public function setText(string $text = '', string $fontPath = '', int $textSize = 10)
    {
        $this->text = $text;

        // 设置字体路径
        $fontDir = dirname(__DIR__, 2) . '/resource/font/';
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

    public function setLogo(string $logoPath = '')
    {
        $this->logoPath = $logoPath;
        return $this;
    }

    // 设置处理方式
    public function setHandleType(int $handleType = self::HANDLE_TYPE_RETURN_IMG)
    {
        $this->handleType = $handleType;
        return $this;
    }


    /**
     * ======================================================
     */


    /**
     *
     * @param string|null $file 保存文件路径
     *
     * @return \GdImage|string
     * @throws \ErrorException
     * @throws \zxf\QrCode\Output\QRCodeOutputException
     */
    public function dump(string|null $file = null): \GdImage|string
    {
        // 设置 returnResource 为 true 以跳过进一步处理
        $this->options->returnResource = true;

        // 调用父类的 dump 方法
        parent::dump($file);

        if (!empty($this->logoPath)) {
            $this->addLogo();
        }

        // 如果有文本，渲染文本输出
        if (!empty($this->text)) {
            $this->addText($this->text);
        }

        $imageData = $this->dumpImage();

        // 是否保存文件到指定路径
        if ($this->handleType == self::HANDLE_TYPE_TO_PATH) {
            if (empty($file)) {
                throw new \ErrorException('未设置文件保存路径');
            }
            $this->saveToFile($imageData, $file);
            return $file;
        }

        // 直接输出到浏览器
        if ($this->handleType == self::HANDLE_TYPE_TO_BROWSER) {
            $this->outputToBrowser();
        }

        if ($this->options->outputBase64) {
            $imageData = $this->toBase64DataURI($imageData);
        }

        return $imageData;
    }

    protected function addText(string $text): void
    {
        // 保存二维码图像
        $qrcode = $this->image;

        // 文本相关选项
        $textSize    = $this->textSize;
        $textBG      = $this->textBG;
        $textColor   = $this->textColor;
        $fontPath    = $this->fontPath;
        $fontPath    = dirname(__DIR__, 2) . '/resource/font/pmzdxx.ttf';
        $lineSpacing = $this->lineSpacing;

        // 存储文本行的数组
        $lines        = [];
        $words        = explode(' ', $text);
        $currentLine  = '';
        $lineWidth    = 0;
        $maxLineWidth = $this->length - 20; // 增加左右边距

        // 计算每行文本的宽度，处理换行
        foreach ($words as $word) {
            $testLine  = $currentLine . ($currentLine ? ' ' : '') . $word;
            $testBox   = imagettfbbox($textSize, 0, $fontPath, $testLine);
            $testWidth = $testBox[2] - $testBox[0];

            if ($testWidth <= $maxLineWidth) {
                $currentLine = $testLine;
            } else {
                $lines[]     = $currentLine;
                $currentLine = $word;
            }
        }
        $lines[] = $currentLine;

        // 计算文字区域的高度
        $lineCount  = count($lines);
        $textHeight = ($lineCount > 1) ? ($textSize * 2 + $lineSpacing) : ($textSize + $lineSpacing);

        // 调整背景的高度以适应文字区域
        $bgWidth  = $this->length;
        $bgHeight = $this->length + $textHeight + $this->extraBgHeight;

        // 创建一个带有额外空间的新图像
        $this->image = imagecreatetruecolor($bgWidth, $bgHeight);
        $background  = imagecolorallocate($this->image, ...$textBG);

        // 允许透明度
        if ($this->options->imageTransparent) {
            imagecolortransparent($this->image, $background);
        }

        // 填充背景
        imagefilledrectangle($this->image, 0, 0, $bgWidth, $bgHeight, $background);

        // 复制二维码到新图像
        imagecopymerge($this->image, $qrcode, 0, 0, 0, 0, $this->length, $this->length, 100);
        imagedestroy($qrcode);

        // 分配字体颜色
        $fontColor = imagecolorallocate($this->image, ...$textColor);

        // 计算文本起始位置
        $y = $this->length + $lineSpacing + $this->textSize;
        foreach ($lines as $line) {
            $lineBox   = imagettfbbox($textSize, 0, $fontPath, $line);
            $lineWidth = $lineBox[2] - $lineBox[0];
            $x         = ($bgWidth - $lineWidth) / 2;
            imagettftext($this->image, $textSize, 0, (int)$x, (int)$y, $fontColor, $fontPath, $line);
            $y += $textSize + $lineSpacing;
        }
    }

    protected function addLogo(): void
    {
        // 获取二维码图像
        $qrImage = $this->image;

        $this->logoPath = $this->logoPath ?: dirname(__DIR__, 2) . '/resource/images/tn_code/bg/1.png';

        // 创建标志图片的图像资源
        $logo = imagecreatefromstring(file_get_contents($this->logoPath));

        // 获取二维码的尺寸
        $qrWidth  = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);


        // 计算标志图片的尺寸（为二维码宽度的 25%）
        $logoWidth  = (int)($qrWidth * 0.3);
        $logoHeight = (int)($qrWidth * 0.3);

        // 调整标志图片的大小
        $resizedLogo = imagescale($logo, $logoWidth, $logoHeight);

        // 计算标志图片的位置，使其居中显示
        $logoX = ($qrWidth - $logoWidth) / 2;
        $logoY = ($qrHeight - $logoHeight) / 2;


        // 将标志图片合并到二维码图像中
        imagecopy($qrImage, $resizedLogo, (int)$logoX, (int)$logoY, 0, 0, $logoWidth, $logoHeight);

        // 销毁标志图片资源
        imagedestroy($logo);
        imagedestroy($resizedLogo);

        // 更新图像资源
        $this->image = $qrImage;
    }

    // 输出到浏览器
    protected function outputToBrowser(): void
    {
        header('Content-Type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
        die;
    }
}
