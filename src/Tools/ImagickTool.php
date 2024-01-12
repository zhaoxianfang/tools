<?php

namespace zxf\Tools;

use Imagick;
use ImagickDraw;
use Exception;

/**
 * 图像处理 (ImageMagick)
 *
 * imagick 3.7 版本
 * 文档 https://www.php.net/imagick
 */
class ImagickTool
{
    private $imagick;

    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('未加载 imagick 扩展.');
        }
        $this->imagick = new Imagick();
    }

    /**
     * 从指定的文件加载图像
     *
     * @param string $filename 文件的路径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function loadImage($filename)
    {
        return $this->imagick->readImage($filename);
    }

    /**
     * 将当前图像保存到指定路径
     *
     * @param string $filename 保存文件的路径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function saveImage($filename)
    {
        return $this->imagick->writeImage($filename);
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
     * 改变图像的大小
     *
     * @param int  $width   新的宽度
     * @param int  $height  新的高度
     * @param bool $bestfit 是否使用最佳匹配
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function resizeImage($width, $height, $bestfit = false)
    {
        //$cols：要调整的新图像的宽度。
        //
        //$rows：要调整的新图像的高度。
        //
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
        return $this->imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, $bestfit);
    }

    /**
     * 旋转图像
     *
     * @param float $angle 旋转角度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function rotateImage($angle)
    {
        return $this->imagick->rotateImage(new ImagickPixel(), $angle);
    }

    /**
     * 在指定位置添加文字水印
     *
     * @param string $text 要添加的文本
     * @param int    $x    X坐标
     * @param int    $y    Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addTextWatermark($text, $x, $y)
    {
        $draw = new ImagickDraw();
        $draw->setFillColor('white');
        $draw->setFontSize(30);
        $this->imagick->annotateImage($draw, $x, $y, 0, $text);
        return true;
    }

    /**
     * 将两张图像合成为一张图像
     *
     * @param ImagickTool $overlayImagick 要叠加的图像
     * @param int         $x              X坐标
     * @param int         $y              Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function compositeImage(ImagickTool $overlayImagick, $x, $y)
    {
        return $this->imagick->compositeImage($overlayImagick->imagick, Imagick::COMPOSITE_ATOP, $x, $y);
    }

    /**
     * 创建缩略图
     *
     * @param int $width  新缩略图的宽度
     * @param int $height 新缩略图的高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function createThumbnail($width, $height)
    {
        $this->imagick->cropThumbnailImage($width, $height);
        return true;
    }

    /**
     * 通过应用模糊效果来平滑图像
     *
     * @param float $radius 模糊半径
     * @param float $sigma  标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyBlur($radius, $sigma)
    {
        return $this->imagick->blurImage($radius, $sigma);
    }

    /**
     * 调整图像的亮度和对比度
     *
     * @param float $brightness 亮度值
     * @param float $contrast   对比度值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adjustBrightnessContrast($brightness, $contrast)
    {
        return $this->imagick->brightnessContrastImage($brightness, $contrast);
    }

    /**
     * 应用灰度效果到图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyGrayscale()
    {
        return $this->imagick->transformImageColorspace(Imagick::COLORSPACE_GRAY);
    }

    /**
     * 应用油画效果到图像
     *
     * @param float $radius 油画半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyOilPaint($radius)
    {
        return $this->imagick->oilPaintImage($radius);
    }

    /**
     * 为图像添加边框
     *
     * @param string $color  边框的颜色
     * @param int    $width  边框的宽度
     * @param int    $height 边框的高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addBorder($color, $width, $height)
    {
        $borderColor = new ImagickPixel($color);
        return $this->imagick->borderImage($borderColor, $width, $height);
    }

    /**
     * 调整图像的色调、饱和度和亮度
     *
     * @param float $hue        色调值
     * @param float $saturation 饱和度值
     * @param float $brightness 亮度值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adjustHueSaturationBrightness($hue, $saturation, $brightness)
    {
        return $this->imagick->modulateImage($brightness, $saturation, $hue);
    }

    /**
     * 为图像添加特殊效果
     *
     * @param int $effectType 特殊效果类型
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addSpecialEffect($effectType)
    {
        switch ($effectType) {
            case 1:
                return $this->imagick->charcoalImage(0.5, 0.5);
            case 2:
                return $this->imagick->embossImage(0.5, 0.1);
            // 添加更多效果类型的case...
            default:
                return false;
        }
    }

    /**
     * 应用像素化效果到图像
     *
     * @param float $amount 像素化程度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyPixelate($amount)
    {
        return $this->imagick->pixelateImage($amount);
    }

    /**
     * 重置所有图像操作
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function resetImage()
    {
        $this->imagick->clear();
        return true;
    }

    /**
     * 扭曲图像
     *
     * @param int   $color  颜色
     * @param float $radius 扭曲半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function distortImage($color, $radius)
    {
        return $this->imagick->radialBlurImage($radius, $color);
    }

    /**
     * 为图像添加阴影效果
     *
     * @param float $opacity 阴影透明度
     * @param float $sigma   x轴模糊半径
     * @param float $x       x偏移量
     * @param float $y       y偏移量
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addShadow($opacity, $sigma, $x, $y)
    {
        return $this->imagick->shadowImage($opacity, $sigma, $x, $y);
    }

    /**
     * 应用描边效果到图像
     *
     * @param string $color 描边的颜色
     * @param float  $width 描边的宽度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyStroke($color, $width)
    {
        $strokeColor = new ImagickPixel($color);
        return $this->imagick->setImageBorderColor($strokeColor) && $this->imagick->setImageMatte(true);
    }

    /**
     * 将图像转换为指定格式
     *
     * @param string $format 目标格式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertToFormat($format)
    {
        return $this->imagick->setImageFormat($format);
    }

    /**
     * 获取当前图像的MIME类型
     *
     * @return string 当前图像的MIME类型
     */
    public function getMimeType()
    {
        return $this->imagick->getImageMimeType();
    }

    /**
     * 创建新的图像实例
     *
     * @param int    $width           新图像的宽度
     * @param int    $height          新图像的高度
     * @param string $backgroundColor 背景颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function createNewImage($width, $height, $backgroundColor)
    {
        $bgColor = new ImagickPixel($backgroundColor);
        return $this->imagick->newImage($width, $height, $bgColor);
    }

    /**
     * 设置图像的透明度
     *
     * @param float $opacity 透明度值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setOpacity($opacity)
    {
        return $this->imagick->setImageOpacity($opacity);
    }

    /**
     * 获取图像的像素信息
     *
     * @param int $x X坐标
     * @param int $y Y坐标
     *
     * @return array 包含指定位置像素信息的关联数组
     */
    public function getPixelInfo($x, $y)
    {
        return $this->imagick->getImagePixelColor($x, $y)->getColor();
    }

    /**
     * 将图像转换为灰度图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertToGrayScale()
    {
        return $this->imagick->setImageType(Imagick::IMGTYPE_GRAYSCALEMATTE);
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
     * 将图像旋转指定角度并裁剪
     *
     * @param float  $angle           旋转角度
     * @param string $backgroundColor 背景颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function rotateAndCropImage($angle, $backgroundColor)
    {
        $bgColor = new ImagickPixel($backgroundColor);
        return $this->imagick->rotateImage($bgColor, $angle)->cropImage($this->imagick->getImageWidth(), $this->imagick->getImageHeight(), 0, 0);
    }

    /**
     * 应用高斯模糊效果到图像
     *
     * @param float $radius 模糊半径
     * @param float $sigma  标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function applyGaussianBlur($radius, $sigma)
    {
        return $this->imagick->gaussianBlurImage($radius, $sigma);
    }

    /**
     * 扭曲图像的某个区域
     *
     * @param float $radius 扭曲半径
     * @param float $angle  扭曲角度
     * @param int   $x      X坐标
     * @param int   $y      Y坐标
     * @param int   $width  区域宽度
     * @param int   $height 区域高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function distortImageRegion($radius, $angle, $x, $y, $width, $height)
    {
        return $this->imagick->distortImage(Imagick::DISTORTION_ARC, [$radius, $angle], false);
    }

    /**
     * 为图像添加噪点
     *
     * @param int   $type   噪点类型
     * @param float $factor 噪点因子
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addNoise($type, $factor)
    {
        switch ($type) {
            case 1:
                return $this->imagick->addNoiseImage(Imagick::NOISE_UNIFORM, $factor);
            case 2:
                return $this->imagick->addNoiseImage(Imagick::NOISE_GAUSSIAN, $factor);
            // 添加更多噪点类型的case...
            default:
                return false;
        }
    }

    /**
     * 给图像添加纹理
     *
     * @param string $texturePath 纹理图像的路径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addTexture($texturePath)
    {
        $texture = new Imagick($texturePath);
        return $this->imagick->textureImage($texture);
    }

    /**
     * 将图像转换为指定的灰度图像
     *
     * @param int $channel 灰度通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertToGrayScaleChannel($channel)
    {
        return $this->imagick->transformImageColorspace($channel);
    }

    /**
     * 将图像转换为指定的色彩空间
     *
     * @param int $colorspace 目标色彩空间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertColorSpace($colorspace)
    {
        return $this->imagick->transformImageColorspace($colorspace);
    }

    /**
     * 通过给定的阈值进行二值化处理
     *
     * @param float $threshold 二值化阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function thresholdImage($threshold)
    {
        return $this->imagick->thresholdImage($threshold * \Imagick::getQuantum());
    }

    /**
     * 获取图像的直方图
     *
     * @return array 包含直方图信息的关联数组
     */
    public function getHistogram()
    {
        return $this->imagick->getImageHistogram();
    }

    /**
     * 将图像转换为黑白图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convertToBlackAndWhite()
    {
        return $this->imagick->transformImageColorspace(\Imagick::COLORSPACE_GRAY) && $this->imagick->thresholdImage(0.5 * \Imagick::getQuantum());
    }

    /**
     * 为图像添加马赛克效果
     *
     * @param int $mosaicSize 马赛克块的尺寸
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addMosaicEffect($mosaicSize)
    {
        return $this->imagick->scaleImage($mosaicSize, $mosaicSize, true);
    }

    /**
     * 设置图像的分辨率
     *
     * @param float $x_resolution x轴分辨率
     * @param float $y_resolution y轴分辨率
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setResolution($x_resolution, $y_resolution)
    {
        return $this->imagick->setImageResolution($x_resolution, $y_resolution);
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
     * 为图像添加水平翻转效果
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function flipImage()
    {
        return $this->imagick->flopImage();
    }

    /**
     * 为图像添加垂直翻转效果
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function flopImage()
    {
        return $this->imagick->flopImage();
    }

    /**
     * 获取图像的文件格式
     *
     * @return string 图像的文件格式
     */
    public function getImageFileFormat()
    {
        return $this->imagick->getImageFormat();
    }

    /**
     * 获取图像的色彩空间
     *
     * @return int 图像的色彩空间
     */
    public function getColorspace()
    {
        return $this->imagick->getColorspace();
    }

    /**
     * 获取图像的压缩类型
     *
     * @return int 图像的压缩类型
     */
    public function getCompression()
    {
        return $this->imagick->getCompression();
    }

    /**
     * 获取图像的质量
     *
     * @return int 图像的质量
     */
    public function getQuality()
    {
        return $this->imagick->getImageCompressionQuality();
    }

    /**
     * 应用自适应模糊效果到图像
     *
     * @param float $radius 模糊半径
     * @param float $sigma  标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adaptiveBlurImage($radius, $sigma)
    {
        return $this->imagick->adaptiveBlurImage($radius, $sigma);
    }

    /**
     * 应用自适应调整大小效果到图像
     *
     * @param int $width  新的宽度
     * @param int $height 新的高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adaptiveResizeImage($width, $height)
    {
        return $this->imagick->adaptiveResizeImage($width, $height);
    }

    /**
     * 应用自适应锐化效果到图像
     *
     * @param float $radius 模糊半径
     * @param float $sigma  标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adaptiveSharpenImage($radius, $sigma)
    {
        return $this->imagick->adaptiveSharpenImage($radius, $sigma);
    }

    /**
     * 应用自适应阈值化效果到图像
     *
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $offset 阈值化的偏移量
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function adaptiveThresholdImage($width, $height, $offset)
    {
        return $this->imagick->adaptiveThresholdImage($width, $height, $offset);
    }

    /**
     * 将另一个图像添加到当前图像
     *
     * @param ImagickTool $overlayImagick 要添加的叠加图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function addImage(ImagickTool $overlayImagick)
    {
        return $this->imagick->addImage($overlayImagick->imagick);
    }

    /**
     * 对图像应用仿射变换
     *
     * @param array $affineMatrix 仿射变换矩阵
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function affineTransformImage($affineMatrix)
    {
        return $this->imagick->affineTransformImage($affineMatrix);
    }

    /**
     * 创建动画效果
     *
     * @param int $delay 每帧之间的延迟时间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function animateImages($delay)
    {
        return $this->imagick->animateImages($delay);
    }

    /**
     * 将一系列图像按顺序拼接在一起
     *
     * @param bool $stack 是否垂直拼接，如果为false则水平拼接
     *
     * @return ImagickTool 新的ImagickTool实例包含拼接后的图像
     */
    public function appendImages($stack)
    {
        $result = new ImagickTool();
        if ($stack) {
            $appended = $this->imagick->appendImages(true);
        } else {
            $appended = $this->imagick->appendImages(false);
        }
        $result->imagick = $appended;
        return $result;
    }

    /**
     * 自动调整图像的级别
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function autoLevelImage()
    {
        return $this->imagick->autoLevelImage();
    }

    /**
     * 计算一系列图像的平均值
     *
     * @return ImagickTool 新的ImagickTool实例包含计算得到的平均图像
     */
    public function averageImages()
    {
        $result          = new ImagickTool();
        $averaged        = $this->imagick->averageImages();
        $result->imagick = $averaged;
        return $result;
    }

    /**
     * 对图像进行黑色阈值处理
     *
     * @param mixed $threshold 用于阈值化的阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function blackThresholdImage($threshold)
    {
        return $this->imagick->blackThresholdImage($threshold);
    }

    /**
     * 对图像进行蓝色偏移处理
     *
     * @param float $factor 偏移因子
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function blueShiftImage($factor)
    {
        return $this->imagick->blueShiftImage($factor);
    }

    /**
     * 在图像中裁剪指定的矩形区域
     *
     * @param int $width  裁剪区域的宽度
     * @param int $height 裁剪区域的高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function chopImage($width, $height, $x, $y)
    {
        return $this->imagick->chopImage($width, $height, $x, $y);
    }

    /**
     * 对图像进行颜色限制
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function clampImage()
    {
        return $this->imagick->clampImage();
    }

    /**
     * 在指定的路径周围裁剪图像
     *
     * @param string $pathname 路径名称
     * @param bool   $inside   是否在路径内部进行裁剪
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function clipImage($pathname, $inside)
    {
        return $this->imagick->clipImage($pathname, $inside);
    }

    /**
     * 在指定的路径周围裁剪图像
     *
     * @param string $pathname 路径名称
     * @param bool   $inside   是否在路径内部进行裁剪
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function clipImagePath($pathname, $inside)
    {
        return $this->imagick->clipImagePath($pathname, $inside);
    }

    /**
     * 在指定的路径周围裁剪图像
     *
     * @param string $pathname 路径名称
     * @param bool   $inside   是否在路径内部进行裁剪
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function clipPathImage($pathname, $inside)
    {
        return $this->imagick->clipPathImage($pathname, $inside);
    }

    /**
     * 克隆当前图像对象
     *
     * @return ImagickTool 新的ImagickTool实例，是当前实例的副本
     */
    public function clone()
    {
        $result          = new ImagickTool();
        $result->imagick = clone $this->imagick;
        return $result;
    }

    /**
     * 使用颜色查找表对图像进行颜色转换
     *
     * @param ImagickTool $lookupTable 要应用的颜色查找表
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function clutImage(ImagickTool $lookupTable)
    {
        return $this->imagick->clutImage($lookupTable->imagick);
    }

    /**
     * 将多帧图像分解为单帧图像
     *
     * @return array 包含分解得到的单帧图像的ImagickTool实例的数组
     */
    public function coalesceImages()
    {
        $result    = array();
        $coalesced = $this->imagick->coalesceImages();
        foreach ($coalesced as $frame) {
            $tool          = new ImagickTool();
            $tool->imagick = $frame;
            $result[]      = $tool;
        }
        return $result;
    }

    /**
     * 对图像进行着色处理
     *
     * @param mixed $colorizeColor 着色的颜色
     * @param mixed $opacityColor  不透明度颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function colorizeImage($colorizeColor, $opacityColor)
    {
        return $this->imagick->colorizeImage($colorizeColor, $opacityColor);
    }

    /**
     * 对图像应用颜色矩阵转换
     *
     * @param array $colorMatrix 颜色矩阵
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function colorMatrixImage($colorMatrix)
    {
        return $this->imagick->colorMatrixImage($colorMatrix);
    }

    /**
     * 将两个图像组合成一个
     *
     * @param int $channel 合并的通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function combineImages($channel)
    {
        return $this->imagick->combineImages($channel);
    }

    /**
     * 向图像添加注释信息
     *
     * @param string $comment 注释信息
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function commentImage($comment)
    {
        return $this->imagick->commentImage($comment);
    }

    /**
     * 比较两个图像的指定通道
     *
     * @param ImagickTool $image       要比较的图像
     * @param int         $channelType 比较的通道类型
     * @param int         $metricType  度量类型
     *
     * @return array 包含比较结果信息的关联数组
     */
    public function compareImageChannels(ImagickTool $image, $channelType, $metricType)
    {
        return $this->imagick->compareImageChannels($image->imagick, $channelType, $metricType);
    }

    /**
     * 比较一组图像的各个层
     *
     * @param int $method 比较的方法
     *
     * @return ImagickTool 新的ImagickTool实例包含比较得到的图像
     */
    public function compareImageLayers($method)
    {
        $result          = new ImagickTool();
        $compared        = $this->imagick->compareImageLayers($method);
        $result->imagick = $compared;
        return $result;
    }

    /**
     * 比较两个图像并返回比较结果
     *
     * @param ImagickTool $image  要比较的图像
     * @param int         $metric 比较的度量
     *
     * @return array 包含比较结果信息的关联数组
     */
    public function compareImages(ImagickTool $image, $metric)
    {
        return $this->imagick->compareImages($image->imagick, $metric);
    }

    /**
     * 对图像进行对比度拉伸
     *
     * @param float $blackPoint 黑点
     * @param float $whitePoint 白点
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function contrastStretchImage($blackPoint, $whitePoint)
    {
        return $this->imagick->contrastStretchImage($blackPoint, $whitePoint);
    }

    /**
     * 对图像应用卷积
     *
     * @param array $kernelMatrix 卷积核矩阵
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function convolveImage($kernelMatrix)
    {
        return $this->imagick->convolveImage($kernelMatrix);
    }

    /**
     * 返回图像的帧数
     *
     * @return int 图像的帧数
     */
    public function count()
    {
        return $this->imagick->count();
    }

    /**
     * 裁剪图像
     *
     * @param int $width  新的宽度
     * @param int $height 新的高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function cropImage($width, $height, $x, $y)
    {
        return $this->imagick->cropImage($width, $height, $x, $y);
    }

    /**
     * 裁剪图像为缩略图
     *
     * @param int $width  新的宽度
     * @param int $height 新的高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function cropThumbnailImage($width, $height)
    {
        return $this->imagick->cropThumbnailImage($width, $height);
    }

    /**
     * 获取当前图像的迭代位置
     *
     * @return int 当前图像的迭代位置
     */
    public function current()
    {
        return $this->imagick->current();
    }

    /**
     * 重复调色板的颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function cycleColormapImage()
    {
        return $this->imagick->cycleColormapImage();
    }

    /**
     * 解密图像
     *
     * @param string $passphrase 解密口令
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function decipherImage($passphrase)
    {
        return $this->imagick->decipherImage($passphrase);
    }

    /**
     * 将一系列图像拆解为单帧图像
     *
     * @return array 包含拆解得到的单帧图像的ImagickTool实例的数组
     */
    public function deconstructImages()
    {
        $result        = array();
        $deconstructed = $this->imagick->deconstructImages();
        foreach ($deconstructed as $frame) {
            $tool          = new ImagickTool();
            $tool->imagick = $frame;
            $result[]      = $tool;
        }
        return $result;
    }

    /**
     * 删除图像的特定属性
     *
     * @param string $name 属性名称
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function deleteImageArtifact($name)
    {
        return $this->imagick->deleteImageArtifact($name);
    }

    /**
     * 删除图像的特定属性
     *
     * @param string $name 属性名称
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function deleteImageProperty($name)
    {
        return $this->imagick->deleteImageProperty($name);
    }

    /**
     * 对图像进行去斜校正
     *
     * @param float $threshold 去斜校正的阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function deskewImage($threshold)
    {
        return $this->imagick->deskewImage($threshold);
    }

    /**
     * 对图像进行去斑处理
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function despeckleImage()
    {
        return $this->imagick->despeckleImage();
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
     * 显示图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function displayImage()
    {
        return $this->imagick->displayImage();
    }

    /**
     * 显示一系列图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function displayImages()
    {
        return $this->imagick->displayImages();
    }

    /**
     * 在图像上绘制一些内容
     *
     * @param ImagickDraw $draw 绘制对象
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function drawImage(ImagickDraw $draw)
    {
        return $this->imagick->drawImage($draw);
    }

    /**
     * 对图像进行边缘检测
     *
     * @param float $radius 边缘半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function edgeImage($radius)
    {
        return $this->imagick->edgeImage($radius);
    }

    /**
     * 对图像应用浮雕效果
     *
     * @param float $radius 浮雕半径
     * @param float $sigma  浮雕标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function embossImage($radius, $sigma)
    {
        return $this->imagick->embossImage($radius, $sigma);
    }

    /**
     * 加密图像
     *
     * @param string $passphrase 加密口令
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function encipherImage($passphrase)
    {
        return $this->imagick->encipherImage($passphrase);
    }

    /**
     * 对图像进行增强处理
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function enhanceImage()
    {
        return $this->imagick->enhanceImage();
    }

    /**
     * 对图像进行直方图均衡化
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function equalizeImage()
    {
        return $this->imagick->equalizeImage();
    }

    /**
     * 对图像应用某种操作
     *
     * @param int   $op       操作类型
     * @param float $constant 操作的常量值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function evaluateImage($op, $constant)
    {
        return $this->imagick->evaluateImage($op, $constant);
    }

    /**
     * 导出图像的像素数据
     *
     * @param int    $x      X坐标
     * @param int    $y      Y坐标
     * @param int    $width  宽度
     * @param int    $height 高度
     * @param string $map    映射类型
     *
     * @return array 包含像素数据的数组
     */
    public function exportImagePixels($x, $y, $width, $height, $map)
    {
        return $this->imagick->exportImagePixels($x, $y, $width, $height, $map);
    }

    /**
     * 调整图像的大小
     *
     * @param int $width  新的宽度
     * @param int $height 新的高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function extentImage($width, $height, $x, $y)
    {
        return $this->imagick->extentImage($width, $height, $x, $y);
    }

    /**
     * 对图像应用滤镜
     *
     * @param int   $filterType 滤镜类型
     * @param float $radius     滤镜半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function filter($filterType, $radius)
    {
        return $this->imagick->filter($filterType, $radius);
    }

    /**
     * 在图像中进行泛洪填充
     *
     * @param mixed $fillColor   填充的颜色
     * @param float $fuzz        模糊因子
     * @param mixed $borderColor 边界颜色
     * @param int   $x           X坐标
     * @param int   $y           Y坐标
     * @param bool  $invert      是否反转
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function floodFillPaintImage($fillColor, $fuzz, $borderColor, $x, $y, $invert)
    {
        return $this->imagick->floodFillPaintImage($fillColor, $fuzz * \Imagick::getQuantum(), $borderColor, $x, $y, $invert);
    }

    /**
     * 对图像应用傅里叶变换
     *
     * @param bool $inverse 是否是逆变换
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function forwardFourierTransformImage($inverse)
    {
        return $this->imagick->forwardFourierTransformImage($inverse);
    }

    /**
     * 为图像添加边框
     *
     * @param mixed $color      边框颜色
     * @param int   $width      边框宽度
     * @param int   $height     边框高度
     * @param int   $innerBevel 内部斜角
     * @param int   $outerBevel 外部斜角
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function frameImage($color, $width, $height, $innerBevel, $outerBevel)
    {
        return $this->imagick->frameImage($color, $width, $height, $innerBevel, $outerBevel);
    }

    /**
     * 对图像应用某种函数
     *
     * @param int   $functionType 函数类型
     * @param array $arguments    函数参数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function functionImage($functionType, $arguments)
    {
        return $this->imagick->functionImage($functionType, $arguments);
    }

    /**
     * 对图像应用FX表达式
     *
     * @param string $expression FX表达式
     *
     * @return mixed 成功时返回处理后的图像，失败时返回false
     */
    public function fxImage($expression)
    {
        return $this->imagick->fxImage($expression);
    }

    /**
     * 对图像应用伽马校正
     *
     * @param float $gamma 伽马值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function gammaImage($gamma)
    {
        return $this->imagick->gammaImage($gamma);
    }

    /**
     * 对图像应用高斯模糊
     *
     * @param float $radius 模糊半径
     * @param float $sigma  模糊标准偏差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function gaussianBlurImage($radius, $sigma)
    {
        return $this->imagick->gaussianBlurImage($radius, $sigma);
    }

    /**
     * 获取图像的压缩质量
     *
     * @return int 图像的压缩质量
     */
    public function getCompressionQuality()
    {
        return $this->imagick->getCompressionQuality();
    }

    /**
     * 获取图像的版权信息
     *
     * @return string 图像的版权信息
     */
    public function getCopyright()
    {
        return $this->imagick->getCopyright();
    }

    /**
     * 获取图像的文件名
     *
     * @return string 图像的文件名
     */
    public function getFilename()
    {
        return $this->imagick->getFilename();
    }

    /**
     * 获取图像的字体信息
     *
     * @return array 包含字体信息的关联数组
     */
    public function getFont()
    {
        return $this->imagick->getFont();
    }

    /**
     * 获取图像的格式
     *
     * @return string 图像的格式
     */
    public function getFormat()
    {
        return $this->imagick->getFormat();
    }

    /**
     * 获取图像的重心
     *
     * @return int 图像的重心
     */
    public function getGravity()
    {
        return $this->imagick->getGravity();
    }

    /**
     * 获取主页URL
     *
     * @return string 主页URL
     */
    public function getHomeURL()
    {
        return $this->imagick->getHomeURL();
    }

    /**
     * 获取图像对象
     *
     * @return Imagick 当前图像对象
     */
    public function getImage()
    {
        return $this->imagick->getImage();
    }

    /**
     * 获取图像的Alpha通道
     *
     * @return int 图像的Alpha通道
     */
    public function getImageAlphaChannel()
    {
        return $this->imagick->getImageAlphaChannel();
    }

    /**
     * 获取图像的特定属性
     *
     * @param string $name 属性名称
     *
     * @return string 图像的特定属性值
     */
    public function getImageArtifact($name)
    {
        return $this->imagick->getImageArtifact($name);
    }

    /**
     * 获取图像的属性
     *
     * @param string $name 属性名称
     *
     * @return string 图像的属性值
     */
    public function getImageAttribute($name)
    {
        return $this->imagick->getImageAttribute($name);
    }

    /**
     * 获取图像的背景颜色
     *
     * @return ImagickPixel 图像的背景颜色
     */
    public function getImageBackgroundColor()
    {
        return $this->imagick->getImageBackgroundColor();
    }

    /**
     * 获取图像的二进制数据
     *
     * @return string 图像的二进制数据
     */
    public function getImageBlob()
    {
        return $this->imagick->getImageBlob();
    }

    /**
     * 获取图像的蓝色主色调
     *
     * @return array 包含蓝色主色调信息的关联数组
     */
    public function getImageBluePrimary()
    {
        return $this->imagick->getImageBluePrimary();
    }

    /**
     * 获取图像的边框颜色
     *
     * @return ImagickPixel 图像的边框颜色
     */
    public function getImageBorderColor()
    {
        return $this->imagick->getImageBorderColor();
    }

    /**
     * 获取图像指定通道的深度
     *
     * @param int $channel 指定的通道
     *
     * @return int 图像指定通道的深度
     */
    public function getImageChannelDepth($channel)
    {
        return $this->imagick->getImageChannelDepth($channel);
    }

    /**
     * 获取图像通道的失真
     *
     * @param int $referenceType 参考类型
     * @param int $channel       指定的通道
     *
     * @return float 图像通道的失真
     */
    public function getImageChannelDistortion($referenceType, $channel)
    {
        return $this->imagick->getImageChannelDistortion($referenceType, $channel);
    }

    /**
     * 获取图像通道的失真
     *
     * @param int $referenceType 参考类型
     *
     * @return array 包含图像通道失真信息的关联数组
     */
    public function getImageChannelDistortions($referenceType)
    {
        return $this->imagick->getImageChannelDistortions($referenceType);
    }

    /**
     * 获取图像通道的极值
     *
     * @param int $channel 指定的通道
     *
     * @return array 包含图像通道极值信息的关联数组
     */
    public function getImageChannelExtrema($channel)
    {
        return $this->imagick->getImageChannelExtrema($channel);
    }

    /**
     * 获取图像通道的峭度
     *
     * @param int $channel 指定的通道
     *
     * @return array 包含图像通道峭度信息的关联数组
     */
    public function getImageChannelKurtosis($channel)
    {
        return $this->imagick->getImageChannelKurtosis($channel);
    }

    /**
     * 获取图像通道的均值
     *
     * @param int $channel 指定的通道
     *
     * @return array 包含图像通道均值信息的关联数组
     */
    public function getImageChannelMean($channel)
    {
        return $this->imagick->getImageChannelMean($channel);
    }

    /**
     * 获取图像通道的范围
     *
     * @param int $channel 指定的通道
     *
     * @return array 包含图像通道范围信息的关联数组
     */
    public function getImageChannelRange($channel)
    {
        return $this->imagick->getImageChannelRange($channel);
    }

    /**
     * 获取图像通道的统计信息
     *
     * @return array 包含图像通道统计信息的关联数组
     */
    public function getImageChannelStatistics()
    {
        return $this->imagick->getImageChannelStatistics();
    }

    /**
     * 获取图像的剪裁掩码
     *
     * @return Imagick 图像的剪裁掩码
     */
    public function getImageClipMask()
    {
        return $this->imagick->getImageClipMask();
    }

    /**
     * 获取图像调色板的颜色
     *
     * @param int $index 调色板索引
     *
     * @return ImagickPixel 调色板的颜色
     */
    public function getImageColormapColor($index)
    {
        return $this->imagick->getImageColormapColor($index);
    }

    /**
     * 获取图像的颜色数目
     *
     * @return int 图像的颜色数目
     */
    public function getImageColors()
    {
        return $this->imagick->getImageColors();
    }

    /**
     * 获取图像的颜色空间
     *
     * @return int 图像的颜色空间
     */
    public function getImageColorspace()
    {
        return $this->imagick->getImageColorspace();
    }

    /**
     * 获取图像的合成模式
     *
     * @return int 图像的合成模式
     */
    public function getImageCompose()
    {
        return $this->imagick->getImageCompose();
    }

    /**
     * 获取图像的压缩类型
     *
     * @return int 图像的压缩类型
     */
    public function getImageCompression()
    {
        return $this->imagick->getImageCompression();
    }

    /**
     * 获取图像的压缩质量
     *
     * @return int 图像的压缩质量
     */
    public function getImageCompressionQuality()
    {
        return $this->imagick->getImageCompressionQuality();
    }

    /**
     * 获取图像的延迟时间
     *
     * @return int 图像的延迟时间
     */
    public function getImageDelay()
    {
        return $this->imagick->getImageDelay();
    }

    /**
     * 获取图像的深度
     *
     * @return int 图像的深度
     */
    public function getImageDepth()
    {
        return $this->imagick->getImageDepth();
    }

    /**
     * 获取图像的处理方式
     *
     * @return int 图像的处理方式
     */
    public function getImageDispose()
    {
        return $this->imagick->getImageDispose();
    }

    /**
     * 获取图像的失真
     *
     * @param ImagickTool $referenceImage 参考图像
     * @param int         $metricType     度量类型
     *
     * @return float 图像的失真
     */
    public function getImageDistortion(ImagickTool $referenceImage, $metricType)
    {
        return $this->imagick->getImageDistortion($referenceImage->imagick, $metricType);
    }

    /**
     * 获取图像的极值
     *
     * @return array 包含图像极值信息的关联数组
     */
    public function getImageExtrema()
    {
        return $this->imagick->getImageExtrema();
    }

    /**
     * 获取图像的文件名
     *
     * @return string 图像的文件名
     */
    public function getImageFilename()
    {
        return $this->imagick->getImageFilename();
    }

    /**
     * 获取图像的伽马值
     *
     * @return float 图像的伽马值
     */
    public function getImageGamma()
    {
        return $this->imagick->getImageGamma();
    }

    /**
     * 获取图像的几何信息
     *
     * @return array 包含图像几何信息的关联数组
     */
    public function getImageGeometry()
    {
        return $this->imagick->getImageGeometry();
    }

    /**
     * 获取图像的重心
     *
     * @return int 图像的重心
     */
    public function getImageGravity()
    {
        return $this->imagick->getImageGravity();
    }

    /**
     * 获取图像的绿色主色调
     *
     * @return array 包含绿色主色调信息的关联数组
     */
    public function getImageGreenPrimary()
    {
        return $this->imagick->getImageGreenPrimary();
    }

    /**
     * 获取图像的高度
     *
     * @return int 图像的高度
     */
    public function getImageHeight()
    {
        return $this->imagick->getImageHeight();
    }

    /**
     * 获取图像的直方图
     *
     * @return array 包含图像直方图信息的关联数组
     */
    public function getImageHistogram()
    {
        return $this->imagick->getImageHistogram();
    }

    /**
     * 获取图像的索引
     *
     * @return int 图像的索引
     */
    public function getImageIndex()
    {
        return $this->imagick->getImageIndex();
    }

    /**
     * 获取图像的交错方案
     *
     * @return int 图像的交错方案
     */
    public function getImageInterlaceScheme()
    {
        return $this->imagick->getImageInterlaceScheme();
    }

    /**
     * 获取图像的插值方法
     *
     * @return int 图像的插值方法
     */
    public function getImageInterpolateMethod()
    {
        return $this->imagick->getImageInterpolateMethod();
    }

    /**
     * 获取图像的迭代次数
     *
     * @return int 图像的迭代次数
     */
    public function getImageIterations()
    {
        return $this->imagick->getImageIterations();
    }

    /**
     * 获取图像的长度
     *
     * @return int 图像的长度
     */
    public function getImageLength()
    {
        return $this->imagick->getImageLength();
    }

    /**
     * 检查图像是否有Alpha通道
     *
     * @return bool 图像是否有Alpha通道
     */
    public function getImageMatte()
    {
        return $this->imagick->getImageMatte();
    }

    /**
     * 获取图像的Alpha通道颜色
     *
     * @return ImagickPixel 图像的Alpha通道颜色
     */
    public function getImageMatteColor()
    {
        return $this->imagick->getImageMatteColor();
    }

    /**
     * 获取图像的MIME类型
     *
     * @return string 图像的MIME类型
     */
    public function getImageMimeType()
    {
        return $this->imagick->getImageMimeType();
    }

    /**
     * 获取图像的方向
     *
     * @return int 图像的方向
     */
    public function getImageOrientation()
    {
        return $this->imagick->getImageOrientation();
    }

    /**
     * 获取图像的页面信息
     *
     * @return array 包含图像页面信息的关联数组
     */
    public function getImagePage()
    {
        return $this->imagick->getImagePage();
    }

    /**
     * 获取图像指定像素的颜色
     *
     * @param int $x X坐标
     * @param int $y Y坐标
     *
     * @return ImagickPixel 指定像素的颜色
     */
    public function getImagePixelColor($x, $y)
    {
        return $this->imagick->getImagePixelColor($x, $y);
    }

    /**
     * 获取图像的指定属性
     *
     * @param string $name 属性名称
     *
     * @return string 图像的指定属性
     */
    public function getImageProperty($name)
    {
        return $this->imagick->getImageProperty($name);
    }

    /**
     * 获取图像的所有属性
     *
     * @param string $pattern 匹配模式
     *
     * @return array 包含图像属性的关联数组
     */
    public function getImageProperties($pattern)
    {
        return $this->imagick->getImageProperties($pattern);
    }

    /**
     * 获取图像的所有属性
     *
     * @return array 包含图像属性的关联数组
     */
    public function getImageProfiles()
    {
        return $this->imagick->getImageProfiles('*');
    }

    /**
     * 获取图像的特定配置文件
     *
     * @param string $name 配置文件名称
     *
     * @return string 图像的特定配置文件
     */
    public function getImageProfile($name)
    {
        return $this->imagick->getImageProfile($name);
    }

    /**
     * 获取图像的红色主色调
     *
     * @return array 包含红色主色调信息的关联数组
     */
    public function getImageRedPrimary()
    {
        return $this->imagick->getImageRedPrimary();
    }

    /**
     * 获取图像的指定区域
     *
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return ImagickTool 新的ImagickTool实例包含指定区域的图像
     */
    public function getImageRegion($width, $height, $x, $y)
    {
        $result          = new ImagickTool();
        $region          = $this->imagick->getImageRegion($width, $height, $x, $y);
        $result->imagick = $region;
        return $result;
    }

    /**
     * 获取图像的渲染意图
     *
     * @return int 图像的渲染意图
     */
    public function getImageRenderingIntent()
    {
        return $this->imagick->getImageRenderingIntent();
    }

    /**
     * 获取图像的分辨率
     *
     * @return array 包含图像分辨率信息的关联数组
     */
    public function getImageResolution()
    {
        return $this->imagick->getImageResolution();
    }

    /**
     * 获取一组图像的二进制数据
     *
     * @return string 一组图像的二进制数据
     */
    public function getImagesBlob()
    {
        return $this->imagick->getImagesBlob();
    }

    /**
     * 获取图像的场景
     *
     * @return int 图像的场景
     */
    public function getImageScene()
    {
        return $this->imagick->getImageScene();
    }

    /**
     * 获取图像的签名
     *
     * @return string 图像的签名
     */
    public function getImageSignature()
    {
        return $this->imagick->getImageSignature();
    }

    /**
     * 获取图像的每秒滴答数
     *
     * @return int 图像的每秒滴答数
     */
    public function getImageTicksPerSecond()
    {
        return $this->imagick->getImageTicksPerSecond();
    }

    /**
     * 获取图像的总油墨密度
     *
     * @return float 图像的总油墨密度
     */
    public function getImageTotalInkDensity()
    {
        return $this->imagick->getImageTotalInkDensity();
    }

    /**
     * 获取图像的类型
     *
     * @return int 图像的类型
     */
    public function getImageType()
    {
        return $this->imagick->getImageType();
    }

    /**
     * 获取图像的单位
     *
     * @return int 图像的单位
     */
    public function getImageUnits()
    {
        return $this->imagick->getImageUnits();
    }

    /**
     * 获取图像的虚拟像素方法
     *
     * @return int 图像的虚拟像素方法
     */
    public function getImageVirtualPixelMethod()
    {
        return $this->imagick->getImageVirtualPixelMethod();
    }

    /**
     * 获取图像的白点
     *
     * @return array 包含图像白点信息的关联数组
     */
    public function getImageWhitePoint()
    {
        return $this->imagick->getImageWhitePoint();
    }

    /**
     * 获取图像的宽度
     *
     * @return int 图像的宽度
     */
    public function getImageWidth()
    {
        return $this->imagick->getImageWidth();
    }

    /**
     * 获取交错方案
     *
     * @return int 交错方案
     */
    public function getInterlaceScheme()
    {
        return $this->imagick->getInterlaceScheme();
    }

    /**
     * 获取迭代器索引
     *
     * @return int 迭代器索引
     */
    public function getIteratorIndex()
    {
        return $this->imagick->getIteratorIndex();
    }

    /**
     * 获取图像的数量
     *
     * @return int 图像的数量
     */
    public function getNumberImages()
    {
        return $this->imagick->getNumberImages();
    }

    /**
     * 获取选项的值
     *
     * @param string $key 选项键名
     *
     * @return string 选项的值
     */
    public function getOption($key)
    {
        return $this->imagick->getOption($key);
    }

    /**
     * 获取包名
     *
     * @return string 包名
     */
    public function getPackageName()
    {
        return $this->imagick->getPackageName();
    }

    /**
     * 获取指定页面
     *
     * @param int $index 页面索引
     *
     * @return ImagickTool 新的ImagickTool实例包含指定页面的图像
     */
    public function getPage($index)
    {
        $result          = new ImagickTool();
        $page            = $this->imagick->getPage($index);
        $result->imagick = $page;
        return $result;
    }

    /**
     * 获取像素迭代器
     *
     * @return ImagickPixelIterator 像素迭代器
     */
    public function getPixelIterator()
    {
        return $this->imagick->getPixelIterator();
    }

    /**
     * 获取像素区域迭代器
     *
     * @param int $x       X坐标
     * @param int $y       Y坐标
     * @param int $columns 列数
     * @param int $rows    行数
     *
     * @return ImagickPixelIterator 像素区域迭代器
     */
    public function getPixelRegionIterator($x, $y, $columns, $rows)
    {
        return $this->imagick->getPixelRegionIterator($x, $y, $columns, $rows);
    }

    /**
     * 获取点大小
     *
     * @return float 点大小
     */
    public function getPointSize()
    {
        return $this->imagick->getPointSize();
    }

    /**
     * 获取量子值
     *
     * @return int 量子值
     */
    public function getQuantum()
    {
        return $this->imagick->getQuantum();
    }

    /**
     * 获取量子深度
     *
     * @return array 包含量子深度信息的关联数组
     */
    public function getQuantumDepth()
    {
        return $this->imagick->getQuantumDepth();
    }

    /**
     * 获取量子范围
     *
     * @return array 包含量子范围信息的关联数组
     */
    public function getQuantumRange()
    {
        return $this->imagick->getQuantumRange();
    }

    /**
     * 获取注册表信息
     *
     * @param string $key 键名
     *
     * @return string 注册表信息
     */
    public function getRegistry($key)
    {
        return $this->imagick->getRegistry($key);
    }

    /**
     * 获取发行日期
     *
     * @return string 发行日期
     */
    public function getReleaseDate()
    {
        return $this->imagick->getReleaseDate();
    }

    /**
     * 获取资源
     *
     * @param int $type 资源类型
     *
     * @return int 资源值
     */
    public function getResource($type)
    {
        return $this->imagick->getResource($type);
    }

    /**
     * 获取资源限制
     *
     * @param int $type 资源类型
     *
     * @return int 资源限制值
     */
    public function getResourceLimit($type)
    {
        return $this->imagick->getResourceLimit($type);
    }

    /**
     * 获取采样因子
     *
     * @return array 包含采样因子信息的关联数组
     */
    public function getSamplingFactors()
    {
        return $this->imagick->getSamplingFactors();
    }

    /**
     * 获取图像的大小
     *
     * @return array 包含图像大小信息的关联数组
     */
    public function getSize()
    {
        return $this->imagick->getSize();
    }

    /**
     * 获取图像大小的偏移量
     *
     * @return array 包含图像大小偏移量信息的关联数组
     */
    public function getSizeOffset()
    {
        return $this->imagick->getSizeOffset();
    }

    /**
     * 获取Imagick的版本信息
     *
     * @return array 包含Imagick版本信息的关联数组
     */
    public function getVersion()
    {
        return $this->imagick->getVersion();
    }

    /**
     * 使用HALD（Harmonic Average Lunar Eclipse) CLUT图像增强图像
     *
     * @param ImagickTool $clut Imagick工具的实例
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function haldClutImage(ImagickTool $clut)
    {
        return $this->imagick->haldClutImage($clut->imagick);
    }

    /**
     * 检查是否有下一张图像
     *
     * @return bool 如果有下一张图像则返回true，否则返回false
     */
    public function hasNextImage()
    {
        return $this->imagick->hasNextImage();
    }

    /**
     * 检查是否有上一张图像
     *
     * @return bool 如果有上一张图像则返回true，否则返回false
     */
    public function hasPreviousImage()
    {
        return $this->imagick->hasPreviousImage();
    }

    /**
     * 识别图像格式
     *
     * @param string $embedText 需要嵌入的文本
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function identifyFormat($embedText)
    {
        return $this->imagick->identifyFormat($embedText);
    }

    /**
     * 识别图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function identifyImage()
    {
        return $this->imagick->identifyImage();
    }

    /**
     * 创建一个新图像作为副本
     *
     * @param int $channel 指定的通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function implodeImage($channel)
    {
        return $this->imagick->implodeImage($channel);
    }

    /**
     * 导入图像像素
     *
     * @param int    $x       X坐标
     * @param int    $y       Y坐标
     * @param int    $width   宽度
     * @param int    $height  高度
     * @param string $map     映射
     * @param int    $storage 存储类型
     * @param array  $pixels  像素数组
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function importImagePixels($x, $y, $width, $height, $map, $storage, $pixels)
    {
        return $this->imagick->importImagePixels($x, $y, $width, $height, $map, $storage, $pixels);
    }

    /**
     * 对图像进行傅里叶反变换
     *
     * @param bool $magnitude 是否是幅度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function inverseFourierTransformImage($magnitude)
    {
        return $this->imagick->inverseFourierTransformImage($magnitude);
    }

    /**
     * 为图像添加标签
     *
     * @param string $label 标签
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function labelImage($label)
    {
        return $this->imagick->labelImage($label);
    }

    /**
     * 对图像进行级别调整
     *
     * @param float $blackPoint 黑点
     * @param float $gamma      伽马值
     * @param float $whitePoint 白点
     * @param int   $channel    指定的通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function levelImage($blackPoint, $gamma, $whitePoint, $channel)
    {
        return $this->imagick->levelImage($blackPoint, $gamma, $whitePoint, $channel);
    }

    /**
     * 对图像进行线性拉伸
     *
     * @param float $blackPoint 黑点
     * @param float $whitePoint 白点
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function linearStretchImage($blackPoint, $whitePoint)
    {
        return $this->imagick->linearStretchImage($blackPoint, $whitePoint);
    }

    /**
     * 对图像进行液态重采样
     *
     * @param int   $width    宽度
     * @param int   $height   高度
     * @param float $deltaX   X方向增量
     * @param float $rigidity 刚性
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function liquidRescaleImage($width, $height, $deltaX, $rigidity)
    {
        return $this->imagick->liquidRescaleImage($width, $height, $deltaX, $rigidity);
    }

    /**
     * 列出注册表信息
     *
     * @return array 包含注册表信息的关联数组
     */
    public function listRegistry()
    {
        return $this->imagick->listRegistry();
    }

    /**
     * 放大图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function magnifyImage()
    {
        return $this->imagick->magnifyImage();
    }

    /**
     * 合并图像图层
     *
     * @param int $layerMethod 图层方法
     *
     * @return ImagickTool 新的ImagickTool实例包含合并后的图像
     */
    public function mergeImageLayers($layerMethod)
    {
        $result          = new ImagickTool();
        $merged          = $this->imagick->mergeImageLayers($layerMethod);
        $result->imagick = $merged;
        return $result;
    }

    /**
     * 缩小图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function minifyImage()
    {
        return $this->imagick->minifyImage();
    }

    /**
     * 调整图像的亮度、色调和饱和度
     *
     * @param float $brightness 亮度
     * @param float $saturation 饱和度
     * @param float $hue        色调
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function modulateImage($brightness, $saturation, $hue)
    {
        return $this->imagick->modulateImage($brightness, $saturation, $hue);
    }

    /**
     * 创建拼贴图像
     *
     * @param ImagickDraw $draw              ImagickDraw实例
     * @param string      $tileGeometry      平铺几何
     * @param string      $thumbnailGeometry 缩略图几何
     * @param int         $mode              模式
     * @param string      $frame             边框
     *
     * @return ImagickTool 新的ImagickTool实例包含拼贴后的图像
     */
    public function montageImage(ImagickDraw $draw, $tileGeometry, $thumbnailGeometry, $mode, $frame)
    {
        $result          = new ImagickTool();
        $montage         = $this->imagick->montageImage($draw, $tileGeometry, $thumbnailGeometry, $mode, $frame);
        $result->imagick = $montage;
        return $result;
    }

    /**
     * 图像变形
     *
     * @param int $numberFrames 帧数
     *
     * @return ImagickTool 新的ImagickTool实例包含变形后的图像
     */
    public function morphImages($numberFrames)
    {
        $result          = new ImagickTool();
        $morphed         = $this->imagick->morphImages($numberFrames);
        $result->imagick = $morphed;
        return $result;
    }

    /**
     * 对图像进行形态学操作
     *
     * @param int           $morphologyMethod 形态学方法
     * @param int           $iterations       迭代次数
     * @param ImagickKernel $kernel           ImagickKernel实例
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function morphology($morphologyMethod, $iterations, ImagickKernel $kernel)
    {
        return $this->imagick->morphology($morphologyMethod, $iterations, $kernel);
    }

    /**
     * 对图像进行运动模糊
     *
     * @param float $radius 半径
     * @param float $sigma  标准差
     * @param float $angle  角度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function motionBlurImage($radius, $sigma, $angle)
    {
        return $this->imagick->motionBlurImage($radius, $sigma, $angle);
    }

    /**
     * 对图像进行取反
     *
     * @param bool $gray 是否是灰度图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function negateImage($gray)
    {
        return $this->imagick->negateImage($gray);
    }

    /**
     * 创建新的图像
     *
     * @param int    $columns    列数
     * @param int    $rows       行数
     * @param mixed  $background 背景
     * @param string $format     格式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function newImage($columns, $rows, $background, $format)
    {
        return $this->imagick->newImage($columns, $rows, $background, $format);
    }

    /**
     * 创建伪图像
     *
     * @param int   $columns    列数
     * @param int   $rows       行数
     * @param mixed $background 背景
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function newPseudoImage($columns, $rows, $background)
    {
        return $this->imagick->newPseudoImage($columns, $rows, $background);
    }

    /**
     * 获取下一张图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function nextImage()
    {
        return $this->imagick->nextImage();
    }

    /**
     * 对图像进行归一化
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function normalizeImage()
    {
        return $this->imagick->normalizeImage();
    }

    /**
     * 对图像进行油画效果处理
     *
     * @param float $radius 半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function oilPaintImage($radius)
    {
        return $this->imagick->oilPaintImage($radius);
    }

    /**
     * 使用指定颜色进行不透明绘制
     *
     * @param mixed $target 指定的颜色
     * @param mixed $fill   指定的填充颜色
     * @param float $fuzz   误差
     * @param bool  $invert 是否反转
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function opaquePaintImage($target, $fill, $fuzz, $invert)
    {
        return $this->imagick->opaquePaintImage($target, $fill, $fuzz, $invert);
    }

    /**
     * 优化图像图层
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function optimizeImageLayers()
    {
        return $this->imagick->optimizeImageLayers();
    }

    /**
     * 对图像进行有序分色处理
     *
     * @param string $thresholdMap 门槛映射
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function orderedPosterizeImage($thresholdMap)
    {
        return $this->imagick->orderedPosterizeImage($thresholdMap);
    }

    /**
     * 检查图像是否有效而无需加载图像数据
     *
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function pingImage($filename)
    {
        return $this->imagick->pingImage($filename);
    }

    /**
     * 从图像Blob检查图像是否有效而无需加载图像数据
     *
     * @param string $image 所需图像的Blob数据
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function pingImageBlob($image)
    {
        return $this->imagick->pingImageBlob($image);
    }

    /**
     * 从图像文件检查图像是否有效而无需加载图像数据
     *
     * @param resource $file     文件资源
     * @param string   $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function pingImageFile($file, $filename)
    {
        return $this->imagick->pingImageFile($file, $filename);
    }

    /**
     * 对图像进行极化处理
     *
     * @param mixed $threshold 极化阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function polaroidImage($threshold)
    {
        return $this->imagick->polaroidImage($threshold);
    }

    /**
     * 对图像进行色阶处理
     *
     * @param int  $levels 色阶等级
     * @param bool $dither 是否抖动
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function posterizeImage($levels, $dither)
    {
        return $this->imagick->posterizeImage($levels, $dither);
    }

    /**
     * 预览图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function previewImages()
    {
        return $this->imagick->previewImages();
    }

    /**
     * 获取上一张图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function previousImage()
    {
        return $this->imagick->previousImage();
    }

    /**
     * 为图像添加描述信息
     *
     * @param string $name        描述名称
     * @param string $profileInfo 描述信息
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function profileImage($name, $profileInfo)
    {
        return $this->imagick->profileImage($name, $profileInfo);
    }

    /**
     * 对图像进行量化处理
     *
     * @param int  $numberColors 颜色数量
     * @param int  $colorspace   颜色空间
     * @param int  $treedepth    树深度
     * @param bool $dither       是否抖动
     * @param bool $measureError 是否测量误差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function quantizeImage($numberColors, $colorspace, $treedepth, $dither, $measureError)
    {
        return $this->imagick->quantizeImage($numberColors, $colorspace, $treedepth, $dither, $measureError);
    }

    /**
     * 对图像集进行量化处理
     *
     * @param int  $numberColors 颜色数量
     * @param int  $colorspace   颜色空间
     * @param int  $treedepth    树深度
     * @param bool $dither       是否抖动
     * @param bool $measureError 是否测量误差
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function quantizeImages($numberColors, $colorspace, $treedepth, $dither, $measureError)
    {
        return $this->imagick->quantizeImages($numberColors, $colorspace, $treedepth, $dither, $measureError);
    }

    /**
     * 查询字体指标
     *
     * @param ImagickDraw $draw ImagickDraw实例
     * @param string      $text 文本
     *
     * @return array 包含字体指标信息的关联数组
     */
    public function queryFontMetrics(ImagickDraw $draw, $text)
    {
        return $this->imagick->queryFontMetrics($draw, $text);
    }

    /**
     * 查询字体
     *
     * @param string $pattern 匹配模式
     *
     * @return array 包含字体信息的关联数组
     */
    public function queryFonts($pattern)
    {
        return $this->imagick->queryFonts($pattern);
    }

    /**
     * 查询格式
     *
     * @param string $pattern 匹配模式
     *
     * @return array 包含格式信息的关联数组
     */
    public function queryFormats($pattern)
    {
        return $this->imagick->queryFormats($pattern);
    }

    /**
     * 对图像进行径向模糊处理
     *
     * @param float $angle 角度
     * @param float $sigma 模糊度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function radialBlurImage($angle, $sigma)
    {
        return $this->imagick->radialBlurImage($angle, $sigma);
    }

    /**
     * 对图像进行抬高处理
     *
     * @param int  $width  宽度
     * @param int  $height 高度
     * @param int  $x      X坐标
     * @param int  $y      Y坐标
     * @param bool $raise  是否抬高
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function raiseImage($width, $height, $x, $y, $raise)
    {
        return $this->imagick->raiseImage($width, $height, $x, $y, $raise);
    }

    /**
     * 对图像进行随机阈值处理
     *
     * @param float $low     阈值下限
     * @param float $high    阈值上限
     * @param int   $channel 通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function randomThresholdImage($low, $high, $channel)
    {
        return $this->imagick->randomThresholdImage($low, $high, $channel);
    }

    /**
     * 从文件中读取图像
     *
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function readImage($filename)
    {
        return $this->imagick->readImage($filename);
    }

    /**
     * 从图像Blob数据中读取图像
     *
     * @param string $image    Blob数据
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function readImageBlob($image, $filename)
    {
        return $this->imagick->readImageBlob($image, $filename);
    }

    /**
     * 从文件中读取多个图像
     *
     * @param string $filenames 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function readImages($filenames)
    {
        return $this->imagick->readImages($filenames);
    }

    /**
     * 重新映射图像颜色
     *
     * @param ImagickTool $replacement 用于替换的ImagickTool实例
     * @param int         $DITHER      抖动
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function remapImage(ImagickTool $replacement, $DITHER)
    {
        return $this->imagick->remapImage($replacement->imagick, $DITHER);
    }

    /**
     * 删除指定位置的图像
     *
     * @param int $index 索引
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function removeImage($index)
    {
        return $this->imagick->removeImage($index);
    }

    /**
     * 移除图像中的指定配置
     *
     * @param string $name 配置名称
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function removeImageProfile($name)
    {
        return $this->imagick->removeImageProfile($name);
    }

    /**
     * 渲染图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function render()
    {
        return $this->imagick->render();
    }

    /**
     * 对图像进行重新采样处理
     *
     * @param float $x_resolution X轴分辨率
     * @param float $y_resolution Y轴分辨率
     * @param int   $filter       过滤器
     * @param float $blur         模糊度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function resampleImage($x_resolution, $y_resolution, $filter, $blur)
    {
        return $this->imagick->resampleImage($x_resolution, $y_resolution, $filter, $blur);
    }

    /**
     * 重置图像页面
     *
     * @param string $page 页面
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function resetImagePage($page)
    {
        return $this->imagick->resetImagePage($page);
    }

    /**
     * 对图像进行滚动
     *
     * @param int $x X坐标
     * @param int $y Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function rollImage($x, $y)
    {
        return $this->imagick->rollImage($x, $y);
    }


    /**
     * 对图像进行旋转模糊处理
     *
     * @param float $angle 角度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function rotationalBlurImage($angle)
    {
        return $this->imagick->rotationalBlurImage($angle);
    }

    /**
     * 对图像进行采样
     *
     * @param int $columns 列数
     * @param int $rows    行数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sampleImage($columns, $rows)
    {
        return $this->imagick->sampleImage($columns, $rows);
    }

    /**
     * 缩放图像
     *
     * @param int $columns 列数
     * @param int $rows    行数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function scaleImage($columns, $rows)
    {
        return $this->imagick->scaleImage($columns, $rows);
    }

    /**
     * 对图像进行分段处理
     *
     * @param int  $colors     颜色数
     * @param int  $colorspace 颜色空间
     * @param bool $verbose    是否详细
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function segmentImage($colors, $colorspace, $verbose)
    {
        return $this->imagick->segmentImage($colors, $colorspace, $verbose);
    }

    /**
     * 对图像进行选择性模糊处理
     *
     * @param float $radius    半径
     * @param float $sigma     标准差
     * @param float $threshold 门槛
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function selectiveBlurImage($radius, $sigma, $threshold)
    {
        return $this->imagick->selectiveBlurImage($radius, $sigma, $threshold);
    }

    /**
     * 分离图像通道
     *
     * @param int $channel 通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function separateImageChannel($channel)
    {
        return $this->imagick->separateImageChannel($channel);
    }

    /**
     * 对图像进行褐色处理
     *
     * @param float $threshold 阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sepiaToneImage($threshold)
    {
        return $this->imagick->sepiaToneImage($threshold);
    }

    /**
     * 设置背景颜色
     *
     * @param mixed $background 背景颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setBackgroundColor($background)
    {
        return $this->imagick->setBackgroundColor($background);
    }

    /**
     * 设置颜色空间
     *
     * @param int $colorspace 颜色空间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setColorspace($colorspace)
    {
        return $this->imagick->setColorspace($colorspace);
    }

    /**
     * 设置压缩
     *
     * @param int $compression 压缩
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setCompression($compression)
    {
        return $this->imagick->setCompression($compression);
    }

    /**
     * 设置压缩质量
     *
     * @param int $quality 质量
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setCompressionQuality($quality)
    {
        return $this->imagick->setCompressionQuality($quality);
    }

    /**
     * 设置文件名
     *
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setFilename($filename)
    {
        return $this->imagick->setFilename($filename);
    }

    /**
     * 设置第一个迭代器
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setFirstIterator()
    {
        return $this->imagick->setFirstIterator();
    }

    /**
     * 设置字体
     *
     * @param string $font 字体
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setFont($font)
    {
        return $this->imagick->setFont($font);
    }

    /**
     * 设置格式
     *
     * @param string $format 格式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setFormat($format)
    {
        return $this->imagick->setFormat($format);
    }

    /**
     * 设置重力
     *
     * @param int $gravity 重力
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setGravity($gravity)
    {
        return $this->imagick->setGravity($gravity);
    }

    /**
     * 设置图像
     *
     * @param Imagick $replace 替换的Imagick实例
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImage(Imagick $replace)
    {
        return $this->imagick->setImage($replace);
    }

    /**
     * 设置图像透明通道
     *
     * @param int $mode 透明通道模式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageAlphaChannel($mode)
    {
        return $this->imagick->setImageAlphaChannel($mode);
    }

    /**
     * 设置图像属性
     *
     * @param string $artifact 图像属性
     * @param string $value    属性值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageArtifact($artifact, $value)
    {
        return $this->imagick->setImageArtifact($artifact, $value);
    }

    /**
     * 设置图像背景颜色
     *
     * @param mixed $background 背景颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageBackgroundColor($background)
    {
        return $this->imagick->setImageBackgroundColor($background);
    }

    /**
     * 设置图像偏置
     *
     * @param float $bias 偏置
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageBias($bias)
    {
        return $this->imagick->setImageBias($bias);
    }

    /**
     * 设置图像偏置量子
     *
     * @param float $bias 偏置量子
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageBiasQuantum($bias)
    {
        return $this->imagick->setImageBiasQuantum($bias);
    }

    /**
     * 设置图像蓝色主色调
     *
     * @param string $x 蓝色主色调X坐标
     * @param string $y 蓝色主色调Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageBluePrimary($x, $y)
    {
        return $this->imagick->setImageBluePrimary($x, $y);
    }

    /**
     * 设置图像边框颜色
     *
     * @param mixed $border 边框颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageBorderColor($border)
    {
        return $this->imagick->setImageBorderColor($border);
    }

    /**
     * 设置图像通道深度
     *
     * @param int $depth 通道深度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageChannelDepth($depth)
    {
        return $this->imagick->setImageChannelDepth($depth);
    }

    /**
     * 设置图像剪切掩码
     *
     * @param Imagick $clip_mask 剪切掩码
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageClipMask(Imagick $clip_mask)
    {
        return $this->imagick->setImageClipMask($clip_mask);
    }

    /**
     * 设置图像颜色映射颜色
     *
     * @param int   $index 索引
     * @param mixed $color 颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageColormapColor($index, $color)
    {
        return $this->imagick->setImageColormapColor($index, $color);
    }

    /**
     * 设置图像颜色空间
     *
     * @param int $colorspace 颜色空间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageColorspace($colorspace)
    {
        return $this->imagick->setImageColorspace($colorspace);
    }

    /**
     * 设置图像组合模式
     *
     * @param int $compose 组合模式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageCompose($compose)
    {
        return $this->imagick->setImageCompose($compose);
    }

    /**
     * 设置图像压缩
     *
     * @param int $compression 压缩
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageCompression($compression)
    {
        return $this->imagick->setImageCompression($compression);
    }

    /**
     * 设置图像压缩质量
     *
     * @param int $quality 压缩质量
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageCompressionQuality($quality)
    {
        return $this->imagick->setImageCompressionQuality($quality);
    }

    /**
     * 设置图像延迟
     *
     * @param int $delay 延迟时间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageDelay($delay)
    {
        return $this->imagick->setImageDelay($delay);
    }

    /**
     * 设置图像深度
     *
     * @param int $depth 深度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageDepth($depth)
    {
        return $this->imagick->setImageDepth($depth);
    }

    /**
     * 设置图像处置方法
     *
     * @param int $dispose 处置方法
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageDispose($dispose)
    {
        return $this->imagick->setImageDispose($dispose);
    }

    /**
     * 设置图像范围
     *
     * @param int $width  宽度
     * @param int $height 高度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageExtent($width, $height)
    {
        return $this->imagick->setImageExtent($width, $height);
    }

    /**
     * 设置图像文件名
     *
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageFilename($filename)
    {
        return $this->imagick->setImageFilename($filename);
    }

    /**
     * 设置图像格式
     *
     * @param string $format 格式
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageFormat($format)
    {
        return $this->imagick->setImageFormat($format);
    }

    /**
     * 设置图像Gamma
     *
     * @param float $gamma Gamma值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageGamma($gamma)
    {
        return $this->imagick->setImageGamma($gamma);
    }

    /**
     * 设置图像重力
     *
     * @param int $gravity 重力
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageGravity($gravity)
    {
        return $this->imagick->setImageGravity($gravity);
    }

    /**
     * 设置图像绿色主色调
     *
     * @param string $x 绿色主色调X坐标
     * @param string $y 绿色主色调Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageGreenPrimary($x, $y)
    {
        return $this->imagick->setImageGreenPrimary($x, $y);
    }

    /**
     * 设置图像交错方案
     *
     * @param int $interlace 交错方案
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageInterlaceScheme($interlace)
    {
        return $this->imagick->setImageInterlaceScheme($interlace);
    }

    /**
     * 设置图像插值方法
     *
     * @param int $interpolate 插值方法
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageInterpolateMethod($interpolate)
    {
        return $this->imagick->setImageInterpolateMethod($interpolate);
    }

    /**
     * 设置图像迭代次数
     *
     * @param int $iterations 迭代次数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageIterations($iterations)
    {
        return $this->imagick->setImageIterations($iterations);
    }

    /**
     * 设置图像掩膜
     *
     * @param mixed $matte 掩膜
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageMatte($matte)
    {
        return $this->imagick->setImageMatte($matte);
    }

    /**
     * 设置图像掩膜颜色
     *
     * @param mixed $matteColor 掩膜颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageMatteColor($matteColor)
    {
        return $this->imagick->setImageMatteColor($matteColor);
    }

    /**
     * 设置图像不透明度
     *
     * @param float $opacity 不透明度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageOpacity($opacity)
    {
        return $this->imagick->setImageOpacity($opacity);
    }

    /**
     * 设置图像方向
     *
     * @param int $orientation 方向
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageOrientation($orientation)
    {
        return $this->imagick->setImageOrientation($orientation);
    }

    /**
     * 设置图像页面
     *
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImagePage($width, $height, $x, $y)
    {
        return $this->imagick->setImagePage($width, $height, $x, $y);
    }

    /**
     * 设置图像属性
     *
     * @param string $name    属性名
     * @param string $profile 图像属性
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageProfile($name, $profile)
    {
        return $this->imagick->setImageProfile($name, $profile);
    }

    /**
     * 设置图像属性
     *
     * @param string $property 属性
     * @param string $value    属性值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageProperty($property, $value)
    {
        return $this->imagick->setImageProperty($property, $value);
    }

    /**
     * 设置图像红色主色调
     *
     * @param string $x 红色主色调X坐标
     * @param string $y 红色主色调Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageRedPrimary($x, $y)
    {
        return $this->imagick->setImageRedPrimary($x, $y);
    }

    /**
     * 设置图像渲染意图
     *
     * @param int $rendering_intent 渲染意图
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageRenderingIntent($rendering_intent)
    {
        return $this->imagick->setImageRenderingIntent($rendering_intent);
    }

    /**
     * 设置图像分辨率
     *
     * @param float $x 分辨率X
     * @param float $y 分辨率Y
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageResolution($x, $y)
    {
        return $this->imagick->setImageResolution($x, $y);
    }

    /**
     * 设置图像场景
     *
     * @param int $scene 场景
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageScene($scene)
    {
        return $this->imagick->setImageScene($scene);
    }

    /**
     * 设置图像每秒帧数
     *
     * @param int $ticksPerSecond 每秒帧数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageTicksPerSecond($ticksPerSecond)
    {
        return $this->imagick->setImageTicksPerSecond($ticksPerSecond);
    }

    /**
     * 设置图像类型
     *
     * @param int $imageType 图像类型
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageType($imageType)
    {
        return $this->imagick->setImageType($imageType);
    }

    /**
     * 设置图像单位
     *
     * @param int $units 单位
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageUnits($units)
    {
        return $this->imagick->setImageUnits($units);
    }

    /**
     * 设置图像虚拟像素方法
     *
     * @param int $method 虚拟像素方法
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageVirtualPixelMethod($method)
    {
        return $this->imagick->setImageVirtualPixelMethod($method);
    }

    /**
     * 设置图像白点
     *
     * @param string $x 白点X坐标
     * @param string $y 白点Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setImageWhitePoint($x, $y)
    {
        return $this->imagick->setImageWhitePoint($x, $y);
    }

    /**
     * 设置交错方案
     *
     * @param int $interlace 交错方案
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setInterlaceScheme($interlace)
    {
        return $this->imagick->setInterlaceScheme($interlace);
    }

    /**
     * 设置迭代器索引
     *
     * @param int $index 索引
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setIteratorIndex($index)
    {
        return $this->imagick->setIteratorIndex($index);
    }

    /**
     * 设置最后一个迭代器
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setLastIterator()
    {
        return $this->imagick->setLastIterator();
    }

    /**
     * 设置选项
     *
     * @param string $key   键
     * @param string $value 值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setOption($key, $value)
    {
        return $this->imagick->setOption($key, $value);
    }

    /**
     * 设置页面
     *
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setPage($width, $height, $x, $y)
    {
        return $this->imagick->setPage($width, $height, $x, $y);
    }

    /**
     * 设置点大小
     *
     * @param float $pointSize 点大小
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setPointSize($pointSize)
    {
        return $this->imagick->setPointSize($pointSize);
    }

    /**
     * 设置进度监视器
     *
     * @param callable $callback 回调函数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setProgressMonitor(callable $callback)
    {
        return $this->imagick->setProgressMonitor($callback);
    }

    /**
     * 设置注册表
     *
     * @param string $key   键
     * @param string $value 值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setRegistry($key, $value)
    {
        return $this->imagick->setRegistry($key, $value);
    }

    /**
     * 设置资源限制
     *
     * @param int $type  类型
     * @param int $limit 限制
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setResourceLimit($type, $limit)
    {
        return $this->imagick->setResourceLimit($type, $limit);
    }

    /**
     * 设置采样因子
     *
     * @param array $factors 采样因子
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setSamplingFactors(array $factors)
    {
        return $this->imagick->setSamplingFactors($factors);
    }

    /**
     * 设置大小
     *
     * @param int $columns 列数
     * @param int $rows    行数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setSize($columns, $rows)
    {
        return $this->imagick->setSize($columns, $rows);
    }

    /**
     * 设置大小偏移
     *
     * @param int $columns 列数
     * @param int $rows    行数
     * @param int $offsetX X偏移
     * @param int $offsetY Y偏移
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setSizeOffset($columns, $rows, $offsetX, $offsetY)
    {
        return $this->imagick->setSizeOffset($columns, $rows, $offsetX, $offsetY);
    }

    /**
     * 设置类型
     *
     * @param int $imageType 图像类型
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function setType($imageType)
    {
        return $this->imagick->setType($imageType);
    }

    /**
     * 添加阴影效果
     *
     * @param float $opacity 不透明度
     * @param float $sigma   X和Y方向标准差
     * @param int   $x       X偏移
     * @param int   $y       Y偏移
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function shadeImage($opacity, $sigma, $x, $y)
    {
        return $this->imagick->shadeImage($opacity, $sigma, $x, $y);
    }

    /**
     * 添加阴影效果
     *
     * @param float $opacity 不透明度
     * @param float $sigma   X和Y方向标准差
     * @param int   $x       X偏移
     * @param int   $y       Y偏移
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function shadowImage($opacity, $sigma, $x, $y)
    {
        return $this->imagick->shadowImage($opacity, $sigma, $x, $y);
    }

    /**
     * 图像锐化
     *
     * @param float $radius  半径
     * @param float $sigma   标准差
     * @param int   $channel 通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sharpenImage($radius, $sigma, $channel)
    {
        return $this->imagick->sharpenImage($radius, $sigma, $channel);
    }

    /**
     * 剃刀刮除图像
     *
     * @param int $columns 列数
     * @param int $rows    行数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function shaveImage($columns, $rows)
    {
        return $this->imagick->shaveImage($columns, $rows);
    }

    /**
     * 图像倾斜
     *
     * @param mixed $background_color 背景颜色
     * @param float $x_shear          X倾斜
     * @param float $y_shear          Y倾斜
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function shearImage($background_color, $x_shear, $y_shear)
    {
        return $this->imagick->shearImage($background_color, $x_shear, $y_shear);
    }

    /**
     * Sigmoidal对比度图像
     *
     * @param bool  $sharpen 是否锐化
     * @param float $alpha   alpha值
     * @param float $beta    beta值
     * @param int   $channel 通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sigmoidalContrastImage($sharpen, $alpha, $beta, $channel)
    {
        return $this->imagick->sigmoidalContrastImage($sharpen, $alpha, $beta, $channel);
    }

    /**
     * 图像素描
     *
     * @param float $radius 半径
     * @param float $sigma  标准差
     * @param float $angle  角度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sketchImage($radius, $sigma, $angle)
    {
        return $this->imagick->sketchImage($radius, $sigma, $angle);
    }

    /**
     * 图像拼接
     *
     * @param bool $stack 堆叠
     *
     * @return Imagick|bool 新的Imagick对象或失败时返回false
     */
    public function smushImages($stack)
    {
        return $this->imagick->smushImages($stack);
    }

    /**
     * 图像曝光
     *
     * @param int $threshold 临界值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function solarizeImage($threshold)
    {
        return $this->imagick->solarizeImage($threshold);
    }

    /**
     * 稀疏颜色图像
     *
     * @param int   $method    方法
     * @param array $arguments 参数
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function sparseColorImage($method, $arguments)
    {
        return $this->imagick->sparseColorImage($method, $arguments);
    }

    /**
     * 图像拼接
     *
     * @param int $width  宽度
     * @param int $height 高度
     * @param int $x      X坐标
     * @param int $y      Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function spliceImage($width, $height, $x, $y)
    {
        return $this->imagick->spliceImage($width, $height, $x, $y);
    }

    /**
     * 图像扩散
     *
     * @param float $radius 半径
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function spreadImage($radius)
    {
        return $this->imagick->spreadImage($radius);
    }

    /**
     * 统计图像
     *
     * @param int $type   类型
     * @param int $width  宽度
     * @param int $height 高度
     *
     * @return array|false 成功时返回统计信息的关联数组，失败时返回false
     */
    public function statisticImage($type, $width, $height)
    {
        return $this->imagick->statisticImage($type, $width, $height);
    }

    /**
     * 隐写图像
     *
     * @param Imagick $watermark 隐写水印
     * @param int     $offset    偏移
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function steganoImage(Imagick $watermark, $offset)
    {
        return $this->imagick->steganoImage($watermark, $offset);
    }

    /**
     * 生成立体图像
     *
     * @param Imagick $offset_image 偏移图像
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function stereoImage(Imagick $offset_image)
    {
        return $this->imagick->stereoImage($offset_image);
    }

    /**
     * 去除图像配置
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function stripImage()
    {
        return $this->imagick->stripImage();
    }

    /**
     * 子图像匹配
     *
     * @param Imagick $Imagick    子图像
     * @param array   $bestMatch  返回的最佳匹配
     * @param float   $similarity 相似度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function subImageMatch(Imagick $Imagick, array &$bestMatch, float &$similarity)
    {
        return $this->imagick->subImageMatch($Imagick, $bestMatch, $similarity);
    }

    /**
     * 图像旋转
     *
     * @param float $degrees          角度
     * @param mixed $background_color 背景颜色
     * @param int   $channel          通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function swirlImage($degrees, $background_color, $channel)
    {
        return $this->imagick->swirlImage($degrees, $background_color, $channel);
    }

    /**
     * 应用纹理到图像
     *
     * @param Imagick $texture 纹理
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function textureImage(Imagick $texture)
    {
        return $this->imagick->textureImage($texture);
    }


    /**
     * 创建缩略图
     *
     * @param int  $columns 列数
     * @param int  $rows    行数
     * @param bool $bestfit 最佳匹配
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function thumbnailImage($columns, $rows, $bestfit = false)
    {
        return $this->imagick->thumbnailImage($columns, $rows, $bestfit);
    }

    /**
     * 图像着色
     *
     * @param mixed $tint    着色
     * @param mixed $opacity 不透明度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function tintImage($tint, $opacity)
    {
        return $this->imagick->tintImage($tint, $opacity);
    }

    /**
     * 转换为字符串
     *
     * @return string 成功时返回图像的字符串表示，失败时返回空字符串
     */
    public function __toString()
    {
        return $this->imagick->__toString();
    }

    /**
     * 图像变换
     *
     * @param ImagickDraw $matrix 图像绘制
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function transformImage(ImagickDraw $matrix)
    {
        return $this->imagick->transformImage($matrix);
    }

    /**
     * 转换图像颜色空间
     *
     * @param int $colorspace 颜色空间
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function transformImageColorspace($colorspace)
    {
        return $this->imagick->transformImageColorspace($colorspace);
    }

    /**
     * 图像透明填充
     *
     * @param mixed $target  指定目标颜色
     * @param float $alpha   透明度
     * @param float $fuzz    模糊度
     * @param bool  $inverse 是否反向
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function transparentPaintImage($target, $alpha, $fuzz, $inverse)
    {
        return $this->imagick->transparentPaintImage($target, $alpha, $fuzz, $inverse);
    }

    /**
     * 图像转置
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function transposeImage()
    {
        return $this->imagick->transposeImage();
    }

    /**
     * 图像转置
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function transverseImage()
    {
        return $this->imagick->transverseImage();
    }

    /**
     * 修剪图像边缘
     *
     * @param float $fuzz 模糊度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function trimImage($fuzz)
    {
        return $this->imagick->trimImage($fuzz);
    }

    /**
     * 获取唯一的图像颜色
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function uniqueImageColors()
    {
        return $this->imagick->uniqueImageColors();
    }

    /**
     * 图像增强
     *
     * @param float $radius    半径
     * @param float $sigma     标准差
     * @param float $amount    量
     * @param float $threshold 临界值
     * @param int   $channel   通道
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function unsharpMaskImage($radius, $sigma, $amount, $threshold, $channel)
    {
        return $this->imagick->unsharpMaskImage($radius, $sigma, $amount, $threshold, $channel);
    }

    /**
     * 检查图像是否有效
     *
     * @return bool 如果图像有效则返回true，否则返回false
     */
    public function valid()
    {
        return $this->imagick->valid();
    }

    /**
     * 添加装饰边框
     *
     * @param mixed $borderColor 边框颜色
     * @param float $width       宽度
     * @param float $height      高度
     * @param float $x           起始X坐标
     * @param float $y           起始Y坐标
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function vignetteImage($borderColor, $width, $height, $x, $y)
    {
        return $this->imagick->vignetteImage($borderColor, $width, $height, $x, $y);
    }

    /**
     * 图像波纹效果
     *
     * @param float $amplitude 振幅
     * @param float $length    长度
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function waveImage($amplitude, $length)
    {
        return $this->imagick->waveImage($amplitude, $length);
    }

    /**
     * 白色阈值处理
     *
     * @param mixed $threshold 阈值
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function whiteThresholdImage($threshold)
    {
        return $this->imagick->whiteThresholdImage($threshold);
    }

    /**
     * 写入图像到文件
     *
     * @param string $filename 文件名
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function writeImage($filename)
    {
        return $this->imagick->writeImage($filename);
    }

    /**
     * 将图像写入文件
     *
     * @param resource $filehandle 文件句柄
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function writeImageFile($filehandle)
    {
        return $this->imagick->writeImageFile($filehandle);
    }

    /**
     * 写入多帧图像到文件
     *
     * @param string $filename 文件名
     * @param bool   $adjoin   是否连续
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function writeImages($filename, $adjoin)
    {
        return $this->imagick->writeImages($filename, $adjoin);
    }

    /**
     * 将多帧图像写入文件
     *
     * @param resource $filehandle 文件句柄
     *
     * @return bool 成功时返回true，失败时返回false
     */
    public function writeImagesFile($filehandle)
    {
        return $this->imagick->writeImagesFile($filehandle);
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
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->imagick, $method), $arg);
    }

}