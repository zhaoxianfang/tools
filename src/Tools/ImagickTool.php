<?php

namespace zxf\Tools;

use Imagick;
use Exception;
use ImagickException;

/**
 * 图像处理 (ImageMagick)
 *
 * imagick 3.7 版本
 * 文档 https://www.php.net/imagick
 */
class ImagickTool
{
    private Imagick $imagick;

    /**
     * 水印位置枚举
     */
    const WATERMARK_TOP_LEFT      = 1;
    const WATERMARK_TOP_CENTER    = 2;
    const WATERMARK_TOP_RIGHT     = 3;
    const WATERMARK_MIDDLE_LEFT   = 4;
    const WATERMARK_MIDDLE_CENTER = 5;
    const WATERMARK_MIDDLE_RIGHT  = 6;
    const WATERMARK_BOTTOM_LEFT   = 7;
    const WATERMARK_BOTTOM_CENTER = 8;
    const WATERMARK_BOTTOM_RIGHT  = 9;

    public static array $watermarkPositionMap = [
        self::WATERMARK_TOP_LEFT      => '左上角',
        self::WATERMARK_TOP_CENTER    => '顶部居中',
        self::WATERMARK_TOP_RIGHT     => '右上角',
        self::WATERMARK_MIDDLE_LEFT   => '左侧居中',
        self::WATERMARK_MIDDLE_CENTER => '居中',
        self::WATERMARK_MIDDLE_RIGHT  => '右侧居中',
        self::WATERMARK_BOTTOM_LEFT   => '左下角',
        self::WATERMARK_BOTTOM_CENTER => '底部居中',
        self::WATERMARK_BOTTOM_RIGHT  => '右下角',
    ];


    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('未加载 imagick 扩展.');
        }
        $this->imagick = new Imagick();
    }

    /**
     * 打开一张图片
     *
     * @param string $filePath 图片文件路径
     *
     * @throws ImagickException
     */
    public function openImage(string $filePath)
    {
        try {
            $this->imagick->readImage($filePath);
        } catch (\ImagickException $e) {
            throw new \ImagickException("无法打开图片：{$e->getMessage()}");
        }
        return $this;
    }

    /**
     * 保存处理后的图片
     *
     * @param string $outputPath 输出文件路径
     * @param string $format     输出格式，例如：jpeg、png、gif、bmp等
     *
     * @throws \ImagickException
     */
    public function saveImage(string $outputPath, string $format = 'png')
    {
        try {
            $this->imagick->setImageFormat($format);
            return $this->imagick->writeImage($outputPath);
        } catch (\ImagickException $e) {
            throw new \ImagickException("保存图片失败：{$e->getMessage()}");
        }
    }

    /**
     * 调整图片大小
     *
     * @param int  $width               新的宽度
     * @param int  $height              新的高度
     * @param bool $maintainAspectRatio 是否保持宽高比例
     *
     * @return ImagickTool
     * @throws ImagickException
     */
    public function resizeImage($width, $height, bool $maintainAspectRatio = true)
    {
        try {
            //$filter：指定用于重新采样的滤波器类型。可选的滤波器类型有：
            //
            //Imagick::FILTER_POINT：最近邻插值，速度最快但效果较差。
            //Imagick::FILTER_BOX：简单的线性插值。
            //Imagick::FILTER_TRIANGLE：三角滤波器，用于简单图像调整，速度较快。
            //Imagick::FILTER_HERMITE：Hermite插值，较三角滤波器效果更好。
            //Imagick::FILTER_HANNING：Hanning插值，用于平滑图像。
            //Imagick::FILTER_HAMMING：Hamming插值，与Hanning类似。
            //Imagick::FILTER_BLACKMAN：Blackman插值，用于柔和的图像调整。
            //Imagick::FILTER_GAUSSIAN：高斯插值，用于平滑图像，效果较好。
            //Imagick::FILTER_QUADRATIC：二次插值，较Hermite插值效果更好。
            //Imagick::FILTER_CUBIC：立方插值，用于柔和图像调整。
            //Imagick::FILTER_CATROM：Catrom插值，用于柔和图像调整，效果较好。
            //Imagick::FILTER_MITCHELL：Mitchell插值，用于平滑图像，效果较好。
            //Imagick::FILTER_LANCZOS：Lanczos插值，用于平滑图像，效果最好但计算成本较高。
            //Imagick::FILTER_BESSEL：Bessel插值，用于柔和图像调整。
            //Imagick::FILTER_SINC：Sinc插值，用于柔和图像调整，效果较好。
            //$blur：模糊因子，用于控制滤波器的模糊程度。通常情况下，将其设置为1表示不进行额外的模糊操作。
            //
            //$bestfit：如果设置为TRUE，则将按比例调整图像，以适应指定的宽度和高度，而不会使图像变形。默认值为FALSE。
            //
            //$legacy：如果设置为TRUE，则将使用旧版的resizeImage行为。在较旧版本的Imagick中，可能会有不同的行为。默认值为FALSE。
            if ($maintainAspectRatio) {
                $this->imagick->cropThumbnailImage($width, $height);
            } else {
                $this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
            }

        } catch (\ImagickException $e) {
            throw new \ImagickException("调整图片大小失败：{$e->getMessage()}");
        }
        return $this;
    }

    /**
     * 压缩图像质量或调整宽度和高度
     *
     * @param int      $quality   图像质量，范围为 0 到 100
     * @param int|null $newWidth  压缩后的宽度，如果为 null，则保持原宽度
     * @param int|null $newHeight 压缩后的高度，如果为 null，则保持原高度
     *
     * @throws \ImagickException
     */
    public function compressImageQuality(int $quality, ?int $newWidth = null, ?int $newHeight = null, ?string $outputPath = null)
    {
        try {
            // 设置图像质量
            $this->imagick->setImageCompressionQuality($quality);

            // 如果图像包含透明通道，则设置透明通道和背景颜色
            if ($this->hasAlphaChannel()) {
                $this->imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
                $this->imagick->setImageBackgroundColor(new \ImagickPixel('transparent'));
            }

            // 如果指定了新的宽度和高度，则调整图像大小
            if ($newWidth !== null && $newHeight !== null) {
                $this->resizeImage($newWidth, $newHeight, false);
            }
            $res = true;
            // 保存合并后的图片到指定路径或返回base64图片
            if (!empty($outputPath)) {
                $this->imagick->writeImages($outputPath,true);
            } else {
                $res = 'data:image/' . $this->imagick->getImageFormat() . ';base64,' . base64_encode($this->imagick->getImageBlob());
            }
            // 清理资源，释放资源
            $this->imagick->clear();
            $this->imagick->destroy();
            return $res;

        } catch (\ImagickException $e) {
            throw new \ImagickException("压缩图像质量失败：{$e->getMessage()}");
        }
    }

    /**
     * 检查图像是否包含透明通道
     *
     * @return bool
     * @throws ImagickException
     */
    private function hasAlphaChannel(): bool
    {
        return $this->imagick->getImageAlphaChannel() === \Imagick::ALPHACHANNEL_ACTIVATE;
    }


    /**
     * 添加文字水印
     *
     * @param string   $text     要添加的水印文字
     * @param string   $fontName 字体名称，例如：pmzdxx
     * @param int      $fontSize 字体大小
     * @param string   $color    文字颜色，例如：#FF0000
     * @param int|null $position 水印位置，使用类的常量
     * @param int      $angle    旋转角度
     *
     * @return ImagickTool
     * @throws ImagickException
     * @throws \ImagickDrawException
     * @throws \ImagickPixelException
     */
    public function addTextWatermark(string $text, string $fontName = 'pmzdxx', int $fontSize, string $color, ?int $position = self::WATERMARK_BOTTOM_RIGHT, int $angle = 0)
    {
        try {
            // 创建 ImagickDraw 对象
            $draw = new \ImagickDraw();

            $fontPath = dirname(__FILE__, 2) . '/resource/font/' . $fontName . '.ttf';
            if (!is_file($fontPath)) {
                throw new \Exception('不支持的字体:' . $fontName);
            }

            // 设置字体、大小、颜色
            $draw->setFont($fontPath);
            $draw->setFontSize($fontSize);
            $draw->setFillColor(new \ImagickPixel($color));

            // 根据位置设置水印坐标
            $this->setWatermarkPosition($position, $draw);

            // 在图像上绘制文字
            $this->imagick->annotateImage($draw, 0, 0, $angle, $text);
        } catch (\ImagickException $e) {
            throw new \ImagickException("添加文字水印失败：{$e->getMessage()}");
        }
        return $this;
    }

    /**
     * 根据水印位置设置坐标
     *
     * @param int          $position 水印位置
     * @param \ImagickDraw $draw     ImagickDraw 对象
     *
     * @throws ImagickException
     */
    private function setWatermarkPosition(int $position, \ImagickDraw $draw)
    {
        // $width  = $this->imagick->getImageWidth();
        // $height = $this->imagick->getImageHeight();

        switch ($position) {
            case self::WATERMARK_TOP_LEFT:
                $draw->setGravity(\Imagick::GRAVITY_NORTHWEST);
                break;
            case self::WATERMARK_TOP_CENTER:
                $draw->setGravity(\Imagick::GRAVITY_NORTH);
                break;
            case self::WATERMARK_TOP_RIGHT:
                $draw->setGravity(\Imagick::GRAVITY_NORTHEAST);
                break;
            case self::WATERMARK_MIDDLE_LEFT:
                $draw->setGravity(\Imagick::GRAVITY_WEST);
                break;
            case self::WATERMARK_MIDDLE_CENTER:
                $draw->setGravity(\Imagick::GRAVITY_CENTER);
                break;
            case self::WATERMARK_MIDDLE_RIGHT:
                $draw->setGravity(\Imagick::GRAVITY_EAST);
                break;
            case self::WATERMARK_BOTTOM_LEFT:
                $draw->setGravity(\Imagick::GRAVITY_SOUTHWEST);
                break;
            case self::WATERMARK_BOTTOM_CENTER:
                $draw->setGravity(\Imagick::GRAVITY_SOUTH);
                break;
            case self::WATERMARK_BOTTOM_RIGHT:
            default:
                $draw->setGravity(\Imagick::GRAVITY_SOUTHEAST);
                break;
        }
    }

    /**
     * 合并多张图片为一张，确保每个图片的宽度与最宽图像一致
     *
     * @param array       $imagePaths 图片文件路径数组
     * @param string|null $outputPath 合并后的图片输出路径，如果为null则返回base64图片
     *
     * @return string|true 返回base64图片字符串（如果$outputPath为null），否则为true
     * @throws \ImagickException
     */
    public function mergeImages(array $imagePaths, ?string $outputPath = null): bool|string
    {
        try {
            $maxWidth = 0;
            // 创建ImageMagick对象
            $imagick = new Imagick();

            // 逐个读取图像并附加到 Imagick 对象
            foreach ($imagePaths as $imagePath) {
                $image = new \Imagick($imagePath);
                // 获取最大宽度
                $maxWidth = max($maxWidth, $image->getImageWidth());
                $imagick->addImage($image);
            }

            // 调整每个图像的宽度为最大宽度
            foreach ($imagick as $image) {
                $image->thumbnailImage($maxWidth, 0);
            }

            // 合并为一张图片
            $imagick = $imagick->appendImages(true);

            $res = true;
            // 保存合并后的图片到指定路径或返回base64图片
            if (!empty($outputPath)) {
                $imagick->writeImages($outputPath,true);
            } else {
                $res = 'data:image/' . $imagick->getImageFormat() . ';base64,' . base64_encode($imagick->getImageBlob());
            }
            // 清理资源，释放资源
            $imagick->clear();
            $imagick->destroy();
            return $res;

        } catch (\ImagickException $e) {
            throw new \ImagickException("合并图片失败：{$e->getMessage()}");
        }
    }

    /**
     * 合并多个PDF文件或图片文件为一张图片
     *
     * @param array       $filePaths
     * @param string|null $outputPath
     *
     * @return bool|string
     * @throws ImagickException
     */
    public function pdfOrImgMergeToImage(array $filePaths = [], ?string $outputPath = null)
    {
        if (empty($filePaths)) {
            throw new \ImagickException("文件路径不能为空");
        }
        // 创建ImageMagick对象
        $imagick = new Imagick();

        foreach ($filePaths as $file) {
            // 判断文件类型是pdf还是图片
            $fileType = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
                throw new \ImagickException("文件类型不支持");
            }
            if ($fileType == 'pdf') {
                $pdf = new \Imagick();
                $pdf->readImage($file);
                $imagick->addImage($pdf);
            } else {
                $img = new \Imagick($file);
                $imagick->addImage($img);
            }
        }
        // 合并为一张图片
        $imagick = $imagick->appendImages(true);

        $res = true;
        // 保存合并后的图片到指定路径或返回base64图片
        if (!empty($outputPath)) {
            $imagick->writeImages($outputPath, true);
        } else {
            $res = 'data:image/' . $imagick->getImageFormat() . ';base64,' . base64_encode($imagick->getImageBlob());

            // 清理资源，释放资源
            $imagick->clear();
            $imagick->destroy();
        }
        // 清理资源，释放资源
        $imagick->clear();
        $imagick->destroy();
        return $res;
    }

    /**
     * 合并多个PDF文件为一个PDF文件
     *
     * @param array       $filePaths
     * @param string|null $outputPath
     *
     * @return bool|string
     * @throws ImagickException
     */
    public function pdfOrImgMergeToPdf(array $filePaths = [], ?string $outputPath = null)
    {
        if (empty($filePaths)) {
            throw new \ImagickException("文件路径不能为空");
        }
        // 创建ImageMagick对象
        $imagick = new Imagick();

        foreach ($filePaths as $file) {
            // 判断文件类型是pdf还是图片
            $fileType = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
                throw new \ImagickException("文件类型不支持");
            }
            if ($fileType == 'pdf') {
                $pdf = new \Imagick();
                $pdf->readImage($file);
                $imagick->addImage($pdf);
            } else {
                $img = new \Imagick($file);
                $imagick->addImage($img);
            }
        }
        // 设置PDF输出选项
        $imagick->setImageFormat('pdf');

        $res = true;
        // 保存合并后的图片到指定路径或返回base64图片
        if (!empty($outputPath)) {
            $imagick->writeImages($outputPath, true);
        } else {
            $res = 'data:image/' . $imagick->getImageFormat() . ';base64,' . base64_encode($imagick->getImageBlob());
        }
        // 清理资源，释放资源
        $imagick->clear();
        $imagick->destroy();
        return $res;

    }

    /**
     * 裁剪图片
     *
     * @param int $x      起始X坐标
     * @param int $y      起始Y坐标
     * @param int $width  裁剪宽度
     * @param int $height 裁剪高度
     *
     * @throws \ImagickException
     */
    public function cropImage(int $x, int $y, int $width, int $height)
    {
        try {
            $this->imagick->cropImage($width, $height, $x, $y);
            $this->imagick->setImagePage(0, 0, 0, 0);
        } catch (\ImagickException $e) {
            throw new \ImagickException("裁剪图片失败：{$e->getMessage()}");
        }
    }

    /**
     * 获取图像信息
     *
     * @return array 图像信息数组，包括宽度、高度、格式等
     * @throws \ImagickException
     */
    public function getImageInfo(): array
    {
        try {
            return [
                'width'  => $this->imagick->getImageWidth(),
                'height' => $this->imagick->getImageHeight(),
                'format' => $this->imagick->getImageFormat(),
            ];
        } catch (\ImagickException $e) {
            throw new \ImagickException("获取图像信息失败：{$e->getMessage()}");
        }
    }

    /**
     * 添加边框
     *
     * @param int    $borderWidth 边框宽度
     * @param string $borderColor 边框颜色，例如：#000000
     *
     * @throws \ImagickException
     */
    public function addBorder(int $borderWidth, string $borderColor)
    {
        try {
            $borderColorPixel = new \ImagickPixel($borderColor);
            $this->imagick->borderImage($borderColorPixel, $borderWidth, $borderWidth);
        } catch (\ImagickException $e) {
            throw new \ImagickException("添加边框失败：{$e->getMessage()}");
        }
    }

    /**
     * 旋转图片
     *
     * @param float  $angle           旋转角度
     * @param string $backgroundColor 背景颜色，例如：#FFFFFF
     *
     * @throws \ImagickException
     */
    public function rotateImage(float $angle, string $backgroundColor = '#FFFFFF')
    {
        try {
            $this->imagick->rotateImage(new \ImagickPixel($backgroundColor), $angle);
        } catch (\ImagickException $e) {
            throw new \ImagickException("旋转图片失败：{$e->getMessage()}");
        }
    }

    /**
     * 获取像素颜色值
     *
     * @param int $x 像素的X坐标
     * @param int $y 像素的Y坐标
     *
     * @return array 像素的颜色值，包括红、绿、蓝三个通道
     * @throws \ImagickException
     */
    public function getPixelColor(int $x, int $y): array
    {
        try {
            $pixel = $this->imagick->getImagePixelColor($x, $y);
            $color = $pixel->getColor();

            return [
                'red'   => $color['r'],
                'green' => $color['g'],
                'blue'  => $color['b'],
            ];
        } catch (\ImagickException $e) {
            throw new \ImagickException("获取像素颜色值失败：{$e->getMessage()}");
        }
    }

    /**
     * 转换图像格式
     *
     * @param string $format 目标格式，例如：jpeg、png
     *
     * @throws \ImagickException
     */
    public function convertImageFormat(string $format)
    {
        try {
            $this->imagick->setImageFormat($format);
        } catch (\ImagickException $e) {
            throw new \ImagickException("转换图像格式失败：{$e->getMessage()}");
        }
    }

    /**
     * 处理透明度
     *
     * @param int $opacity 透明度值，范围为 0 到 100
     *
     * @throws \ImagickException
     */
    public function processOpacity(int $opacity)
    {
        try {
            $this->imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
            $this->imagick->setImageBackgroundColor(new \ImagickPixel('transparent'));
            $this->imagick->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity / 100, \Imagick::CHANNEL_ALPHA);
        } catch (\ImagickException $e) {
            throw new \ImagickException("处理透明度失败：{$e->getMessage()}");
        }
    }

    /**
     * 添加水印
     *
     * @param string $watermarkPath 水印图片路径
     * @param int    $x             水印在图像上的X坐标
     * @param int    $y             水印在图像上的Y坐标
     * @param int    $opacity       水印透明度，范围为 0 到 100
     *
     * @throws \ImagickException
     */
    public function addWatermark(string $watermarkPath, int $x, int $y, int $opacity)
    {
        try {
            $watermark = new \Imagick($watermarkPath);
            $watermark->setImageOpacity($opacity / 100);

            $this->imagick->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
        } catch (\ImagickException $e) {
            throw new \ImagickException("添加水印失败：{$e->getMessage()}");
        }
    }

    /**
     * 获取当前图像的宽度
     *
     * @return int 当前图像的宽度
     */
    public function getWidth()
    {
        return $this->imagick->getImageWidth();
    }

    /**
     * 获取当前图像的高度
     *
     * @return int 当前图像的高度
     */
    public function getHeight()
    {
        return $this->imagick->getImageHeight();
    }

    /**
     * 重置所有图像操作
     *
     */
    public function resetImage()
    {
        $this->imagick->clear();
        return $this;
    }

    /**
     * 获取当前图像的MIME类型
     *
     */
    public function getMimeType()
    {
        return $this->imagick->getImageMimeType();
    }

    /**
     * 设置图像的透明度
     *
     * @param float $opacity 透明度值
     *
     * @throws ImagickException
     */
    public function setOpacity($opacity)
    {
        $this->imagick->setImageOpacity($opacity);
    }


    /**
     * 将图像转换为灰度图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertToGrayScale()
    {
        $this->imagick->setImageType(Imagick::IMGTYPE_GRAYSCALEMATTE);
    }

    /**
     * 获取当前图像的文件大小
     *
     * @return int 当前图像的文件大小
     */
    public function getFileSize()
    {
        return $this->imagick->getImageLength();
    }


    /**
     * 将图像转换为黑白图像
     */
    public function convertToBlackAndWhite()
    {
        $this->imagick->transformImageColorspace(\Imagick::COLORSPACE_GRAY) && $this->imagick->thresholdImage(0.5 * \Imagick::getQuantum());
    }

    /**
     * 获取图像的格式
     *
     * @return string 图像的格式
     */
    public function getImageFormat()
    {
        return $this->imagick->getImageFormat();
    }

    /**
     * 将一系列图像按顺序拼接在一起
     *
     * @param bool $stack 是否垂直拼接，如果为false则水平拼接
     *
     */
    public function appendImages($stack)
    {
        if ($stack) {
            $this->imagick->appendImages(true);
        } else {
            $this->imagick->appendImages(false);
        }
    }

    /**
     * 释放与图像关联的内存
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function destroy()
    {
        return $this->imagick->destroy();
    }


    /**
     * Catches calls to undefined methods.
     *
     * Provides magic access to private functions of the class and native public Db functions
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, mixed $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->imagick, $method), $arg);
    }
}
