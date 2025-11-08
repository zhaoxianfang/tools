<?php

namespace zxf\Tools;

use Exception;
use Imagick;
use ImagickDraw;
use ImagickException;
use ImagickPixel;

/**
 * 高级图像处理工具类 (ImagickTool)
 *
 * 基于 Imagick 3.7+ 版本开发的专业级图像处理工具
 * 支持文字生成图片、图像处理、滤镜效果、格式转换等丰富功能
 *
 * @package zxf\Tools
 * @version 2.0
 */
class ImagickTool
{
    private static $instance = null;

    /**
     * Imagick 实例
     * @var Imagick
     */
    private Imagick $imagick;

    /**
     * 资源加载状态
     * @var bool
     */
    private bool $resourceLoaded = false;

    /**
     * 临时文件资源数组，用于资源清理
     * @var array
     */
    private array $tempResources = [];

    // ==========================================
    // 位置常量 - 九宫格位置定义
    // ==========================================

    /**
     * 位置常量 - 左上角
     */
    const POSITION_TOP_LEFT = 1;

    /**
     * 位置常量 - 顶部居中
     */
    const POSITION_TOP_CENTER = 2;

    /**
     * 位置常量 - 右上角
     */
    const POSITION_TOP_RIGHT = 3;

    /**
     * 位置常量 - 左侧居中
     */
    const POSITION_MIDDLE_LEFT = 4;

    /**
     * 位置常量 - 居中
     */
    const POSITION_CENTER = 5;

    /**
     * 位置常量 - 右侧居中
     */
    const POSITION_MIDDLE_RIGHT = 6;

    /**
     * 位置常量 - 左下角
     */
    const POSITION_BOTTOM_LEFT = 7;

    /**
     * 位置常量 - 底部居中
     */
    const POSITION_BOTTOM_CENTER = 8;

    /**
     * 位置常量 - 右下角
     */
    const POSITION_BOTTOM_RIGHT = 9;

    // ==========================================
    // 布局常量 - 拼图布局类型
    // ==========================================

    /**
     * 布局常量 - 水平布局
     */
    const LAYOUT_HORIZONTAL = 'horizontal';

    /**
     * 布局常量 - 垂直布局
     */
    const LAYOUT_VERTICAL = 'vertical';

    /**
     * 布局常量 - 2x2网格布局
     */
    const LAYOUT_GRID_2X2 = 'grid_2x2';

    /**
     * 布局常量 - 3x3网格布局
     */
    const LAYOUT_GRID_3X3 = 'grid_3x3';

    /**
     * 布局常量 - 4x4网格布局
     */
    const LAYOUT_GRID_4X4 = 'grid_4x4';

    /**
     * 布局常量 - 对角线布局
     */
    const LAYOUT_DIAGONAL = 'diagonal';

    /**
     * 布局常量 - 螺旋布局
     */
    const LAYOUT_SPIRAL = 'spiral';

    /**
     * 布局常量 - 圆形布局
     */
    const LAYOUT_CIRCLE = 'circle';

    /**
     * 布局常量 - 马赛克布局
     */
    const LAYOUT_MOSAIC = 'mosaic';

    /**
     * 布局常量 - 拼贴布局
     */
    const LAYOUT_COLLAGE = 'collage';

    // ==========================================
    // 渐变类型常量 - 透明度渐变方向
    // ==========================================

    /**
     * 渐变常量 - 从左到右渐变
     */
    const GRADIENT_LEFT_TO_RIGHT = 'left_to_right';

    /**
     * 渐变常量 - 从右到左渐变
     */
    const GRADIENT_RIGHT_TO_LEFT = 'right_to_left';

    /**
     * 渐变常量 - 从上到下渐变
     */
    const GRADIENT_TOP_TO_BOTTOM = 'top_to_bottom';

    /**
     * 渐变常量 - 从下到上渐变
     */
    const GRADIENT_BOTTOM_TO_TOP = 'bottom_to_top';

    /**
     * 渐变常量 - 从中心到边缘渐变
     */
    const GRADIENT_CENTER_TO_EDGE = 'center_to_edge';

    /**
     * 渐变常量 - 从边缘到中心渐变
     */
    const GRADIENT_EDGE_TO_CENTER = 'edge_to_center';

    /**
     * 渐变常量 - 左上到右下渐变
     */
    const GRADIENT_DIAGONAL_LEFT = 'diagonal_left';

    /**
     * 渐变常量 - 右上到左下渐变
     */
    const GRADIENT_DIAGONAL_RIGHT = 'diagonal_right';

    // ==========================================
    // 滤镜类型常量 - 图像滤镜效果
    // ==========================================

    /**
     * 滤镜常量 - 高斯模糊
     */
    const FILTER_GAUSSIAN_BLUR = 'gaussian_blur';

    /**
     * 滤镜常量 - 运动模糊
     */
    const FILTER_MOTION_BLUR = 'motion_blur';

    /**
     * 滤镜常量 - 径向模糊
     */
    const FILTER_RADIAL_BLUR = 'radial_blur';

    /**
     * 滤镜常量 - 锐化
     */
    const FILTER_SHARPEN = 'sharpen';

    /**
     * 滤镜常量 - 边缘检测
     */
    const FILTER_EDGE_DETECT = 'edge_detect';

    /**
     * 滤镜常量 - 浮雕
     */
    const FILTER_EMBOSS = 'emboss';

    /**
     * 滤镜常量 - 油画
     */
    const FILTER_OIL_PAINT = 'oil_paint';

    /**
     * 滤镜常量 - 水彩
     */
    const FILTER_WATERCOLOR = 'watercolor';

    /**
     * 滤镜常量 - 炭笔画
     */
    const FILTER_CHARCOAL = 'charcoal';

    /**
     * 滤镜常量 - 像素化
     */
    const FILTER_PIXELATE = 'pixelate';

    /**
     * 滤镜常量 - 复古棕褐色
     */
    const FILTER_SEPIA = 'sepia';

    /**
     * 滤镜常量 - 暗角
     */
    const FILTER_VIGNETTE = 'vignette';

    /**
     * 滤镜常量 - 噪点
     */
    const FILTER_NOISE = 'noise';

    /**
     * 滤镜常量 - 曝光过度
     */
    const FILTER_SOLARIZE = 'solarize';

    /**
     * 文字对齐方式常量
     */
    const TEXT_ALIGN_LEFT = 'left';
    const TEXT_ALIGN_CENTER = 'center';
    const TEXT_ALIGN_RIGHT = 'right';

    /**
     * 文字垂直对齐方式常量
     */
    const TEXT_VALIGN_TOP = 'top';
    const TEXT_VALIGN_MIDDLE = 'middle';
    const TEXT_VALIGN_BOTTOM = 'bottom';

    /**
     * 文字装饰常量
     */
    const TEXT_DECORATION_NONE = 'none';
    const TEXT_DECORATION_UNDERLINE = 'underline';
    const TEXT_DECORATION_OVERLINE = 'overline';
    const TEXT_DECORATION_LINETHROUGH = 'line-through';

    /**
     * 文字样式常量
     */
    const TEXT_STYLE_NORMAL = 'normal';
    const TEXT_STYLE_ITALIC = 'italic';
    const TEXT_STYLE_OBLIQUE = 'oblique';

    /**
     * 位置映射表 - 中文描述
     * @var array
     */
    private static array $positionMap = [
        self::POSITION_TOP_LEFT => '左上角',
        self::POSITION_TOP_CENTER => '顶部居中',
        self::POSITION_TOP_RIGHT => '右上角',
        self::POSITION_MIDDLE_LEFT => '左侧居中',
        self::POSITION_CENTER => '居中',
        self::POSITION_MIDDLE_RIGHT => '右侧居中',
        self::POSITION_BOTTOM_LEFT => '左下角',
        self::POSITION_BOTTOM_CENTER => '底部居中',
        self::POSITION_BOTTOM_RIGHT => '右下角',
    ];

    /**
     * 布局映射表 - 中文描述
     * @var array
     */
    private static array $layoutMap = [
        self::LAYOUT_HORIZONTAL => '水平布局',
        self::LAYOUT_VERTICAL => '垂直布局',
        self::LAYOUT_GRID_2X2 => '2x2网格布局',
        self::LAYOUT_GRID_3X3 => '3x3网格布局',
        self::LAYOUT_GRID_4X4 => '4x4网格布局',
        self::LAYOUT_DIAGONAL => '对角线布局',
        self::LAYOUT_SPIRAL => '螺旋布局',
        self::LAYOUT_CIRCLE => '圆形布局',
        self::LAYOUT_MOSAIC => '马赛克布局',
        self::LAYOUT_COLLAGE => '拼贴布局',
    ];

    /**
     * 页面尺寸映射表 - PDF页面尺寸
     * @var array
     */
    private static array $pageSizes = [
        'A4' => [595, 842],
        'A3' => [842, 1191],
        'A2' => [1191, 1684],
        'Letter' => [612, 792],
        'Legal' => [612, 1008],
        'Tabloid' => [792, 1224],
    ];

    /**
     * 文字对齐方式映射表
     * @var array
     */
    private static array $textAlignMap = [
        self::TEXT_ALIGN_LEFT => Imagick::ALIGN_LEFT,
        self::TEXT_ALIGN_CENTER => Imagick::ALIGN_CENTER,
        self::TEXT_ALIGN_RIGHT => Imagick::ALIGN_RIGHT,
    ];

    /**
     * 文字样式映射表
     * @var array
     */
    private static array $textStyleMap = [
        self::TEXT_STYLE_NORMAL => Imagick::STYLE_NORMAL,
        self::TEXT_STYLE_ITALIC => Imagick::STYLE_ITALIC,
        self::TEXT_STYLE_OBLIQUE => Imagick::STYLE_OBLIQUE,
    ];

    /**
     * 文字装饰映射表
     * @var array
     */
    private static array $textDecorationMap = [
        self::TEXT_DECORATION_NONE => Imagick::DECORATION_NO,
        self::TEXT_DECORATION_UNDERLINE => Imagick::DECORATION_UNDERLINE,
        self::TEXT_DECORATION_OVERLINE => Imagick::DECORATION_OVERLINE,
        self::TEXT_DECORATION_LINETHROUGH => Imagick::DECORATION_LINETROUGH,
    ];

    /**
     * 构造函数
     *
     * 初始化 Imagick 实例，检查扩展是否加载
     *
     * @throws Exception 当未加载 imagick 扩展时抛出异常
     */
    public function __construct()
    {
        // 检查 imagick 扩展是否已加载
        if (!extension_loaded('imagick')) {
            throw new Exception('Imagick 扩展未加载，请安装并启用 imagick PHP 扩展');
        }

        // 初始化 Imagick 实例
        $this->imagick = new Imagick();
        // 设置默认资源类型为真彩色
        $this->imagick->setType(Imagick::IMGTYPE_TRUECOLOR);
    }

    /**
     * 静态初始化实例
     */
    public static function instance(): ?self
    {
        if (! isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    // ==========================================
    // 基础图像操作方法
    // ==========================================

    /**
     * 打开图像文件
     *
     * 从文件路径加载图像，支持常见图像格式（JPEG、PNG、GIF、WEBP、BMP等）
     *
     * @param string $filePath 图像文件路径，支持绝对路径和相对路径
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当文件不存在、无法读取或格式不支持时抛出异常
     */
    public function openImage(string $filePath): self
    {
        // 验证文件是否存在且可读
        $this->validateFile($filePath);

        try {
            // 清除之前的图像资源
            if ($this->resourceLoaded) {
                $this->imagick->clear();
            }

            // 读取图像文件到 Imagick 实例
            $this->imagick->readImage($filePath);
            // 标记资源已成功加载
            $this->resourceLoaded = true;
        } catch (ImagickException $e) {
            // 抛出包含详细路径信息的异常
            throw new ImagickException("无法打开图像文件: {$filePath} - {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 从二进制数据加载图像
     *
     * 直接从二进制字符串数据加载图像，适用于网络传输或数据库存储的图像数据
     *
     * @param string $imageData 图像二进制数据
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当数据格式错误或加载失败时抛出异常
     */
    public function loadFromBlob(string $imageData): self
    {
        // 验证二进制数据有效性
        if (empty($imageData)) {
            throw new ImagickException("图像二进制数据不能为空");
        }

        try {
            // 清除之前的图像资源
            if ($this->resourceLoaded) {
                $this->imagick->clear();
            }

            // 从二进制数据读取图像
            $this->imagick->readImageBlob($imageData);
            // 标记资源已成功加载
            $this->resourceLoaded = true;

        } catch (ImagickException $e) {
            throw new ImagickException("从二进制数据加载图像失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 从Base64字符串加载图像
     *
     * 支持带前缀和不带前缀的Base64编码图像数据
     *
     * @param string $base64Data Base64编码的图像数据，可包含 data:image/format;base64, 前缀
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当Base64数据格式错误或解码失败时抛出异常
     */
    public function loadFromBase64(string $base64Data): self
    {
        // 验证Base64数据有效性
        if (empty($base64Data)) {
            throw new ImagickException("Base64数据不能为空");
        }

        // 移除Base64数据URI前缀（如果存在）
        if (str_contains($base64Data, 'base64,')) {
            $base64Data = substr($base64Data, strpos($base64Data, 'base64,') + 7);
        }

        // 解码Base64数据
        $imageData = base64_decode($base64Data, true);
        if ($imageData === false) {
            throw new ImagickException("Base64数据解码失败，请检查数据格式是否正确");
        }

        // 调用二进制加载方法处理解码后的数据
        return $this->loadFromBlob($imageData);
    }

    /**
     * 创建空白画布
     *
     * 创建指定尺寸和背景颜色的新画布，适用于生成新图像或作为合成底图
     *
     * @param int $width 画布宽度（像素），必须大于0，建议范围：1-10000
     * @param int $height 画布高度（像素），必须大于0，建议范围：1-10000
     * @param string $backgroundColor 背景颜色，支持格式：
     *                                - 十六进制颜色码：#RRGGBB 或 #RGB
     *                                - 颜色名称：red, blue, green, white, black等
     *                                - RGB值：rgb(255,255,255)
     *                                默认值：#FFFFFF（白色）
     * @param string $format 图像格式，默认PNG，支持：JPEG, PNG, GIF, WEBP, BMP等
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当尺寸参数无效或创建失败时抛出异常
     */
    public function createCanvas(int $width, int $height, string $backgroundColor = '#FFFFFF', string $format = 'PNG'): self
    {
        // 验证尺寸参数有效性
        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("画布尺寸必须大于0: {$width}x{$height}");
        }

        // 验证尺寸是否过大（防止内存溢出）
        if ($width > 10000 || $height > 10000) {
            throw new ImagickException("画布尺寸过大，建议最大尺寸为10000x10000");
        }

        try {
            // 清除之前的图像资源
            if ($this->resourceLoaded) {
                $this->imagick->clear();
            }

            // 创建 ImagickPixel 对象（修复了原类中的构造异常）
            $pixel = $this->createImagickPixel($backgroundColor);

            // 创建指定尺寸和背景颜色的新图像
            $this->imagick->newImage($width, $height, $pixel);
            // 设置图像格式
            $this->imagick->setImageFormat($format);
            // 标记资源已成功加载
            $this->resourceLoaded = true;

        } catch (ImagickException $e) {
            throw new ImagickException("创建画布失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 保存处理后的图像
     *
     * 将当前图像保存到文件或返回Base64编码数据，支持质量压缩和格式转换
     *
     * @param string|null $outputPath 输出文件路径，为null时返回Base64数据
     *                                支持格式：/path/to/image.jpg
     * @param string $format 输出格式，可选值：
     *                       - jpeg, jpg: JPEG格式（有损压缩）
     *                       - png: PNG格式（无损，支持透明）
     *                       - gif: GIF格式（动图）
     *                       - webp: WebP格式（现代格式）
     *                       - bmp: BMP格式（无压缩）
     *                       - ico: ICO格式（图标）
     *                       - pdf: PDF格式
     *                       默认值：png
     * @param int $quality 图像质量，范围1-100，默认85（高质量）
     *                     仅对JPEG、WEBP等有损格式有效
     * @return bool|string 保存成功返回true，输出Base64时返回数据URI字符串
     * @throws ImagickException 当保存失败或路径不可写时抛出异常
     */
    public function saveImage(?string $outputPath = null, string $format = 'png', int $quality = 85): bool|string
    {
        // 验证资源是否已加载
        $this->validateResource();

        // 验证质量参数
        if ($quality < 1 || $quality > 100) {
            throw new ImagickException("图像质量参数必须在1-100之间: {$quality}");
        }

        try {
            // 统一转换为小写格式名称
            $format = strtolower($format);
            // 设置输出图像格式
            $this->imagick->setImageFormat($format);

            // 设置图像压缩质量（仅对JPEG、WEBP等有损格式有效）
            $this->imagick->setImageCompressionQuality($quality);

            // 图像优化处理
            $this->optimizeImageForOutput();

            if ($outputPath !== null) {
                // 文件输出模式：保存到指定路径
                $this->prepareOutputDirectory($outputPath);
                // 写入图像文件，返回操作结果
                $result = $this->imagick->writeImage($outputPath);
                return $result;
            } else {
                // Base64输出模式：返回数据URI
                $result = $this->toBase64($format, $quality);
                return $result;
            }
        } catch (ImagickException $e) {
            throw new ImagickException("保存图像失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 输出为Base64字符串
     *
     * 将当前图像转换为Base64编码的数据URI，适用于网页内联显示或API返回
     *
     * @param string $format 输出格式，可选值：jpeg, png, gif, webp, bmp
     *                       默认值：png
     * @param int $quality 图像质量，范围1-100，默认85
     * @return string Base64编码的数据URI字符串
     * @throws ImagickException 当转换失败时抛出异常
     */
    public function toBase64(string $format = 'png', int $quality = 85): string
    {
        $this->validateResource();

        // 验证质量参数
        if ($quality < 1 || $quality > 100) {
            throw new ImagickException("图像质量参数必须在1-100之间: {$quality}");
        }

        try {
            // 克隆当前图像实例，避免修改原始图像
            $clone = clone $this->imagick;
            // 设置输出格式
            $clone->setImageFormat($format);
            // 设置压缩质量
            $clone->setImageCompressionQuality($quality);
            // 优化克隆图像
            $this->optimizeImageInstance($clone);

            // 获取图像二进制数据和MIME类型
            $blob = $clone->getImageBlob();
            $mime = $clone->getImageMimeType();
            // 清理克隆实例释放内存
            $clone->clear();

            // 构建并返回数据URI
            return 'data:' . $mime . ';base64,' . base64_encode($blob);
        } catch (ImagickException $e) {
            throw new ImagickException("生成Base64数据失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 直接在浏览器输出图像
     *
     * 设置合适的HTTP头并直接输出图像二进制流，支持下载和直接显示
     *
     * @param string $format 输出格式，可选值：jpeg, png, gif, webp, bmp
     *                       默认值：png
     * @param int $quality 图像质量，范围1-100，默认85
     * @param string $filename 下载时的文件名，为空时直接在浏览器显示
     *                         示例：image.jpg, download.png
     * @param bool $attachment 是否作为附件下载，true下载/false直接显示
     *                         默认false（直接显示）
     * @return void
     * @throws ImagickException 当输出失败时抛出异常
     */
    public function outputToBrowser(string $format = 'png', int $quality = 85, string $filename = '', bool $attachment = false): void
    {
        $this->validateResource();

        // 验证质量参数
        if ($quality < 1 || $quality > 100) {
            throw new ImagickException("图像质量参数必须在1-100之间: {$quality}");
        }

        // 检查是否已经发送过HTTP头
        if (headers_sent()) {
            throw new ImagickException("HTTP头已经发送，无法输出图像");
        }

        try {
            // 设置输出格式和质量
            $this->imagick->setImageFormat($format);
            $this->imagick->setImageCompressionQuality($quality);
            // 优化图像输出
            $this->optimizeImageForOutput();

            // 获取图像MIME类型
            $mime = $this->imagick->getImageMimeType();

            // 设置HTTP响应头
            header('Content-Type: ' . $mime);

            if (!empty($filename)) {
                $disposition = $attachment ? 'attachment' : 'inline';
                header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
            }

            // 禁用缓存确保实时输出
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // 直接输出图像二进制数据
            echo $this->imagick->getImageBlob();

            // 终止脚本执行，确保只输出图像数据
            exit(0);
        } catch (ImagickException $e) {
            throw new ImagickException("浏览器输出失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    // ==========================================
    // 图像尺寸调整和裁剪方法
    // ==========================================

    /**
     * 调整图像尺寸
     *
     * 按指定尺寸调整图像大小，支持保持比例和强制拉伸
     *
     * @param int $width 目标宽度（像素），必须大于0
     * @param int $height 目标高度（像素），必须大于0
     * @param bool $maintainAspectRatio 是否保持宽高比例：
     *                                  - true: 保持比例（推荐）
     *                                  - false: 强制拉伸（可能变形）
     *                                  默认值：true
     * @param int $filter 重采样滤波器类型，可选值：
     *                    - Imagick::FILTER_LANCZOS: Lanczos滤波器（高质量，默认）
     *                    - Imagick::FILTER_CATROM: Catmull-Rom滤波器
     *                    - Imagick::FILTER_CUBIC: 立方滤波器
     *                    - Imagick::FILTER_TRIANGLE: 三角滤波器
     *                    - Imagick::FILTER_POINT: 点滤波器（快速）
     *                    默认值：Imagick::FILTER_LANCZOS
     * @param float $blur 模糊因子，范围0.1-10.0，默认1.0（推荐值）
     * @param bool $bestfit 是否最佳适应：
     *                      - true: 保持比例且不超出指定尺寸
     *                      - false: 精确调整为指定尺寸
     *                      默认值：true
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当调整失败或参数无效时抛出异常
     */
    public function resizeImage(
        int $width,
        int $height,
        bool $maintainAspectRatio = true,
        int $filter = Imagick::FILTER_LANCZOS,
        float $blur = 1.0,
        bool $bestfit = true
    ): self {
        $this->validateResource();

        // 验证目标尺寸有效性
        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("目标尺寸必须大于0: {$width}x{$height}");
        }

        // 验证模糊因子
        if ($blur < 0.1 || $blur > 10.0) {
            throw new ImagickException("模糊因子必须在0.1-10.0之间: {$blur}");
        }

        try {
            if ($maintainAspectRatio) {
                // 等比例缩放模式：保持原始宽高比
                $this->imagick->resizeImage($width, $height, $filter, $blur, $bestfit);
            } else {
                // 强制调整模式：精确调整为指定尺寸，可能造成变形
                $this->imagick->resizeImage($width, $height, $filter, $blur);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("调整图像尺寸失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 生成缩略图
     *
     * 快速生成指定尺寸的缩略图，支持填充和裁剪模式
     *
     * @param int $width 缩略图宽度（像素），必须大于0
     * @param int $height 缩略图高度（像素），必须大于0
     * @param bool $bestfit 是否最佳适应：
     *                      - true: 保持比例
     *                      - false: 强制尺寸
     *                      默认值：true
     * @param bool $fill 是否填充到指定尺寸：
     *                   - true: 填充空白区域
     *                   - false: 保持原始比例
     *                   默认值：false
     * @param string $fillColor 填充颜色，当fill=true时生效
     *                          支持格式：十六进制、颜色名称、RGB值
     *                          默认值：#FFFFFF（白色）
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当生成失败时抛出异常
     */
    public function thumbnail(
        int $width,
        int $height,
        bool $bestfit = true,
        bool $fill = false,
        string $fillColor = '#FFFFFF'
    ): self {
        $this->validateResource();

        // 验证尺寸参数
        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("缩略图尺寸必须大于0: {$width}x{$height}");
        }

        try {
            if ($fill) {
                // 填充模式：生成指定尺寸的缩略图，空白区域填充指定颜色
                $this->imagick->thumbnailImage($width, $height, $bestfit);

                // 创建指定尺寸的填充画布
                $thumb = new Imagick();
                $pixel = $this->createImagickPixel($fillColor);
                $thumb->newImage($width, $height, $pixel);
                $thumb->setImageFormat($this->imagick->getImageFormat());

                // 计算缩略图在画布中的居中位置
                $thumbWidth = $this->imagick->getImageWidth();
                $thumbHeight = $this->imagick->getImageHeight();
                $x = (int)(($width - $thumbWidth) / 2);
                $y = (int)(($height - $thumbHeight) / 2);

                // 将缩略图合成到填充画布上
                $thumb->compositeImage($this->imagick, Imagick::COMPOSITE_OVER, $x, $y);
                // 替换当前图像实例
                $this->imagick = $thumb;
                // 添加到临时资源管理
                $this->tempResources[] = $thumb;
            } else {
                // 普通模式：直接生成缩略图，可能不满足指定尺寸
                $this->imagick->thumbnailImage($width, $height, $bestfit);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("生成缩略图失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 裁剪图像
     *
     * 从指定位置裁剪指定尺寸的图像区域
     *
     * @param int $x 起始X坐标（左上角为原点），必须大于等于0
     * @param int $y 起始Y坐标（左上角为原点），必须大于等于0
     * @param int $width 裁剪宽度（像素），必须大于0
     * @param int $height 裁剪高度（像素），必须大于0
     * @param bool $resetPage 是否重置页面几何：
     *                        - true: 重置页面几何（推荐）
     *                        - false: 保留页面几何信息
     *                        默认值：true
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当裁剪区域超出图像范围时抛出异常
     */
    public function cropImage(int $x, int $y, int $width, int $height, bool $resetPage = true): self
    {
        $this->validateResource();

        // 验证坐标参数
        if ($x < 0 || $y < 0) {
            throw new ImagickException("裁剪坐标不能为负数: x={$x}, y={$y}");
        }

        // 验证尺寸参数
        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("裁剪尺寸必须大于0: {$width}x{$height}");
        }

        // 验证裁剪区域有效性
        $imageWidth = $this->imagick->getImageWidth();
        $imageHeight = $this->imagick->getImageHeight();

        if ($x >= $imageWidth || $y >= $imageHeight) {
            throw new ImagickException("裁剪起始坐标超出图像范围: 图像{$imageWidth}x{$imageHeight}, 坐标{$x},{$y}");
        }

        if ($x + $width > $imageWidth || $y + $height > $imageHeight) {
            throw new ImagickException("裁剪区域超出图像范围: 图像{$imageWidth}x{$imageHeight}, 裁剪区域{$x},{$y} {$width}x{$height}");
        }

        try {
            // 执行裁剪操作
            $this->imagick->cropImage($width, $height, $x, $y);

            if ($resetPage) {
                // 重置页面几何信息，移除虚拟画布数据
                $this->imagick->setImagePage(0, 0, 0, 0);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("裁剪图像失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 智能裁剪 - 基于重要内容区域的自动裁剪
     *
     * 自动识别图像中的重要内容区域并进行智能裁剪
     *
     * @param int $width 目标宽度，必须大于0
     * @param int $height 目标高度，必须大于0
     * @param bool $gravity 是否使用重力感应：
     *                      - true: 使用重力感应（推荐）
     *                      - false: 不使用重力感应
     *                      默认值：true
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当智能裁剪失败时抛出异常
     */
    public function smartCrop(int $width, int $height, bool $gravity = true): self
    {
        $this->validateResource();

        // 验证尺寸参数
        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("目标尺寸必须大于0: {$width}x{$height}");
        }

        try {
            // 使用Imagick的智能裁剪功能
            $this->imagick->cropThumbnailImage($width, $height);

            if ($gravity) {
                // 应用重力感应，保持重要内容在视觉中心
                $this->imagick->setGravity(Imagick::GRAVITY_CENTER);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("智能裁剪失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    // ==========================================
    // 图像旋转和翻转方法
    // ==========================================

    /**
     * 旋转图像
     *
     * 按指定角度旋转图像，空白区域填充指定颜色
     *
     * @param float $angle 旋转角度（度数）：
     *                     - 正数: 顺时针旋转
     *                     - 负数: 逆时针旋转
     *                     示例：90, -45, 180
     * @param string $backgroundColor 背景颜色，旋转后空白区域填充颜色
     *                                支持格式：十六进制、颜色名称、RGB值
     *                                默认值：#FFFFFF（白色）
     * @param int $interpolate 像素插值方法，可选值：
     *                         - Imagick::INTERPOLATE_INTEGER: 整数插值（默认）
     *                         - Imagick::INTERPOLATE_BILINEAR: 双线性插值
     *                         - Imagick::INTERPOLATE_BICUBIC: 双立方插值
     *                         - Imagick::INTERPOLATE_SPLINE: 样条插值
     *                         默认值：Imagick::INTERPOLATE_INTEGER
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当旋转失败时抛出异常
     */
    public function rotateImage(float $angle, string $backgroundColor = '#FFFFFF', int $interpolate = Imagick::INTERPOLATE_INTEGER): self
    {
        $this->validateResource();

        try {
            // 创建背景颜色像素（使用修复后的方法）
            $pixel = $this->createImagickPixel($backgroundColor);
            // 设置像素插值方法
            $this->imagick->setInterpolateMethod($interpolate);
            // 执行旋转操作
            $this->imagick->rotateImage($pixel, $angle);

        } catch (ImagickException $e) {
            throw new ImagickException("旋转图像失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 水平翻转图像（镜像）
     *
     * 沿垂直轴水平翻转图像，创建镜像效果
     *
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当翻转失败时抛出异常
     */
    public function flipImage(): self
    {
        $this->validateResource();

        try {
            // 执行水平翻转
            $this->imagick->flopImage();
        } catch (ImagickException $e) {
            throw new ImagickException("水平翻转失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 垂直翻转图像
     *
     * 沿水平轴垂直翻转图像
     *
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当翻转失败时抛出异常
     */
    public function flopImage(): self
    {
        $this->validateResource();

        try {
            // 执行垂直翻转
            $this->imagick->flipImage();
        } catch (ImagickException $e) {
            throw new ImagickException("垂直翻转失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;

    }

    // ==========================================
    // 图像压缩和质量优化方法
    // ==========================================

    /**
     * 压缩图像质量和尺寸
     *
     * 综合压缩方法，支持质量压缩和尺寸调整
     *
     * @param int $quality 图像质量，范围1-100，推荐70-90
     *                     默认值：85
     * @param int|null $newWidth 压缩后的宽度，null时保持原宽度
     *                           必须大于0
     * @param int|null $newHeight 压缩后的高度，null时保持原高度
     *                            必须大于0
     * @param string|null $outputPath 输出路径，null时返回Base64数据
     *                                示例：/path/to/compressed.jpg
     * @param int $filter 调整尺寸时使用的滤波器类型，可选值：
     *                    - Imagick::FILTER_LANCZOS: Lanczos滤波器（默认）
     *                    - Imagick::FILTER_CATROM: Catmull-Rom滤波器
     *                    - Imagick::FILTER_CUBIC: 立方滤波器
     *                    默认值：Imagick::FILTER_LANCZOS
     * @return bool|string 成功返回true或Base64字符串，失败抛出异常
     * @throws ImagickException 当压缩失败时抛出异常
     */
    public function compressImage(
        int $quality = 85,
        ?int $newWidth = null,
        ?int $newHeight = null,
        ?string $outputPath = null,
        int $filter = Imagick::FILTER_LANCZOS
    ): bool|string {
        $this->validateResource();

        // 验证质量参数
        if ($quality < 1 || $quality > 100) {
            throw new ImagickException("图像质量参数必须在1-100之间: {$quality}");
        }

        // 验证尺寸参数
        if (($newWidth !== null && $newWidth <= 0) || ($newHeight !== null && $newHeight <= 0)) {
            throw new ImagickException("压缩尺寸必须大于0: {$newWidth}x{$newHeight}");
        }

        try {
            // 设置图像压缩质量
            $this->imagick->setImageCompressionQuality($quality);

            // 处理透明通道（如果存在）
            if ($this->hasAlphaChannel()) {
                $this->imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
                $this->imagick->setImageBackgroundColor(new ImagickPixel('transparent'));
            }

            // 调整图像尺寸（如果指定了新尺寸）
            if ($newWidth !== null && $newHeight !== null) {
                $this->resizeImage($newWidth, $newHeight, true, $filter);
            }

            // 图像优化处理
            $this->optimizeImageForOutput();

            // 根据输出路径选择输出方式
            if (!empty($outputPath)) {
                $this->prepareOutputDirectory($outputPath);
                $result = $this->imagick->writeImage($outputPath);
            } else {
                $result = $this->toBase64($this->imagick->getImageFormat(), $quality);
            }

            return $result;

        } catch (ImagickException $e) {
            throw new ImagickException("压缩图像失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 批量压缩图像
     *
     * 批量处理多个图像的压缩任务
     *
     * @param array $imagePaths 图像路径数组
     *                          示例：['/path/to/image1.jpg', '/path/to/image2.png']
     * @param string $outputDir 输出目录
     *                          示例：/path/to/output
     * @param int $quality 压缩质量，范围1-100，默认80
     * @param int|null $maxWidth 最大宽度，null时不限制
     * @param int|null $maxHeight 最大高度，null时不限制
     * @return array 处理结果数组，包含每个文件的处理状态
     * @throws ImagickException 当批量处理失败时抛出异常
     */
    public function batchCompress(array $imagePaths, string $outputDir, int $quality = 80, ?int $maxWidth = null, ?int $maxHeight = null): array
    {
        // 验证输入参数
        if (empty($imagePaths)) {
            throw new ImagickException("图像路径数组不能为空");
        }

        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new ImagickException("无法创建输出目录: {$outputDir}");
        }

        $results = [];

        foreach ($imagePaths as $inputPath) {
            try {
                // 验证输入文件
                $this->validateFile($inputPath);

                // 打开图像
                $this->openImage($inputPath);

                // 调整尺寸（如果指定了最大尺寸）
                if ($maxWidth !== null && $maxHeight !== null) {
                    $this->resizeImage($maxWidth, $maxHeight, true);
                }

                // 生成输出文件名
                $filename = pathinfo($inputPath, PATHINFO_FILENAME);
                $extension = pathinfo($inputPath, PATHINFO_EXTENSION);
                $outputPath = $outputDir . '/' . $filename . '_compressed.' . $extension;

                // 压缩并保存
                $result = $this->compressImage($quality, null, null, $outputPath);

                $results[] = [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'success' => $result,
                    'message' => '压缩成功',
                    'file_size' => filesize($outputPath)
                ];

            } catch (Exception $e) {
                $results[] = [
                    'input' => $inputPath,
                    'output' => null,
                    'success' => false,
                    'message' => $e->getMessage(),
                    'file_size' => 0
                ];
            } finally {
                // 确保资源被清理
                $this->cleanup();
            }
        }

        return $results;
    }

    // ==========================================
    // 水印处理方法
    // ==========================================

    /**
     * 添加文字水印
     *
     * 在图像上添加文字水印，支持多种样式和位置
     *
     * @param string $text 水印文字内容
     *                     示例：'版权所有', 'Confidential'
     * @param string|null $fontName 字体名称，null时使用默认字体
     *                              可选值：pmzdxx, pmzdbt, lishu, yishanbei, xingkai
     *                              默认值：pmzdxx
     * @param int $fontSize 字体大小（像素），范围1-200，默认12
     * @param string $color 文字颜色，支持格式：十六进制、颜色名称、RGB值
     *                      默认值：#FFFFFF（白色）
     * @param int|null $position 水印位置，使用类的常量，可选值：
     *                           POSITION_TOP_LEFT, POSITION_TOP_CENTER, POSITION_TOP_RIGHT,
     *                           POSITION_MIDDLE_LEFT, POSITION_CENTER, POSITION_MIDDLE_RIGHT,
     *                           POSITION_BOTTOM_LEFT, POSITION_BOTTOM_CENTER, POSITION_BOTTOM_RIGHT
     *                           默认值：POSITION_BOTTOM_RIGHT（右下角）
     * @param int $angle 旋转角度，范围-180到180，默认0度
     * @param int $padding 水印边距（像素），范围0-100，默认10
     * @param bool $textAntialias 文字抗锯齿模式：
     *                            - true: 开启抗锯齿（推荐）
     *                            - false: 关闭抗锯齿
     *                            默认值：true
     * @param string|null $strokeColor 描边颜色，null时无描边
     *                                 支持格式：十六进制、颜色名称、RGB值
     * @param int $strokeWidth 描边宽度（像素），范围0-10，默认1
     * @param string|null $backgroundColor 背景颜色，null时无背景
     *                                     支持格式：十六进制、颜色名称、RGB值
     * @param int $backgroundOpacity 背景透明度，0-100，默认70
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当添加水印失败时抛出异常
     */
    public function addTextWatermark(
        string $text,
        ?string $fontName = null,
        int $fontSize = 12,
        string $color = '#FFFFFF',
        ?int $position = self::POSITION_BOTTOM_RIGHT,
        int $angle = 0,
        int $padding = 10,
        bool $textAntialias = true,
        ?string $strokeColor = null,
        int $strokeWidth = 1,
        ?string $backgroundColor = null,
        int $backgroundOpacity = 70
    ): self {
        $this->validateResource();

        // 验证文字内容
        if (empty(trim($text))) {
            throw new ImagickException("水印文字内容不能为空");
        }

        // 验证字体大小
        if ($fontSize < 1 || $fontSize > 200) {
            throw new ImagickException("字体大小必须在1-200之间: {$fontSize}");
        }

        // 验证边距
        if ($padding < 0 || $padding > 100) {
            throw new ImagickException("水印边距必须在0-100之间: {$padding}");
        }

        // 验证旋转角度
        if ($angle < -180 || $angle > 180) {
            throw new ImagickException("旋转角度必须在-180到180之间: {$angle}");
        }

        // 验证描边宽度
        if ($strokeWidth < 0 || $strokeWidth > 10) {
            throw new ImagickException("描边宽度必须在0-10之间: {$strokeWidth}");
        }

        // 验证背景透明度
        if ($backgroundOpacity < 0 || $backgroundOpacity > 100) {
            throw new ImagickException("背景透明度必须在0-100之间: {$backgroundOpacity}");
        }

        try {
            // 创建文字绘制对象
            $draw = new ImagickDraw();

            // 设置字体
            $fontPath = $this->getFontPath($fontName);
            $draw->setFont($fontPath);

            // 设置字体属性
            $draw->setFontSize($fontSize);
            $draw->setFillColor($this->createImagickPixel($color));
            $draw->setTextAntialias($textAntialias);

            // 设置描边效果
            if ($strokeColor !== null) {
                $draw->setStrokeColor($this->createImagickPixel($strokeColor));
                $draw->setStrokeWidth($strokeWidth);
            }

            // 添加文字背景
            if ($backgroundColor !== null) {
                $this->addTextBackgroundWithDraw($text, $draw, $position, $padding, $backgroundColor, $backgroundOpacity);
            }

            // 设置水印位置
            $this->setWatermarkPosition($position, $draw, $padding);

            // 在图像上绘制文字水印
            $this->imagick->annotateImage($draw, 0, 0, $angle, $text);

            // 清理绘制对象
            $draw->destroy();

        } catch (ImagickException $e) {
            throw new ImagickException("添加文字水印失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 添加图片水印
     *
     * 在图像上添加图片水印，支持透明度、位置和混合模式
     *
     * @param string $watermarkPath 水印图片路径
     *                              示例：/path/to/watermark.png
     * @param int $x 水印在图像上的X坐标，默认0
     * @param int $y 水印在图像上的Y坐标，默认0
     * @param int $opacity 水印透明度，范围0-100，默认50
     * @param int $composite 水印合成操作类型，可选值：
     *                       - Imagick::COMPOSITE_OVER: 叠加（默认）
     *                       - Imagick::COMPOSITE_ATOP: 顶部
     *                       - Imagick::COMPOSITE_IN: 内部
     *                       - Imagick::COMPOSITE_OUT: 外部
     *                       默认值：Imagick::COMPOSITE_OVER
     * @param int|null $position 水印位置，如果提供则忽略x,y坐标
     *                           使用位置常量，默认null
     * @param int $padding 水印边距，当使用位置时有效，范围0-100，默认10
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当添加水印失败时抛出异常
     */
    public function addImageWatermark(
        string $watermarkPath,
        int $x = 0,
        int $y = 0,
        int $opacity = 50,
        int $composite = Imagick::COMPOSITE_OVER,
        ?int $position = null,
        int $padding = 10
    ): self {
        $this->validateResource();
        $this->validateFile($watermarkPath);

        // 验证透明度
        if ($opacity < 0 || $opacity > 100) {
            throw new ImagickException("水印透明度必须在0-100之间: {$opacity}");
        }

        // 验证边距
        if ($padding < 0 || $padding > 100) {
            throw new ImagickException("水印边距必须在0-100之间: {$padding}");
        }

        try {
            // 加载水印图片
            $watermark = new Imagick($watermarkPath);
            // 设置水印透明度
            $opacity = max(0, min(100, $opacity));
            $watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);

            // 如果指定了位置，则自动计算坐标
            if ($position !== null) {
                list($x, $y) = $this->calculateWatermarkPosition($position, $padding, $watermark);
            }

            // 合成水印到主图像
            $this->imagick->compositeImage($watermark, $composite, $x, $y);
            // 清理水印资源
            $watermark->clear();

        } catch (ImagickException $e) {
            throw new ImagickException("添加图片水印失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 批量添加水印
     *
     * 为多张图片批量添加相同的水印
     *
     * @param array $imagePaths 图片路径数组
     *                          示例：['/path/to/image1.jpg', '/path/to/image2.png']
     * @param string $watermarkPath 水印图片路径
     * @param int $position 水印位置，使用位置常量
     *                      默认值：POSITION_BOTTOM_RIGHT
     * @param int $opacity 透明度，范围0-100，默认50
     * @param string $outputDir 输出目录
     *                          示例：/path/to/output
     * @return array 处理结果数组，包含每个文件的处理状态
     * @throws ImagickException 当批量处理失败时抛出异常
     */
    public function batchWatermark(array $imagePaths, string $watermarkPath, int $position = self::POSITION_BOTTOM_RIGHT, int $opacity = 50, string $outputDir = ''): array
    {
        // 验证输入参数
        if (empty($imagePaths)) {
            throw new ImagickException("图像路径数组不能为空");
        }

        $this->validateFile($watermarkPath);

        if (!empty($outputDir) && !is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new ImagickException("无法创建输出目录: {$outputDir}");
        }

        $results = [];

        foreach ($imagePaths as $inputPath) {
            try {
                // 验证输入文件
                $this->validateFile($inputPath);

                // 打开图像
                $this->openImage($inputPath);
                // 添加水印
                $this->addImageWatermark($watermarkPath, 0, 0, $opacity, Imagick::COMPOSITE_OVER, $position);

                // 处理输出
                if (!empty($outputDir)) {
                    $filename = pathinfo($inputPath, PATHINFO_FILENAME);
                    $extension = pathinfo($inputPath, PATHINFO_EXTENSION);
                    $outputPath = $outputDir . '/' . $filename . '_watermarked.' . $extension;
                    $result = $this->saveImage($outputPath);
                } else {
                    $result = $this->toBase64();
                    $outputPath = 'base64';
                }

                $results[] = [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'success' => (bool)$result,
                    'message' => '水印添加成功'
                ];

            } catch (Exception $e) {
                $results[] = [
                    'input' => $inputPath,
                    'output' => null,
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            } finally {
                // 确保资源被清理
                $this->cleanup();
            }
        }

        return $results;
    }

    // ==========================================
    // 多图合并和拼图功能
    // ==========================================

    /**
     * 多图合并为一张图片
     *
     * 将多张图片按指定方向合并为一张图片，支持渐变透明度效果
     *
     * @param array $imagePaths 图片路径数组
     *                          示例：['/path/to/image1.jpg', '/path/to/image2.png']
     * @param string $direction 合并方向，可选值：
     *                          - 'horizontal': 水平合并
     *                          - 'vertical': 垂直合并
     *                          默认值：'horizontal'
     * @param int $spacing 图片间距（像素），范围0-100，默认0
     * @param string $backgroundColor 背景颜色
     *                                支持格式：十六进制、颜色名称、RGB值
     *                                默认值：#FFFFFF（白色）
     * @param string|null $gradientType 渐变类型，null时无渐变，可选值：
     *                                  GRADIENT_LEFT_TO_RIGHT, GRADIENT_RIGHT_TO_LEFT,
     *                                  GRADIENT_TOP_TO_BOTTOM, GRADIENT_BOTTOM_TO_TOP,
     *                                  GRADIENT_CENTER_TO_EDGE, GRADIENT_EDGE_TO_CENTER,
     *                                  GRADIENT_DIAGONAL_LEFT, GRADIENT_DIAGONAL_RIGHT
     * @param float $gradientIntensity 渐变强度，0-1，默认0.5
     * @param string|null $outputPath 输出路径，null时返回 本类
     * @return string|self
     * @throws ImagickException 当合并失败时抛出异常
     */
    public function mergeImages(
        array $imagePaths,
        string $direction = 'horizontal',
        int $spacing = 0,
        string $backgroundColor = '#FFFFFF',
        ?string $gradientType = null,
        float $gradientIntensity = 0.5,
        ?string $outputPath = null
    ):string|self
    {
        // 验证参数
        if (empty($imagePaths)) {
            throw new ImagickException("图片路径数组不能为空");
        }

        if (!in_array($direction, ['horizontal', 'vertical'])) {
            throw new ImagickException("合并方向必须是 'horizontal' 或 'vertical'");
        }

        // 标记资源已成功加载
        $this->resourceLoaded = true;

        $spacing = max(0, min(100, $spacing));
        $gradientIntensity = max(0, min(1, $gradientIntensity));

        // 第一步：快速计算最大尺寸和总尺寸
        $maxWidth = 0;
        $maxHeight = 0;
        $imageDimensions = [];

        foreach ($imagePaths as $path) {
            if (!file_exists($path)) {
                throw new ImagickException("图片文件不存在: {$path}");
            }

            $img = new Imagick();
            $img->pingImage($path); // 只读取元数据，不加载像素数据
            $width = $img->getImageWidth();
            $height = $img->getImageHeight();

            $imageDimensions[$path] = ['width' => $width, 'height' => $height];
            $maxWidth = max($maxWidth, $width);
            $maxHeight = max($maxHeight, $height);

            $img->destroy();
        }

        // 计算画布尺寸
        if ($direction === 'horizontal') {
            $totalWidth = 0;
            foreach ($imageDimensions as $dim) {
                $newHeight = $maxHeight;
                $newWidth = ($dim['width'] * $newHeight) / $dim['height'];
                $totalWidth += $newWidth;
            }
            $totalWidth += $spacing * (count($imagePaths) - 1);
            $canvasWidth = $totalWidth;
            $canvasHeight = $maxHeight;
        } else {
            $totalHeight = 0;
            foreach ($imageDimensions as $dim) {
                $newWidth = $maxWidth;
                $newHeight = ($dim['height'] * $newWidth) / $dim['width'];
                $totalHeight += $newHeight;
            }
            $totalHeight += $spacing * (count($imagePaths) - 1);
            $canvasWidth = $maxWidth;
            $canvasHeight = $totalHeight;
        }

        // 创建画布
        $this->imagick = new Imagick();
        $this->imagick->newImage($canvasWidth, $canvasHeight, $backgroundColor);
        $this->imagick->setImageFormat('png');

        // 应用渐变效果
        if ($gradientType) {
            $this->applyGradient($this->imagick, $gradientType, $gradientIntensity);
        }

        // 第二步：逐个加载、调整尺寸并合并图片
        $offset = 0;
        foreach ($imagePaths as $path) {
            $img = new Imagick($path);
            $dim = $imageDimensions[$path];

            if ($direction === 'horizontal') {
                $newHeight = $maxHeight;
                $newWidth = ($dim['width'] * $newHeight) / $dim['height'];
                $img->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1, true);
                $this->imagick->compositeImage($img, Imagick::COMPOSITE_OVER, $offset, 0);
                $offset += $newWidth + $spacing;
            } else {
                $newWidth = $maxWidth;
                $newHeight = ($dim['height'] * $newWidth) / $dim['width'];
                $img->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1, true);
                $this->imagick->compositeImage($img, Imagick::COMPOSITE_OVER, 0, $offset);
                $offset += $newHeight + $spacing;
            }

            $img->destroy();
        }

        // 输出结果
        if ($outputPath) {
            $result = $this->imagick->writeImage($outputPath);
            $this->imagick->destroy();
            return $result;
        } else {
            return $this;
        }
    }

    /**
     * 高级拼图功能
     *
     * 支持多种布局方式的图片拼接，如九宫格、网格、圆形等
     *
     * @param array $imagePaths 图片路径数组
     *                          示例：['/path/to/image1.jpg', '/path/to/image2.png']
     * @param string $layout 布局类型，使用LAYOUT常量，可选值：
     *                       LAYOUT_HORIZONTAL, LAYOUT_VERTICAL, LAYOUT_GRID_2X2,
     *                       LAYOUT_GRID_3X3, LAYOUT_GRID_4X4, LAYOUT_DIAGONAL,
     *                       LAYOUT_SPIRAL, LAYOUT_CIRCLE, LAYOUT_MOSAIC, LAYOUT_COLLAGE
     *                       默认值：LAYOUT_GRID_3X3
     * @param int $canvasWidth 画布宽度，必须大于0，默认1000
     * @param int $canvasHeight 画布高度，必须大于0，默认1000
     * @param string $backgroundColor 背景颜色
     *                                支持格式：十六进制、颜色名称、RGB值
     *                                默认值：#FFFFFF（白色）
     * @param array $options 额外选项，可选参数：
     *                       - 'spacing': 间距（像素），默认10
     *                       - 'border_width': 边框宽度，默认0
     *                       - 'border_color': 边框颜色，默认#000000
     *                       - 'round_corners': 圆角半径，默认0
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当拼图失败时抛出异常
     */
    public function createCollage(
        array $imagePaths,
        string $layout = self::LAYOUT_GRID_3X3,
        int $canvasWidth = 1000,
        int $canvasHeight = 1000,
        string $backgroundColor = '#FFFFFF',
        array $options = []
    ): self {
        // 验证输入参数
        if (empty($imagePaths)) {
            throw new ImagickException("图片路径数组不能为空");
        }

        // 验证画布尺寸
        if ($canvasWidth <= 0 || $canvasHeight <= 0) {
            throw new ImagickException("画布尺寸必须大于0: {$canvasWidth}x{$canvasHeight}");
        }

        // 验证布局类型
        $validLayouts = [
            self::LAYOUT_HORIZONTAL, self::LAYOUT_VERTICAL, self::LAYOUT_GRID_2X2,
            self::LAYOUT_GRID_3X3, self::LAYOUT_GRID_4X4, self::LAYOUT_DIAGONAL,
            self::LAYOUT_SPIRAL, self::LAYOUT_CIRCLE, self::LAYOUT_MOSAIC, self::LAYOUT_COLLAGE
        ];
        if (!in_array($layout, $validLayouts)) {
            throw new ImagickException("不支持的布局类型: {$layout}");
        }

        try {
            // 创建画布
            $this->createCanvas($canvasWidth, $canvasHeight, $backgroundColor);

            // 根据布局类型处理图片
            switch ($layout) {
                case self::LAYOUT_GRID_3X3:
                    $this->createGridCollage($imagePaths, 3, 3, $options);
                    break;
                case self::LAYOUT_GRID_2X2:
                    $this->createGridCollage($imagePaths, 2, 2, $options);
                    break;
                case self::LAYOUT_GRID_4X4:
                    $this->createGridCollage($imagePaths, 4, 4, $options);
                    break;
                case self::LAYOUT_CIRCLE:
                    $this->createCircleCollage($imagePaths, $options);
                    break;
                case self::LAYOUT_SPIRAL:
                    $this->createSpiralCollage($imagePaths, $options);
                    break;
                case self::LAYOUT_DIAGONAL:
                    $this->createDiagonalCollage($imagePaths, $options);
                    break;
                case self::LAYOUT_HORIZONTAL:
                    $this->createHorizontalCollage($imagePaths, $options);
                    break;
                case self::LAYOUT_VERTICAL:
                    $this->createVerticalCollage($imagePaths, $options);
                    break;
                default:
                    $this->createGridCollage($imagePaths, 3, 3, $options);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("创建拼图失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    // ==========================================
    // 文字生成图片功能 - 全面优化和增强版本
    // ==========================================

    /**
     * 从文字生成图片 - 全面优化和增强版本
     *
     * 智能文字布局，支持自动换行、字体调整、旋转和居中显示
     * 彻底解决文字旋转角度导致的文字重叠和超出区域问题
     * 支持70-290度特殊角度区间的文字处理
     * 增强文字行高处理逻辑，不限制参数设置区间
     *
     * @param string $text 文字内容，支持<br>换行
     *                     示例："Hello<br>World", "这是一段测试文字"
     * @param int $width 图片宽度，必须大于0，建议范围：100-5000
     * @param int $height 图片高度，必须大于0，建议范围：100-5000
     * @param string $backgroundColor 图片背景颜色
     *                                支持格式：十六进制、颜色名称、RGB值
     *                                默认值：#FFFFFF（白色）
     * @param string $textColor 文字颜色
     *                          支持格式：十六进制、颜色名称、RGB值
     *                          默认值：#000000（黑色）
     * @param string $textBackgroundColor 文字背景颜色
     *                                    支持格式：十六进制、颜色名称、RGB值
     *                                    默认值：transparent（透明）
     * @param float $angle 文字旋转角度，任意角度值，默认0
     *                     支持任意角度值，自动标准化到0-360度
     * @param string $fontName 字体名称，可选值：pmzdxx, pmzdbt, lishu, yishanbei, xingkai
     *                         默认值：pmzdxx
     * @param bool $download 是否下载到浏览器，默认true
     * @param int $targetAreaRatio 文字区域占图片面积比例，10-90，默认80
     * @param float $lineHeightRatio 文字行高比例，任意正数值，默认1.2
     *                               建议范围：0.8-3.0，但支持任意正数值
     * @param string $textAlign 文字水平对齐方式，可选值：left, center, right
     *                          默认值：center
     * @param string $textValign 文字垂直对齐方式，可选值：top, middle, bottom
     *                           默认值：middle
     * @param int $padding 文字内边距，范围0-100，默认20
     * @param string $textStyle 文字样式，可选值：normal, italic, oblique
     *                          默认值：normal
     * @param string $textDecoration 文字装饰，可选值：none, underline, overline, line-through
     *                               默认值：none
     * @param string|null $strokeColor 文字描边颜色，null时无描边
     *                                 支持格式：十六进制、颜色名称、RGB值
     * @param float $strokeWidth 文字描边宽度，任意正数值，默认0
     * @param int $textOpacity 文字透明度，0-100，默认100
     * @param int $backgroundOpacity 文字背景透明度，0-100，默认100
     * @param array $shadow 文字阴影设置，数组格式：
     *                      - 'color': 阴影颜色，默认#000000
     *                      - 'opacity': 阴影透明度，0-100，默认50
     *                      - 'x': X轴偏移，默认2
     *                      - 'y': Y轴偏移，默认2
     *                      - 'blur': 模糊半径，默认3
     *                      默认值：[]
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当生成失败时抛出异常
     */
    public function createTextImage(
        string $text,
        int $width,
        int $height,
        string $backgroundColor = '#FFFFFF',
        string $textColor = '#000000',
        string $textBackgroundColor = 'transparent',
        float $angle = 0,
        string $fontName = 'pmzdxx',
        bool $download = true,
        int $targetAreaRatio = 80,
        float $lineHeightRatio = 1.2,
        string $textAlign = 'center',
        string $textValign = 'middle',
        int $padding = 20,
        string $textStyle = 'normal',
        string $textDecoration = 'none',
        ?string $strokeColor = null,
        float $strokeWidth = 0,
        int $textOpacity = 100,
        int $backgroundOpacity = 100,
        array $shadow = []
    ): self {
        // 验证输入参数
        if (empty(trim($text))) {
            throw new ImagickException("文字内容不能为空");
        }

        if ($width <= 0 || $height <= 0) {
            throw new ImagickException("图片尺寸必须大于0: {$width}x{$height}");
        }

        if ($targetAreaRatio < 10 || $targetAreaRatio > 90) {
            throw new ImagickException("文字区域比例必须在10-90之间: {$targetAreaRatio}");
        }

        if ($lineHeightRatio <= 0) {
            throw new ImagickException("行高比例必须大于0: {$lineHeightRatio}");
        }

        if ($padding < 0 || $padding > 100) {
            throw new ImagickException("内边距必须在0-100之间: {$padding}");
        }

        if ($textOpacity < 0 || $textOpacity > 100) {
            throw new ImagickException("文字透明度必须在0-100之间: {$textOpacity}");
        }

        if ($backgroundOpacity < 0 || $backgroundOpacity > 100) {
            throw new ImagickException("背景透明度必须在0-100之间: {$backgroundOpacity}");
        }

        if ($strokeWidth < 0) {
            throw new ImagickException("描边宽度不能为负数: {$strokeWidth}");
        }

        // 标准化角度到0-360度范围
        $normalizedAngle = $this->normalizeAngleTo360($angle);

        try {
            // 创建画布
            $this->createCanvas($width, $height, $backgroundColor);

            // 计算文字布局（全面优化版本，彻底解决重叠和超出问题）
            $layout = $this->calculateAdvancedTextLayout(
                $text,
                $width,
                $height,
                $fontName,
                $targetAreaRatio,
                $normalizedAngle,
                $lineHeightRatio,
                $textAlign,
                $textValign,
                $padding,
                $textStyle,
                $textDecoration,
                $strokeColor,
                $strokeWidth
            );

            // 创建文字绘制对象
            $draw = new ImagickDraw();
            $draw->setFont($this->getFontPath($fontName));
            $draw->setFontSize($layout['fontSize']);
            $draw->setFillColor($this->createImagickPixel($textColor));
            $draw->setTextAntialias(true);
            $draw->setTextEncoding('UTF-8');

            // 设置文字透明度
            $draw->setFillOpacity($textOpacity / 100);

            // 设置文字样式
            if (isset(self::$textStyleMap[$textStyle])) {
                $draw->setFontStyle(self::$textStyleMap[$textStyle]);
            }

            // 设置文字装饰
            if (isset(self::$textDecorationMap[$textDecoration])) {
                $draw->setTextDecoration(self::$textDecorationMap[$textDecoration]);
            }

            // 设置文字对齐方式
            $draw->setTextAlignment(self::$textAlignMap[$textAlign] ?? Imagick::ALIGN_CENTER);

            // 设置文字描边
            if ($strokeColor !== null && $strokeWidth > 0) {
                $draw->setStrokeColor($this->createImagickPixel($strokeColor));
                $draw->setStrokeWidth($strokeWidth);
                $draw->setStrokeAntialias(true);
            }

            // 设置文字阴影
            if (!empty($shadow)) {
                $this->applyTextShadow($draw, $shadow);
            }

            // 设置文字背景（如果需要）
            if ($textBackgroundColor !== 'transparent') {
                $this->addAdvancedTextBackground($draw, $layout, $textBackgroundColor, $backgroundOpacity);
            }

            // 绘制文字（全面优化版本，支持特殊角度区间处理）
            $this->drawAdvancedTextLines($draw, $layout);

            if ($download) {
                $this->outputToBrowser('jpeg');
                // 清理绘制对象
                $draw->destroy();
                exit;
            }

            // 清理绘制对象
            $draw->destroy();

        } catch (ImagickException $e) {
            throw new ImagickException("文字生成图片失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 标准化角度到0-360度范围
     *
     * @param float $angle 原始角度
     * @return float 标准化后的角度（0-360度）
     */
    private function normalizeAngleTo360(float $angle): float
    {
        // 处理任意角度值，标准化到0-360度范围
        $angle = fmod($angle, 360);
        if ($angle < 0) {
            $angle += 360;
        }
        return $angle;
    }

    /**
     * 计算高级文字布局 - 彻底解决重叠和超出问题
     *
     * @param string $text 文字内容
     * @param int $width 图片宽度
     * @param int $height 图片高度
     * @param string $fontName 字体名称
     * @param int $targetAreaRatio 目标面积比例
     * @param float $angle 旋转角度（0-360度）
     * @param float $lineHeightRatio 行高比例
     * @param string $textAlign 文字对齐方式
     * @param string $textValign 文字垂直对齐方式
     * @param int $padding 内边距
     * @param string $textStyle 文字样式
     * @param string $textDecoration 文字装饰
     * @param string|null $strokeColor 描边颜色
     * @param float $strokeWidth 描边宽度
     * @return array 布局信息数组
     * @throws ImagickException
     */
    private function calculateAdvancedTextLayout(
        string $text,
        int $width,
        int $height,
        string $fontName,
        int $targetAreaRatio,
        float $angle,
        float $lineHeightRatio,
        string $textAlign,
        string $textValign,
        int $padding,
        string $textStyle,
        string $textDecoration,
        ?string $strokeColor,
        float $strokeWidth
    ): array {
        // 分割文本行
        $lines = array_filter(explode("<br>", $text), 'trim');
        if (empty($lines)) {
            throw new ImagickException("文字内容不能为空");
        }

        // 计算有效区域（考虑内边距）
        $effectiveWidth = $width - ($padding * 2);
        $effectiveHeight = $height - ($padding * 2);

        if ($effectiveWidth <= 0 || $effectiveHeight <= 0) {
            throw new ImagickException("内边距设置过大，导致有效区域为负");
        }

        // 动态计算字体大小（全面优化版本）
        $fontSize = $this->calculateAdvancedFontSize(
            $lines,
            $effectiveWidth,
            $effectiveHeight,
            $fontName,
            $targetAreaRatio,
            $angle,
            $lineHeightRatio,
            $textStyle,
            $textDecoration,
            $strokeColor,
            $strokeWidth
        );

        // 创建临时绘制对象测量文字尺寸
        $draw = new ImagickDraw();
        $draw->setFont($this->getFontPath($fontName));
        $draw->setFontSize($fontSize);
        $draw->setTextAntialias(true);
        $draw->setTextEncoding('UTF-8');

        // 设置文字样式
        if (isset(self::$textStyleMap[$textStyle])) {
            $draw->setFontStyle(self::$textStyleMap[$textStyle]);
        }

        // 设置文字装饰
        if (isset(self::$textDecorationMap[$textDecoration])) {
            $draw->setTextDecoration(self::$textDecorationMap[$textDecoration]);
        }

        // 设置文字描边
        if ($strokeColor !== null && $strokeWidth > 0) {
            $draw->setStrokeColor($this->createImagickPixel($strokeColor));
            $draw->setStrokeWidth($strokeWidth);
            $draw->setStrokeAntialias(true);
        }

        // 计算每行文字尺寸和位置（考虑旋转）
        $lineMetrics = [];
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($lines as $line) {
            $metrics = $this->imagick->queryFontMetrics($draw, $line);
            if (!$metrics) {
                throw new ImagickException("无法测量文字尺寸");
            }

            $lineWidth = $metrics['textWidth'];
            $lineHeight = $metrics['textHeight'] * $lineHeightRatio;

            // 计算旋转后的单行尺寸（关键优化：逐行计算旋转尺寸）
            $rotatedLineDimensions = $this->calculateRotatedLineDimensions($lineWidth, $lineHeight, $angle);

            $lineMetrics[] = [
                'text' => $line,
                'width' => $lineWidth,
                'height' => $lineHeight,
                'rotatedWidth' => $rotatedLineDimensions['width'],
                'rotatedHeight' => $rotatedLineDimensions['height'],
                'ascent' => $metrics['ascender'] ?? $lineHeight * 0.8,
                'descent' => $metrics['descender'] ?? $lineHeight * 0.2,
                'boundingBox' => $rotatedLineDimensions['boundingBox'] ?? [
                        'x1' => 0, 'y1' => 0,
                        'x2' => $lineWidth, 'y2' => $lineHeight
                    ]
            ];

            $maxWidth = max($maxWidth, $lineWidth);
            $totalHeight += $lineHeight;
        }

        // 计算整体旋转后的尺寸（考虑70-290度特殊区间）
        $rotatedDimensions = $this->calculateAdvancedRotatedDimensions($lineMetrics, $angle, $lineHeightRatio);

        // 计算文字起始位置（考虑特殊角度区间）
        $position = $this->calculateAdvancedTextPosition(
            $rotatedDimensions['width'],
            $rotatedDimensions['height'],
            $width,
            $height,
            $textAlign,
            $textValign,
            $padding,
            $angle
        );

        $draw->destroy();

        return [
            'lines' => $lineMetrics,
            'fontSize' => $fontSize,
            'totalWidth' => $maxWidth,
            'totalHeight' => $totalHeight,
            'startX' => $position['x'],
            'startY' => $position['y'],
            'angle' => $angle,
            'lineHeightRatio' => $lineHeightRatio,
            'textAlign' => $textAlign,
            'textValign' => $textValign,
            'rotatedWidth' => $rotatedDimensions['width'],
            'rotatedHeight' => $rotatedDimensions['height'],
            'padding' => $padding,
            'effectiveArea' => [
                'width' => $effectiveWidth,
                'height' => $effectiveHeight
            ],
            'specialAngle' => $this->isSpecialAngle($angle)
        ];
    }

    /**
     * 计算旋转后的单行文字尺寸（关键优化方法）
     *
     * @param float $width 原始宽度
     * @param float $height 原始高度
     * @param float $angle 旋转角度
     * @return array 旋转后的尺寸和边界框
     */
    private function calculateRotatedLineDimensions(float $width, float $height, float $angle): array
    {
        if ($angle == 0) {
            return [
                'width' => $width,
                'height' => $height,
                'boundingBox' => [
                    'x1' => 0, 'y1' => 0,
                    'x2' => $width, 'y2' => $height
                ]
            ];
        }

        $rad = deg2rad($angle);
        $cos = cos($rad);
        $sin = sin($rad);

        // 计算四个角的旋转后坐标
        $corners = [
            [0, 0],                    // 左上角
            [$width, 0],               // 右上角
            [$width, $height],         // 右下角
            [0, $height]               // 左下角
        ];

        $rotatedCorners = [];
        foreach ($corners as $corner) {
            $x = $corner[0];
            $y = $corner[1];
            $rotatedX = $x * $cos - $y * $sin;
            $rotatedY = $x * $sin + $y * $cos;
            $rotatedCorners[] = [$rotatedX, $rotatedY];
        }

        // 计算边界框
        $minX = min(array_column($rotatedCorners, 0));
        $maxX = max(array_column($rotatedCorners, 0));
        $minY = min(array_column($rotatedCorners, 1));
        $maxY = max(array_column($rotatedCorners, 1));

        $rotatedWidth = $maxX - $minX;
        $rotatedHeight = $maxY - $minY;

        return [
            'width' => $rotatedWidth,
            'height' => $rotatedHeight,
            'boundingBox' => [
                'x1' => $minX, 'y1' => $minY,
                'x2' => $maxX, 'y2' => $maxY
            ]
        ];
    }

    /**
     * 计算高级旋转后尺寸（处理70-290度特殊区间）
     *
     * @param array $lineMetrics 行度量数据
     * @param float $angle 旋转角度
     * @param float $lineHeightRatio 行高比例
     * @return array 旋转后的尺寸
     */
    private function calculateAdvancedRotatedDimensions(array $lineMetrics, float $angle, float $lineHeightRatio): array
    {
        // 检查是否为特殊角度区间（70-290度）
        $isSpecialAngle = $this->isSpecialAngle($angle);

        if (!$isSpecialAngle) {
            // 普通角度处理
            return $this->calculateNormalRotatedDimensions($lineMetrics, $angle);
        }

        // 特殊角度区间处理（70-290度）
        return $this->calculateSpecialAngleRotatedDimensions($lineMetrics, $angle, $lineHeightRatio);
    }

    /**
     * 检查是否为特殊角度区间（70-290度）
     *
     * @param float $angle 角度
     * @return bool 是否为特殊角度
     */
    private function isSpecialAngle(float $angle): bool
    {
        return ($angle >= 70 && $angle <= 290);
    }

    /**
     * 计算普通角度旋转后尺寸
     *
     * @param array $lineMetrics 行度量数据
     * @param float $angle 旋转角度
     * @return array 旋转后的尺寸
     */
    private function calculateNormalRotatedDimensions(array $lineMetrics, float $angle): array
    {
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($lineMetrics as $line) {
            $maxWidth = max($maxWidth, $line['rotatedWidth']);
            $totalHeight += $line['rotatedHeight'];
        }

        return [
            'width' => $maxWidth,
            'height' => $totalHeight
        ];
    }

    /**
     * 计算特殊角度区间旋转后尺寸（70-290度）
     *
     * @param array $lineMetrics 行度量数据
     * @param float $angle 旋转角度
     * @param float $lineHeightRatio 行高比例
     * @return array 旋转后的尺寸
     */
    private function calculateSpecialAngleRotatedDimensions(array $lineMetrics, float $angle, float $lineHeightRatio): array
    {
        // 对于特殊角度区间，需要逐行计算并考虑行间重叠
        $totalWidth = 0;
        $totalHeight = 0;
        $previousLineBottom = 0;

        foreach ($lineMetrics as $line) {
            $lineWidth = $line['rotatedWidth'];
            $lineHeight = $line['rotatedHeight'];

            // 考虑行间间距，避免重叠
            $lineSpacing = $lineHeight * ($lineHeightRatio - 1) * 0.5;

            if ($previousLineBottom > 0) {
                $totalHeight += $lineSpacing;
            }

            $totalWidth = max($totalWidth, $lineWidth);
            $totalHeight += $lineHeight;
            $previousLineBottom = $totalHeight;
        }

        return [
            'width' => $totalWidth,
            'height' => $totalHeight
        ];
    }

    /**
     * 计算高级文字位置（考虑特殊角度区间）
     *
     * @param float $textWidth 文字宽度
     * @param float $textHeight 文字高度
     * @param int $canvasWidth 画布宽度
     * @param int $canvasHeight 画布高度
     * @param string $textAlign 水平对齐方式
     * @param string $textValign 垂直对齐方式
     * @param int $padding 内边距
     * @param float $angle 旋转角度
     * @return array 文字起始位置
     */
    private function calculateAdvancedTextPosition(
        float $textWidth,
        float $textHeight,
        int $canvasWidth,
        int $canvasHeight,
        string $textAlign,
        string $textValign,
        int $padding,
        float $angle
    ): array {
        $x = $padding;
        $y = $padding;

        // 水平对齐计算
        switch ($textAlign) {
            case self::TEXT_ALIGN_CENTER:
                $x = ($canvasWidth - $textWidth) / 2;
                break;
            case self::TEXT_ALIGN_RIGHT:
                $x = $canvasWidth - $textWidth - $padding;
                break;
            default: // left
                $x = $padding;
        }

        // 垂直对齐计算
        switch ($textValign) {
            case self::TEXT_VALIGN_MIDDLE:
                $y = ($canvasHeight - $textHeight) / 2;
                break;
            case self::TEXT_VALIGN_BOTTOM:
                $y = $canvasHeight - $textHeight - $padding;
                break;
            default: // top
                $y = $padding;
        }

        // 对于特殊角度区间，进行额外调整
        if ($this->isSpecialAngle($angle)) {
            // 特殊角度区间需要更大的安全边距
            $safeMargin = $padding * 1.5;
            $x = max($safeMargin, min($x, $canvasWidth - $textWidth - $safeMargin));
            $y = max($safeMargin, min($y, $canvasHeight - $textHeight - $safeMargin));
        } else {
            // 普通角度安全边距
            $safeMargin = $padding;
            $x = max($safeMargin, min($x, $canvasWidth - $textWidth - $safeMargin));
            $y = max($safeMargin, min($y, $canvasHeight - $textHeight - $safeMargin));
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * 计算高级字体大小 - 全面优化面积计算
     *
     * @param array $lines 文本行数组
     * @param int $width 有效宽度
     * @param int $height 有效高度
     * @param string $fontName 字体名称
     * @param int $targetAreaRatio 目标面积比例
     * @param float $angle 旋转角度
     * @param float $lineHeightRatio 行高比例
     * @param string $textStyle 文字样式
     * @param string $textDecoration 文字装饰
     * @param string|null $strokeColor 描边颜色
     * @param float $strokeWidth 描边宽度
     * @return int 最佳字体大小
     * @throws ImagickException
     */
    private function calculateAdvancedFontSize(
        array $lines,
        int $width,
        int $height,
        string $fontName,
        int $targetAreaRatio,
        float $angle,
        float $lineHeightRatio,
        string $textStyle,
        string $textDecoration,
        ?string $strokeColor,
        float $strokeWidth
    ): int {
        $maxFontSize = min($width, $height, 300); // 提高最大字体大小限制
        $minFontSize = 6; // 降低最小字体大小限制
        $optimalSize = $minFontSize;

        $draw = new ImagickDraw();
        $draw->setFont($this->getFontPath($fontName));
        $draw->setTextAntialias(true);
        $draw->setTextEncoding('UTF-8');

        // 设置文字样式
        if (isset(self::$textStyleMap[$textStyle])) {
            $draw->setFontStyle(self::$textStyleMap[$textStyle]);
        }

        // 设置文字装饰
        if (isset(self::$textDecorationMap[$textDecoration])) {
            $draw->setTextDecoration(self::$textDecorationMap[$textDecoration]);
        }

        // 设置文字描边
        if ($strokeColor !== null && $strokeWidth > 0) {
            $draw->setStrokeColor($this->createImagickPixel($strokeColor));
            $draw->setStrokeWidth($strokeWidth);
            $draw->setStrokeAntialias(true);
        }

        // 二分查找最佳字体大小
        while ($minFontSize <= $maxFontSize) {
            $currentSize = (int)(($minFontSize + $maxFontSize) / 2);
            $draw->setFontSize($currentSize);

            $totalWidth = 0;
            $totalHeight = 0;
            $fits = true;

            foreach ($lines as $line) {
                $metrics = $this->imagick->queryFontMetrics($draw, $line);
                if (!$metrics) {
                    $fits = false;
                    break;
                }

                $lineWidth = $metrics['textWidth'];
                $lineHeight = $metrics['textHeight'] * $lineHeightRatio;

                // 计算旋转后的单行尺寸
                $rotatedLineDimensions = $this->calculateRotatedLineDimensions($lineWidth, $lineHeight, $angle);
                $rotatedLineWidth = $rotatedLineDimensions['width'];
                $rotatedLineHeight = $rotatedLineDimensions['height'];

                // 检查单行宽度是否超出有效区域
                if ($rotatedLineWidth > $width) {
                    $fits = false;
                    break;
                }

                // 检查总高度是否超出有效区域
                $totalHeight += $rotatedLineHeight;
                if ($totalHeight > $height) {
                    $fits = false;
                    break;
                }

                $totalWidth = max($totalWidth, $rotatedLineWidth);
            }

            if ($fits) {
                // 计算当前文字区域面积
                $currentArea = $totalWidth * $totalHeight;
                $targetArea = ($width * $height) * ($targetAreaRatio / 100);

                if ($currentArea <= $targetArea) {
                    $optimalSize = $currentSize;
                    $minFontSize = $currentSize + 1;
                } else {
                    $maxFontSize = $currentSize - 1;
                }
            } else {
                $maxFontSize = $currentSize - 1;
            }
        }

        $draw->destroy();
        return $optimalSize;
    }

    /**
     * 应用文字阴影
     *
     * @param ImagickDraw $draw 绘制对象
     * @param array $shadow 阴影设置
     * @return void
     */
    private function applyTextShadow(ImagickDraw $draw, array $shadow): void
    {
        $shadowColor = $shadow['color'] ?? '#000000';
        $shadowOpacity = $shadow['opacity'] ?? 50;
        $shadowX = $shadow['x'] ?? 2;
        $shadowY = $shadow['y'] ?? 2;
        $shadowBlur = $shadow['blur'] ?? 3;

        $draw->setTextShadow($this->createImagickPixel($shadowColor), $shadowX, $shadowY, $shadowBlur);
        $draw->setFillOpacity($shadowOpacity / 100);
    }

    /**
     * 添加高级文字背景
     *
     * @param ImagickDraw $draw 绘制对象
     * @param array $layout 布局信息
     * @param string $backgroundColor 背景颜色
     * @param int $backgroundOpacity 背景透明度
     * @return void
     */
    private function addAdvancedTextBackground(ImagickDraw $draw, array $layout, string $backgroundColor, int $backgroundOpacity): void
    {
        $bgDraw = new ImagickDraw();
        $bgDraw->setFillColor($this->createImagickPixel($backgroundColor));
        $bgDraw->setFillOpacity($backgroundOpacity / 100);

        $bgPadding = 15;
        $bgX = $layout['startX'] - $bgPadding;
        $bgY = $layout['startY'] - $bgPadding;
        $bgWidth = $layout['rotatedWidth'] + ($bgPadding * 2);
        $bgHeight = $layout['rotatedHeight'] + ($bgPadding * 2);

        // 添加圆角效果
        $cornerRadius = 10;
        $bgDraw->roundRectangle($bgX, $bgY, $bgX + $bgWidth, $bgY + $bgHeight, $cornerRadius, $cornerRadius);

        $this->imagick->drawImage($bgDraw);
        $bgDraw->destroy();
    }

    /**
     * 绘制高级文字行 - 支持特殊角度区间处理
     *
     * @param ImagickDraw $draw 绘制对象
     * @param array $layout 布局信息
     * @return void
     * @throws ImagickException
     */
    private function drawAdvancedTextLines(ImagickDraw $draw, array $layout): void
    {
        $currentY = $layout['startY'];

        foreach ($layout['lines'] as $lineIndex => $lineInfo) {
            // 计算每行的水平位置
            $x = $this->calculateAdvancedLineXPosition($layout, $lineInfo['width'], $lineIndex);

            // 计算每行的垂直位置（考虑行高和特殊角度）
            $y = $this->calculateAdvancedLineYPosition($layout, $lineInfo, $currentY, $lineIndex);

            // 确保坐标在画布范围内
            $safeMargin = 8;
            $maxX = $this->imagick->getImageWidth() - $lineInfo['rotatedWidth'] - $safeMargin;
            $maxY = $this->imagick->getImageHeight() - $lineInfo['rotatedHeight'] - $safeMargin;

            $x = max($safeMargin, min($x, $maxX));
            $y = max($lineInfo['ascent'] + $safeMargin, min($y, $maxY));

            // 绘制文字行
            $this->imagick->annotateImage($draw, $x, $y, $layout['angle'], $lineInfo['text']);

            // 更新下一行的起始位置
            $currentY = $y + $lineInfo['height'];
        }
    }

    /**
     * 计算高级每行文字的水平位置
     *
     * @param array $layout 布局信息
     * @param float $lineWidth 行宽度
     * @param int $lineIndex 行索引
     * @return float 水平位置
     */
    private function calculateAdvancedLineXPosition(array $layout, float $lineWidth, int $lineIndex): float
    {
        switch ($layout['textAlign']) {
            case self::TEXT_ALIGN_CENTER:
                return $layout['startX'] + ($layout['totalWidth'] - $lineWidth) / 2;
            case self::TEXT_ALIGN_RIGHT:
                return $layout['startX'] + $layout['totalWidth'] - $lineWidth;
            default: // left
                return $layout['startX'];
        }
    }

    /**
     * 计算高级每行文字的垂直位置
     *
     * @param array $layout 布局信息
     * @param array $lineInfo 行信息
     * @param float $currentY 当前Y坐标
     * @param int $lineIndex 行索引
     * @return float 垂直位置
     */
    private function calculateAdvancedLineYPosition(array $layout, array $lineInfo, float $currentY, int $lineIndex): float
    {
        $baseY = $currentY + $lineInfo['ascent'];

        // 对于特殊角度区间，进行额外调整
        if ($layout['specialAngle']) {
            // 特殊角度区间需要更大的行间距
            $lineSpacing = $lineInfo['height'] * ($layout['lineHeightRatio'] - 1) * 0.8;
            $baseY += $lineSpacing * $lineIndex;
        }

        return $baseY;
    }

    // ==========================================
    // 新增图片处理功能
    // ==========================================

    /**
     * 添加图片边框
     *
     * 为图像添加各种样式的边框
     *
     * @param int $width 边框宽度，必须大于0，默认10
     * @param string $color 边框颜色，支持格式：十六进制、颜色名称、RGB值
     *                      默认值：#000000（黑色）
     * @param string $style 边框样式，可选值：solid, dashed, dotted, double
     *                      默认值：solid
     * @param int $opacity 边框透明度，0-100，默认100
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当添加边框失败时抛出异常
     */
    public function addBorder(int $width = 10, string $color = '#000000', string $style = 'solid', int $opacity = 100): self
    {
        $this->validateResource();

        if ($width <= 0) {
            throw new ImagickException("边框宽度必须大于0: {$width}");
        }

        if ($opacity < 0 || $opacity > 100) {
            throw new ImagickException("边框透明度必须在0-100之间: {$opacity}");
        }

        try {
            // 创建边框颜色
            $borderColor = $this->createImagickPixel($color);

            // 设置边框透明度
            if ($opacity < 100) {
                $borderColor->setColorValue(Imagick::COLOR_ALPHA, $opacity / 100);
            }

            // 添加边框
            $this->imagick->borderImage($borderColor, $width, $width);

            // 处理边框样式
            if ($style !== 'solid') {
                $this->applyBorderStyle($style, $width);
            }

        } catch (ImagickException $e) {
            throw new ImagickException("添加边框失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 应用边框样式
     *
     * @param string $style 边框样式
     * @param int $width 边框宽度
     * @return void
     */
    private function applyBorderStyle(string $style, int $width): void
    {
        // 这里可以实现各种边框样式
        // 由于Imagick内置边框样式有限，这里主要处理实线边框
        // 虚线、点线等复杂边框样式需要更复杂的实现
        switch ($style) {
            case 'dashed':
            case 'dotted':
            case 'double':
                // 复杂边框样式的占位实现
                break;
            default:
                // 实线边框，默认处理
                break;
        }
    }

    /**
     * 添加图片阴影
     *
     * 为图像添加阴影效果
     *
     * @param int $xOffset X轴偏移，默认5
     * @param int $yOffset Y轴偏移，默认5
     * @param int $blur 模糊半径，默认10
     * @param string $color 阴影颜色，支持格式：十六进制、颜色名称、RGB值
     *                      默认值：#000000（黑色）
     * @param int $opacity 阴影透明度，0-100，默认50
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当添加阴影失败时抛出异常
     */
    public function addShadow(int $xOffset = 5, int $yOffset = 5, int $blur = 10, string $color = '#000000', int $opacity = 50): self
    {
        $this->validateResource();

        if ($blur < 0) {
            throw new ImagickException("模糊半径不能为负数: {$blur}");
        }

        if ($opacity < 0 || $opacity > 100) {
            throw new ImagickException("阴影透明度必须在0-100之间: {$opacity}");
        }

        try {
            // 创建阴影层
            $shadow = $this->imagick->clone();

            // 设置阴影颜色和透明度
            $shadowColor = $this->createImagickPixel($color);
            $shadow->setImageBackgroundColor($shadowColor);
            $shadow->shadowImage($opacity, $blur, $xOffset, $yOffset);

            // 合并阴影和原图
            $this->imagick->compositeImage($shadow, Imagick::COMPOSITE_OVER, 0, 0);

            $shadow->clear();

        } catch (ImagickException $e) {
            throw new ImagickException("添加阴影失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 图片圆角处理
     *
     * 为图像添加圆角效果
     *
     * @param int $radius 圆角半径，必须大于0，默认20
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当圆角处理失败时抛出异常
     */
    public function roundCorners(int $radius = 20): self
    {
        $this->validateResource();

        if ($radius <= 0) {
            throw new ImagickException("圆角半径必须大于0: {$radius}");
        }

        try {
            $width = $this->imagick->getImageWidth();
            $height = $this->imagick->getImageHeight();

            // 创建圆角蒙版
            $mask = new Imagick();
            $mask->newImage($width, $height, new ImagickPixel('transparent'));

            $draw = new ImagickDraw();
            $draw->setFillColor(new ImagickPixel('white'));
            $draw->roundRectangle(0, 0, $width, $height, $radius, $radius);

            $mask->drawImage($draw);
            $this->imagick->setImageMatte(true);
            $this->imagick->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

            $mask->clear();
            $draw->destroy();

        } catch (ImagickException $e) {
            throw new ImagickException("圆角处理失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    // ==========================================
    // 新增文字处理功能
    // ==========================================

    /**
     * 多行文字自动换行
     *
     * 根据指定宽度自动换行文字
     *
     * @param string $text 文字内容
     * @param int $maxWidth 最大宽度（像素）
     * @param string $fontName 字体名称
     * @param int $fontSize 字体大小
     * @return string 换行后的文字
     * @throws ImagickException
     */
    public function autoWrapText(string $text, int $maxWidth, string $fontName = 'pmzdxx', int $fontSize = 12): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        if ($maxWidth <= 0) {
            throw new ImagickException("最大宽度必须大于0: {$maxWidth}");
        }

        if ($fontSize <= 0) {
            throw new ImagickException("字体大小必须大于0: {$fontSize}");
        }

        try {
            $draw = new ImagickDraw();
            $draw->setFont($this->getFontPath($fontName));
            $draw->setFontSize($fontSize);
            $draw->setTextAntialias(true);
            $draw->setTextEncoding('UTF-8');

            $words = preg_split('/\s+/', $text);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
                $metrics = $this->imagick->queryFontMetrics($draw, $testLine);

                if ($metrics['textWidth'] <= $maxWidth) {
                    $currentLine = $testLine;
                } else {
                    if (!empty($currentLine)) {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                }
            }

            if (!empty($currentLine)) {
                $lines[] = $currentLine;
            }

            $draw->destroy();

            return implode("<br>", $lines);

        } catch (ImagickException $e) {
            throw new ImagickException("文字自动换行失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 获取文字尺寸信息
     *
     * 获取指定文字的宽度、高度等尺寸信息
     *
     * @param string $text 文字内容
     * @param string $fontName 字体名称
     * @param int $fontSize 字体大小
     * @param string $textStyle 文字样式
     * @return array 文字尺寸信息
     * @throws ImagickException
     */
    public function getTextDimensions(string $text, string $fontName = 'pmzdxx', int $fontSize = 12, string $textStyle = 'normal'): array
    {
        if (empty(trim($text))) {
            return [
                'width' => 0,
                'height' => 0,
                'ascender' => 0,
                'descender' => 0
            ];
        }

        if ($fontSize <= 0) {
            throw new ImagickException("字体大小必须大于0: {$fontSize}");
        }

        try {
            $draw = new ImagickDraw();
            $draw->setFont($this->getFontPath($fontName));
            $draw->setFontSize($fontSize);
            $draw->setTextAntialias(true);
            $draw->setTextEncoding('UTF-8');

            // 设置文字样式
            if (isset(self::$textStyleMap[$textStyle])) {
                $draw->setFontStyle(self::$textStyleMap[$textStyle]);
            }

            $metrics = $this->imagick->queryFontMetrics($draw, $text);

            $draw->destroy();

            return [
                'width' => $metrics['textWidth'] ?? 0,
                'height' => $metrics['textHeight'] ?? 0,
                'ascender' => $metrics['ascender'] ?? 0,
                'descender' => $metrics['descender'] ?? 0,
                'characterWidth' => $metrics['characterWidth'] ?? 0,
                'characterHeight' => $metrics['characterHeight'] ?? 0
            ];

        } catch (ImagickException $e) {
            throw new ImagickException("获取文字尺寸失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    // ==========================================
    // 格式转换功能
    // ==========================================

    /**
     * 转换为ICO格式
     *
     * 将图像转换为Windows图标格式，支持单尺寸或多尺寸
     *
     * @param string|null $outputPath 输出路径，null时直接下载到浏览器
     *                                示例：/path/to/icon.ico
     * @param int $size 图标尺寸，必须大于0，建议范围：16-256
     *                  默认值：48
     * @param bool $download 是否直接下载到浏览器（$outputPath 为 null 时生效）
     * @return bool|string 转换成功返回true|base64编码字符串
     * @throws ImagickException 当转换失败时抛出异常
     */
    public function convertToIco(?string $outputPath = null, int $size = 48, bool $download = false): bool|string
    {
        $this->validateResource();

        // 验证尺寸参数
        if ($size <= 0 || $size > 256) {
            throw new ImagickException("图标尺寸必须在1-256之间: {$size}");
        }

        try {
            $ico = new Imagick();

            // 创建单个尺寸的ICO图标
            $layer = clone $this->imagick;

            // 调整尺寸并保持比例
            $layer->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1, true);
            $layer->setImageFormat('PNG');

            // 设置背景为透明（对于ICO格式很重要）
            $layer->setImageBackgroundColor(new ImagickPixel('transparent'));
            $layer->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

            // 添加到ICO图像序列
            $ico->addImage($layer);
            $layer->clear();

            // 设置ICO格式
            $ico->setImageFormat('ICO');

            if ($outputPath !== null) {
                // 保存到文件模式
                $this->prepareOutputDirectory($outputPath);
                $result = $ico->writeImages($outputPath, true);
            } else {
                if(!$download){
                    $this->imagick = $ico;
                    return $this->toBase64('ICO');
                }
                // 直接下载到浏览器模式
                if (headers_sent()) {
                    throw new ImagickException("HTTP头已经发送，无法输出ICO文件");
                }

                // 设置HTTP响应头
                header('Content-Type: image/x-icon');
                header('Content-Disposition: attachment; filename="favicon.ico"');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');

                // 输出ICO二进制数据
                echo $ico->getImagesBlob();
                $ico->clear();

                // 终止脚本执行，确保只输出ICO数据
                exit(0);
            }

            $ico->clear();
            return $result;

        } catch (ImagickException $e) {
            throw new ImagickException("转换为ICO格式失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 图片转PDF
     *
     * 将当前图像转换为PDF文档
     *
     * @param string $outputPath 输出PDF路径
     *                           示例：/path/to/document.pdf
     * @param string $pageSize 页面尺寸，可选值：
     *                         A4, A3, A2, Letter, Legal, Tabloid
     *                         默认值：A4
     * @param string $orientation 页面方向，可选值：
     *                            - 'portrait': 纵向
     *                            - 'landscape': 横向
     *                            默认值：portrait
     * @return bool 转换成功返回true
     * @throws ImagickException 当转换失败时抛出异常
     */
    public function convertToPdf(string $outputPath, string $pageSize = 'A4', string $orientation = 'portrait'): bool
    {
        $this->validateResource();

        // 验证页面尺寸
        if (!isset(self::$pageSizes[$pageSize])) {
            throw new ImagickException("不支持的页面尺寸: {$pageSize}");
        }

        // 验证页面方向
        if (!in_array($orientation, ['portrait', 'landscape'])) {
            throw new ImagickException("页面方向必须是 'portrait' 或 'landscape': {$orientation}");
        }

        try {
            // 设置PDF格式
            $this->imagick->setImageFormat('PDF');

            // 获取页面尺寸
            [$width, $height] = self::$pageSizes[$pageSize];
            if ($orientation === 'landscape') {
                [$width, $height] = [$height, $width]; // 交换宽高
            }

            // 调整图像尺寸以适应页面
            $this->imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);

            // 设置页面尺寸
            $this->imagick->setImagePage($width, $height, 0, 0);

            $this->prepareOutputDirectory($outputPath);
            $result = $this->imagick->writeImage($outputPath);

            return $result;

        } catch (ImagickException $e) {
            throw new ImagickException("转换为PDF失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * PDF转图片
     *
     * 将PDF文档转换为图片，支持多页转换
     *
     * @param string $pdfPath PDF文件路径
     *                        示例：/path/to/document.pdf
     * @param string $outputDir 输出目录
     *                          示例：/path/to/output
     * @param string $format 输出格式，可选值：png, jpg, jpeg, webp
     *                       默认值：png
     * @param int $quality 图片质量，范围1-100，默认90
     * @param int $resolution 分辨率，范围72-600，默认150
     * @return array 生成的图片路径数组
     * @throws ImagickException 当转换失败时抛出异常
     */
    public function convertPdfToImages(string $pdfPath, string $outputDir, string $format = 'png', int $quality = 90, int $resolution = 150): array
    {
        $this->validateFile($pdfPath);

        // 验证输出目录
        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new ImagickException("无法创建输出目录: {$outputDir}");
        }

        // 验证质量参数
        if ($quality < 1 || $quality > 100) {
            throw new ImagickException("图片质量必须在1-100之间: {$quality}");
        }

        // 验证分辨率
        if ($resolution < 72 || $resolution > 600) {
            throw new ImagickException("分辨率必须在72-600之间: {$resolution}");
        }

        try {
            $pdf = new Imagick();
            $pdf->setResolution($resolution, $resolution);
            $pdf->readImage($pdfPath);

            $outputFiles = [];

            foreach ($pdf as $pageNumber => $page) {
                $page->setImageFormat($format);
                $page->setImageCompressionQuality($quality);

                $outputFile = $outputDir . '/page_' . ($pageNumber + 1) . '.' . $format;
                $page->writeImage($outputFile);
                $outputFiles[] = $outputFile;
            }

            $pdf->clear();

            return $outputFiles;

        } catch (ImagickException $e) {
            throw new ImagickException("PDF转图片失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    // ==========================================
    // 图像滤镜和特效
    // ==========================================

    /**
     * 应用图像滤镜
     *
     * 应用多种图像滤镜效果，如模糊、锐化、艺术效果等
     *
     * @param string $filterType 滤镜类型，使用FILTER常量，可选值：
     *                           FILTER_GAUSSIAN_BLUR, FILTER_MOTION_BLUR, FILTER_RADIAL_BLUR,
     *                           FILTER_SHARPEN, FILTER_EDGE_DETECT, FILTER_EMBOSS, FILTER_OIL_PAINT,
     *                           FILTER_WATERCOLOR, FILTER_CHARCOAL, FILTER_PIXELATE, FILTER_SEPIA,
     *                           FILTER_VIGNETTE, FILTER_NOISE, FILTER_SOLARIZE
     * @param array $parameters 滤镜参数，具体参数因滤镜类型而异：
     *                          - 高斯模糊: ['radius' => 5, 'sigma' => 3]
     *                          - 运动模糊: ['radius' => 10, 'sigma' => 5, 'angle' => 0]
     *                          - 锐化: ['radius' => 2, 'sigma' => 1]
     *                          - 浮雕: ['radius' => 1, 'sigma' => 0.5]
     *                          - 油画: ['radius' => 3]
     *                          - 炭笔画: ['radius' => 1, 'sigma' => 0.5]
     *                          - 复古: ['threshold' => 80]
     *                          - 暗角: ['black_point' => 0.3, 'white_point' => 0.1, 'x' => 0, 'y' => 0]
     *                          默认值：[]
     * @return self 返回当前对象实例，支持链式调用
     * @throws ImagickException 当应用滤镜失败时抛出异常
     */
    public function applyFilter(string $filterType, array $parameters = []): self
    {
        $this->validateResource();

        // 验证滤镜类型
        $validFilters = [
            self::FILTER_GAUSSIAN_BLUR, self::FILTER_MOTION_BLUR, self::FILTER_RADIAL_BLUR,
            self::FILTER_SHARPEN, self::FILTER_EDGE_DETECT, self::FILTER_EMBOSS,
            self::FILTER_OIL_PAINT, self::FILTER_WATERCOLOR, self::FILTER_CHARCOAL,
            self::FILTER_PIXELATE, self::FILTER_SEPIA, self::FILTER_VIGNETTE,
            self::FILTER_NOISE, self::FILTER_SOLARIZE
        ];

        if (!in_array($filterType, $validFilters)) {
            throw new ImagickException("不支持的滤镜类型: {$filterType}");
        }

        try {
            switch ($filterType) {
                case self::FILTER_GAUSSIAN_BLUR:
                    $radius = $parameters['radius'] ?? 5;
                    $sigma = $parameters['sigma'] ?? 3;
                    $this->imagick->gaussianBlurImage($radius, $sigma);
                    break;

                case self::FILTER_MOTION_BLUR:
                    $radius = $parameters['radius'] ?? 10;
                    $sigma = $parameters['sigma'] ?? 5;
                    $angle = $parameters['angle'] ?? 0;
                    $this->imagick->motionBlurImage($radius, $sigma, $angle);
                    break;

                case self::FILTER_RADIAL_BLUR:
                    $angle = $parameters['angle'] ?? 10;
                    $this->imagick->radialBlurImage($angle);
                    break;

                case self::FILTER_SHARPEN:
                    $radius = $parameters['radius'] ?? 2;
                    $sigma = $parameters['sigma'] ?? 1;
                    $this->imagick->sharpenImage($radius, $sigma);
                    break;

                case self::FILTER_EDGE_DETECT:
                    $radius = $parameters['radius'] ?? 2;
                    $this->imagick->edgeImage($radius);
                    break;

                case self::FILTER_EMBOSS:
                    $radius = $parameters['radius'] ?? 1;
                    $sigma = $parameters['sigma'] ?? 0.5;
                    $this->imagick->embossImage($radius, $sigma);
                    break;

                case self::FILTER_OIL_PAINT:
                    $radius = $parameters['radius'] ?? 3;
                    $this->imagick->oilPaintImage($radius);
                    break;

                case self::FILTER_CHARCOAL:
                    $radius = $parameters['radius'] ?? 1;
                    $sigma = $parameters['sigma'] ?? 0.5;
                    $this->imagick->charcoalImage($radius, $sigma);
                    break;

                case self::FILTER_SEPIA:
                    $threshold = $parameters['threshold'] ?? 80;
                    $this->imagick->sepiaToneImage($threshold);
                    break;

                case self::FILTER_VIGNETTE:
                    $blackPoint = $parameters['black_point'] ?? 0.3;
                    $whitePoint = $parameters['white_point'] ?? 0.1;
                    $x = $parameters['x'] ?? 0;
                    $y = $parameters['y'] ?? 0;
                    $this->imagick->vignetteImage($blackPoint, $whitePoint, $x, $y);
                    break;

                case self::FILTER_PIXELATE:
                    $width = $parameters['width'] ?? 10;
                    $height = $parameters['height'] ?? 10;
                    $this->imagick->scaleImage($width, $height);
                    $this->imagick->scaleImage(
                        $this->imagick->getImageWidth() * $width,
                        $this->imagick->getImageHeight() * $height
                    );
                    break;

                default:
                    throw new ImagickException("滤镜类型暂未实现: {$filterType}");
            }

        } catch (ImagickException $e) {
            throw new ImagickException("应用滤镜失败: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 批量应用滤镜
     *
     * 为多张图片批量应用相同的滤镜效果
     *
     * @param array $imagePaths 图片路径数组
     * @param string $filterType 滤镜类型，使用FILTER常量
     * @param array $parameters 滤镜参数
     * @param string $outputDir 输出目录
     * @return array 处理结果数组
     * @throws ImagickException 当批量处理失败时抛出异常
     */
    public function batchApplyFilter(array $imagePaths, string $filterType, array $parameters, string $outputDir): array
    {
        // 验证输入参数
        if (empty($imagePaths)) {
            throw new ImagickException("图像路径数组不能为空");
        }

        if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
            throw new ImagickException("无法创建输出目录: {$outputDir}");
        }

        $results = [];

        foreach ($imagePaths as $inputPath) {
            try {
                // 验证输入文件
                $this->validateFile($inputPath);

                // 打开图像
                $this->openImage($inputPath);
                // 应用滤镜
                $this->applyFilter($filterType, $parameters);

                // 生成输出文件名
                $filename = pathinfo($inputPath, PATHINFO_FILENAME);
                $extension = pathinfo($inputPath, PATHINFO_EXTENSION);
                $outputPath = $outputDir . '/' . $filename . '_filtered.' . $extension;

                // 保存结果
                $result = $this->saveImage($outputPath);

                $results[] = [
                    'input' => $inputPath,
                    'output' => $outputPath,
                    'success' => (bool)$result,
                    'message' => '滤镜应用成功'
                ];

            } catch (Exception $e) {
                $results[] = [
                    'input' => $inputPath,
                    'output' => null,
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            } finally {
                // 确保资源被清理
                $this->cleanup();
            }
        }

        return $results;
    }

    // ==========================================
    // 辅助方法和工具函数
    // ==========================================

    /**
     * 验证文件是否存在且可读
     *
     * @param string $filePath 文件路径
     * @throws ImagickException 当文件不存在或不可读时抛出异常
     */
    private function validateFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new ImagickException("文件不存在: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new ImagickException("文件不可读: {$filePath}");
        }

        // 检查文件大小（防止处理过大文件）
        $fileSize = filesize($filePath);
        if ($fileSize > 100 * 1024 * 1024) { // 100MB限制
            throw new ImagickException("文件过大，最大支持100MB: {$filePath}");
        }
    }

    /**
     * 验证资源是否已加载
     *
     * @throws ImagickException 当资源未加载时抛出异常
     */
    private function validateResource(): void
    {
        if (!$this->resourceLoaded) {
            throw new ImagickException('图像资源未加载，请先打开图像文件或创建画布');
        }
    }

    /**
     * 准备输出目录
     *
     * @param string $outputPath 输出路径
     * @throws ImagickException 当目录创建失败时抛出异常
     */
    private function prepareOutputDirectory(string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (!empty($directory) && !is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new ImagickException("无法创建输出目录: {$directory}");
        }
    }

    /**
     * 获取字体路径
     *
     * @param string|null $fontName 字体名称
     * @return string 字体路径
     */
    private function getFontPath(?string $fontName = 'pmzdxx'): string
    {
        $fontName = empty($fontName) ? 'pmzdxx' : $fontName;

        // 如果是完整路径，直接返回
        if (is_file($fontName)) {
            return $fontName;
        }

        // 构建字体路径
        $fontPath = dirname(__DIR__, 1).'/resource/font/'.$fontName.'.ttf';
        // 如果字体文件不存在，使用默认字体
        if (!is_file($fontPath)) {
            $fontPath = dirname(__DIR__, 1).'/resource/font/pmzdxx.ttf';
        }

        return $fontPath;
    }

    /**
     * 创建 ImagickPixel 对象 - 修复了构造异常问题
     *
     * @param string $color 颜色字符串
     * @return ImagickPixel
     * @throws ImagickException 当颜色格式无效时抛出异常
     */
    private function createImagickPixel(string $color): ImagickPixel
    {
        try {
            // 预处理颜色字符串
            $color = $this->normalizeColor($color);
            return new ImagickPixel($color);
        } catch (ImagickException $e) {
            // 如果颜色格式无效，使用默认颜色
            try {
                return new ImagickPixel('#FFFFFF'); // 默认白色
            } catch (ImagickException $e) {
                throw new ImagickException("无法创建 ImagickPixel 对象，颜色格式无效: {$color}", $e->getCode(), $e);
            }
        }
    }

    /**
     * 标准化颜色格式
     *
     * @param string $color 颜色字符串
     * @return string 标准化后的颜色字符串
     */
    private function normalizeColor(string $color): string
    {
        // 转换为小写
        $color = strtolower(trim($color));

        // 处理透明颜色
        if ($color === 'transparent') {
            return 'rgba(255,255,255,0)';
        }

        // 处理RGB格式
        if (preg_match('/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/', $color, $matches)) {
            return $color;
        }

        // 处理RGBA格式
        if (preg_match('/^rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)$/', $color, $matches)) {
            return $color;
        }

        // 处理十六进制格式（3位）
        if (preg_match('/^#([a-f0-9]{3})$/', $color, $matches)) {
            $r = hexdec($matches[1][0].$matches[1][0]);
            $g = hexdec($matches[1][1].$matches[1][1]);
            $b = hexdec($matches[1][2].$matches[1][2]);
            return "rgb($r,$g,$b)";
        }

        // 处理十六进制格式（6位）
        if (preg_match('/^#([a-f0-9]{6})$/', $color, $matches)) {
            $r = hexdec(substr($matches[1], 0, 2));
            $g = hexdec(substr($matches[1], 2, 2));
            $b = hexdec(substr($matches[1], 4, 2));
            return "rgb($r,$g,$b)";
        }

        // 常见的颜色名称映射
        $colorMap = [
            'black' => 'rgb(0,0,0)',
            'white' => 'rgb(255,255,255)',
            'red' => 'rgb(255,0,0)',
            'green' => 'rgb(0,255,0)',
            'blue' => 'rgb(0,0,255)',
            'yellow' => 'rgb(255,255,0)',
            'cyan' => 'rgb(0,255,255)',
            'magenta' => 'rgb(255,0,255)',
            'gray' => 'rgb(128,128,128)',
            'grey' => 'rgb(128,128,128)',
            'silver' => 'rgb(192,192,192)',
            'maroon' => 'rgb(128,0,0)',
            'olive' => 'rgb(128,128,0)',
            'lime' => 'rgb(0,128,0)',
            'aqua' => 'rgb(0,128,128)',
            'teal' => 'rgb(0,128,128)',
            'navy' => 'rgb(0,0,128)',
            'purple' => 'rgb(128,0,128)',
            'fuchsia' => 'rgb(255,0,255)',
        ];

        if (isset($colorMap[$color])) {
            return $colorMap[$color];
        }

        // 如果无法识别，返回默认颜色
        return 'rgb(255,255,255)';
    }

    /**
     * 计算水印位置坐标
     *
     * @param int $position 水印位置
     * @param Imagick $watermark 水印图像
     * @param int $padding 边距
     * @return array [x, y] 坐标数组
     */
    private function calculateWatermarkPosition(int $position, int $padding, Imagick $watermark): array
    {
        $imageWidth = $this->imagick->getImageWidth();
        $imageHeight = $this->imagick->getImageHeight();
        $watermarkWidth = $watermark->getImageWidth();
        $watermarkHeight = $watermark->getImageHeight();

        $x = 0;
        $y = 0;

        switch ($position) {
            case self::POSITION_TOP_LEFT:
                $x = $padding;
                $y = $padding;
                break;
            case self::POSITION_TOP_CENTER:
                $x = ($imageWidth - $watermarkWidth) / 2;
                $y = $padding;
                break;
            case self::POSITION_TOP_RIGHT:
                $x = $imageWidth - $watermarkWidth - $padding;
                $y = $padding;
                break;
            case self::POSITION_MIDDLE_LEFT:
                $x = $padding;
                $y = ($imageHeight - $watermarkHeight) / 2;
                break;
            case self::POSITION_CENTER:
                $x = ($imageWidth - $watermarkWidth) / 2;
                $y = ($imageHeight - $watermarkHeight) / 2;
                break;
            case self::POSITION_MIDDLE_RIGHT:
                $x = $imageWidth - $watermarkWidth - $padding;
                $y = ($imageHeight - $watermarkHeight) / 2;
                break;
            case self::POSITION_BOTTOM_LEFT:
                $x = $padding;
                $y = $imageHeight - $watermarkHeight - $padding;
                break;
            case self::POSITION_BOTTOM_CENTER:
                $x = ($imageWidth - $watermarkWidth) / 2;
                $y = $imageHeight - $watermarkHeight - $padding;
                break;
            case self::POSITION_BOTTOM_RIGHT:
                $x = $imageWidth - $watermarkWidth - $padding;
                $y = $imageHeight - $watermarkHeight - $padding;
                break;
        }

        return [max(0, (int)$x), max(0, (int)$y)];
    }

    /**
     * 设置水印文字位置
     *
     * @param int|null $position 水印位置
     * @param ImagickDraw $draw 绘制对象
     * @param int $padding 边距
     * @return void
     */
    private function setWatermarkPosition(?int $position, ImagickDraw $draw, int $padding): void
    {
        if ($position === null) {
            return;
        }

        $gravityMap = [
            self::POSITION_TOP_LEFT => Imagick::GRAVITY_NORTHWEST,
            self::POSITION_TOP_CENTER => Imagick::GRAVITY_NORTH,
            self::POSITION_TOP_RIGHT => Imagick::GRAVITY_NORTHEAST,
            self::POSITION_MIDDLE_LEFT => Imagick::GRAVITY_WEST,
            self::POSITION_CENTER => Imagick::GRAVITY_CENTER,
            self::POSITION_MIDDLE_RIGHT => Imagick::GRAVITY_EAST,
            self::POSITION_BOTTOM_LEFT => Imagick::GRAVITY_SOUTHWEST,
            self::POSITION_BOTTOM_CENTER => Imagick::GRAVITY_SOUTH,
            self::POSITION_BOTTOM_RIGHT => Imagick::GRAVITY_SOUTHEAST,
        ];

        $draw->setGravity($gravityMap[$position] ?? Imagick::GRAVITY_SOUTHEAST);
    }

    /**
     * 检查图像是否包含Alpha通道
     *
     * @return bool 包含Alpha通道返回true，否则返回false
     */
    private function hasAlphaChannel(): bool
    {
        try {
            return $this->imagick->getImageAlphaChannel() !== 0;
        } catch (ImagickException $e) {
            return false;
        }
    }

    /**
     * 优化图像输出
     *
     * @return void
     */
    private function optimizeImageForOutput(): void
    {
        try {
            $this->imagick->stripImage(); // 移除EXIF等元数据，减小文件大小
            $this->imagick->setInterlaceScheme(Imagick::INTERLACE_PLANE); // 设置渐进式加载

            // 对于PNG格式，尝试优化压缩
            if ($this->imagick->getImageFormat() === 'PNG') {
                $this->imagick->setImageCompression(Imagick::COMPRESSION_ZIP);
            }
        } catch (ImagickException $e) {
            // 忽略优化过程中的异常
        }
    }

    /**
     * 优化图像实例
     *
     * @param Imagick $image 图像实例
     * @return void
     */
    private function optimizeImageInstance(Imagick $image): void
    {
        try {
            $image->stripImage();
            $image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
        } catch (ImagickException $e) {
            // 忽略优化过程中的异常
        }
    }

    /**
     * 将Imagick对象转换为Base64
     *
     * @param Imagick $image Imagick对象
     * @return string Base64编码的图像数据
     */
    private function imageToBase64(Imagick $image): string
    {
        try {
            $blob = $image->getImageBlob();
            $mime = $image->getImageMimeType();
            return 'data:' . $mime . ';base64,' . base64_encode($blob);
        } catch (ImagickException $e) {
            return '';
        }
    }

    /**
     * 为文字添加背景（使用布局信息）
     *
     * @param ImagickDraw $draw 绘制对象
     * @param array $layout 布局信息
     * @param string $backgroundColor 背景颜色
     * @return void
     */
    private function addTextBackgroundWithLayout(ImagickDraw $draw, array $layout, string $backgroundColor): void
    {
        $bgDraw = new ImagickDraw();
        $bgDraw->setFillColor($this->createImagickPixel($backgroundColor));
        $bgDraw->setFillOpacity(0.8);

        $padding = 10;
        $bgX = $layout['startX'] - $padding;
        $bgY = $layout['startY'] - $padding;
        $bgWidth = $layout['totalWidth'] + ($padding * 2);
        $bgHeight = $layout['totalHeight'] + ($padding * 2);

        $bgDraw->rectangle($bgX, $bgY, $bgX + $bgWidth, $bgY + $bgHeight);
        $this->imagick->drawImage($bgDraw);
        $bgDraw->destroy();
    }

    /**
     * 为文字水印添加背景
     *
     * @param string $text 文字内容
     * @param ImagickDraw $draw 绘制对象
     * @param int $position 位置
     * @param int $padding 边距
     * @param string $backgroundColor 背景颜色
     * @param int $backgroundOpacity 背景透明度
     * @return void
     */
    private function addTextBackgroundWithDraw(string $text, ImagickDraw $draw, int $position, int $padding, string $backgroundColor, int $backgroundOpacity): void
    {
        $metrics = $this->imagick->queryFontMetrics($draw, $text);
        $textWidth = $metrics['textWidth'];
        $textHeight = $metrics['textHeight'];

        $backgroundDraw = new ImagickDraw();
        $backgroundDraw->setFillColor($this->createImagickPixel($backgroundColor));
        $backgroundDraw->setFillOpacity($backgroundOpacity / 100.0);

        [$bgX, $bgY] = $this->calculateTextBackgroundPosition($position, $textWidth, $textHeight, $padding);
        $backgroundDraw->rectangle($bgX, $bgY, $bgX + $textWidth + 10, $bgY + $textHeight + 5);

        $this->imagick->drawImage($backgroundDraw);
        $backgroundDraw->destroy();
    }

    /**
     * 计算文字背景位置
     *
     * @param int $position 位置
     * @param float $textWidth 文字宽度
     * @param float $textHeight 文字高度
     * @param int $padding 边距
     * @return array [x, y] 坐标数组
     */
    private function calculateTextBackgroundPosition(int $position, float $textWidth, float $textHeight, int $padding): array
    {
        $imageWidth = $this->imagick->getImageWidth();
        $imageHeight = $this->imagick->getImageHeight();

        $positions = [
            self::POSITION_TOP_LEFT => [$padding, $padding],
            self::POSITION_TOP_CENTER => [($imageWidth - $textWidth) / 2 - 5, $padding],
            self::POSITION_TOP_RIGHT => [$imageWidth - $textWidth - $padding - 10, $padding],
            self::POSITION_MIDDLE_LEFT => [$padding, ($imageHeight - $textHeight) / 2 - 2.5],
            self::POSITION_CENTER => [
                ($imageWidth - $textWidth) / 2 - 5,
                ($imageHeight - $textHeight) / 2 - 2.5
            ],
            self::POSITION_MIDDLE_RIGHT => [
                $imageWidth - $textWidth - $padding - 10,
                ($imageHeight - $textHeight) / 2 - 2.5
            ],
            self::POSITION_BOTTOM_LEFT => [$padding, $imageHeight - $textHeight - $padding - 5],
            self::POSITION_BOTTOM_CENTER => [
                ($imageWidth - $textWidth) / 2 - 5,
                $imageHeight - $textHeight - $padding - 5
            ],
            self::POSITION_BOTTOM_RIGHT => [
                $imageWidth - $textWidth - $padding - 10,
                $imageHeight - $textHeight - $padding - 5
            ],
        ];

        return $positions[$position] ?? $positions[self::POSITION_BOTTOM_RIGHT];
    }

    /**
     * 应用渐变透明度
     *
     * @param Imagick $image 图像对象
     * @param string $gradientType 渐变类型
     * @param float $intensity 渐变强度
     * @param int $index 图片索引
     * @param int $total 图片总数
     * @return void
     */
    private function applyGradient(Imagick $canvas, string $gradientType, float $intensity): void
    {
        $width = $canvas->getImageWidth();
        $height = $canvas->getImageHeight();

        // 创建渐变遮罩
        $gradient = new Imagick();

        switch ($gradientType) {
            case self::GRADIENT_DIAGONAL_LEFT:
            case self::GRADIENT_LEFT_TO_RIGHT:
                $gradient->newPseudoImage($width, $height, 'gradient:black-white');
                break;
            case self::GRADIENT_DIAGONAL_RIGHT:
            case self::GRADIENT_RIGHT_TO_LEFT:
                $gradient->newPseudoImage($width, $height, 'gradient:black-white');
                $gradient->flopImage();
                break;
            case self::GRADIENT_TOP_TO_BOTTOM:
                $gradient->newPseudoImage($width, $height, 'gradient:black-white');
                $gradient->rotateImage('black', 90);
                break;
            case self::GRADIENT_BOTTOM_TO_TOP:
                $gradient->newPseudoImage($width, $height, 'gradient:black-white');
                $gradient->rotateImage('black', -90);
                break;
            case self::GRADIENT_CENTER_TO_EDGE:
                $gradient->newPseudoImage($width, $height, 'radial-gradient:black-white');
                break;
            case self::GRADIENT_EDGE_TO_CENTER:
                $gradient->newPseudoImage($width, $height, 'radial-gradient:white-black');
                break;
        }

        // 调整渐变强度
        $gradient->evaluateImage(Imagick::EVALUATE_MULTIPLY, $intensity, Imagick::CHANNEL_ALPHA);

        // 应用渐变透明度
        $canvas->compositeImage($gradient, Imagick::COMPOSITE_DSTIN, 0, 0);
        $gradient->destroy();
    }

    // ==========================================
    // 拼图布局实现方法
    // ==========================================

    /**
     * 创建网格拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param int $rows 行数
     * @param int $cols 列数
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createGridCollage(array $imagePaths, int $rows, int $cols, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $borderWidth = $options['border_width'] ?? 0;
        $borderColor = $options['border_color'] ?? '#000000';
        $roundCorners = $options['round_corners'] ?? 0;

        $cellWidth = (int)($this->imagick->getImageWidth() / $cols);
        $cellHeight = (int)($this->imagick->getImageHeight() / $rows);

        $index = 0;
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                if ($index < count($imagePaths)) {
                    $image = new Imagick($imagePaths[$index]);

                    // 调整图片尺寸以适应单元格
                    $image->thumbnailImage($cellWidth - $spacing, $cellHeight - $spacing, true);

                    // 应用圆角效果（如果需要）
                    if ($roundCorners > 0) {
                        $this->applyRoundCorners($image, $roundCorners);
                    }

                    // 应用边框效果（如果需要）
                    if ($borderWidth > 0) {
                        $this->applyBorder($image, $borderWidth, $borderColor);
                    }

                    $x = $col * $cellWidth + (int)($spacing / 2);
                    $y = $row * $cellHeight + (int)($spacing / 2);

                    $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, $x, $y);
                    $image->clear();
                    $index++;
                }
            }
        }
    }

    /**
     * 创建圆形拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createCircleCollage(array $imagePaths, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $radius = min($this->imagick->getImageWidth(), $this->imagick->getImageHeight()) / 3;
        $centerX = $this->imagick->getImageWidth() / 2;
        $centerY = $this->imagick->getImageHeight() / 2;

        $angleStep = 2 * M_PI / count($imagePaths);
        $smallImageSize = (int)($radius / 2);

        foreach ($imagePaths as $index => $path) {
            $angle = $index * $angleStep;
            $x = $centerX + $radius * cos($angle) - $smallImageSize / 2;
            $y = $centerY + $radius * sin($angle) - $smallImageSize / 2;

            $image = new Imagick($path);
            $image->thumbnailImage($smallImageSize, $smallImageSize, true);

            // 创建圆形蒙版
            $mask = new Imagick();
            $mask->newImage($smallImageSize, $smallImageSize, new ImagickPixel('transparent'));
            $maskDraw = new ImagickDraw();
            $maskDraw->setFillColor(new ImagickPixel('white'));
            $maskDraw->circle($smallImageSize/2, $smallImageSize/2, $smallImageSize/2, $smallImageSize);
            $mask->drawImage($maskDraw);

            $image->setImageMatte(true);
            $image->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

            $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

            $image->clear();
            $mask->clear();
            $maskDraw->destroy();
        }
    }

    /**
     * 创建螺旋拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createSpiralCollage(array $imagePaths, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $centerX = $this->imagick->getImageWidth() / 2;
        $centerY = $this->imagick->getImageHeight() / 2;
        $maxRadius = min($centerX, $centerY) * 0.8;

        foreach ($imagePaths as $index => $path) {
            $progress = $index / count($imagePaths);
            $angle = $progress * 8 * M_PI; // 4圈螺旋
            $radius = $progress * $maxRadius;

            $imageSize = (int)(50 + 100 * $progress); // 图片大小随进度增加

            $x = $centerX + $radius * cos($angle) - $imageSize / 2;
            $y = $centerY + $radius * sin($angle) - $imageSize / 2;

            $image = new Imagick($path);
            $image->thumbnailImage($imageSize, $imageSize, true);

            $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);
            $image->clear();
        }
    }

    /**
     * 创建对角线拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createDiagonalCollage(array $imagePaths, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $width = $this->imagick->getImageWidth();
        $height = $this->imagick->getImageHeight();
        $imageSize = min($width, $height) / (count($imagePaths) + 1);

        foreach ($imagePaths as $index => $path) {
            $progress = $index / (count($imagePaths) - 1);
            $x = $progress * ($width - $imageSize);
            $y = $progress * ($height - $imageSize);

            $image = new Imagick($path);
            $image->thumbnailImage((int)$imageSize, (int)$imageSize, true);

            $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);
            $image->clear();
        }
    }

    /**
     * 创建水平拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createHorizontalCollage(array $imagePaths, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $totalWidth = $this->imagick->getImageWidth();
        $height = $this->imagick->getImageHeight();

        $imageWidth = ($totalWidth - ($spacing * (count($imagePaths) - 1))) / count($imagePaths);
        $x = 0;

        foreach ($imagePaths as $path) {
            $image = new Imagick($path);
            $image->thumbnailImage((int)$imageWidth, $height, true);

            $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, (int)$x, 0);
            $image->clear();

            $x += $imageWidth + $spacing;
        }
    }

    /**
     * 创建垂直拼图
     *
     * @param array $imagePaths 图片路径数组
     * @param array $options 选项参数
     * @return void
     * @throws ImagickException
     */
    private function createVerticalCollage(array $imagePaths, array $options): void
    {
        $spacing = $options['spacing'] ?? 10;
        $width = $this->imagick->getImageWidth();
        $totalHeight = $this->imagick->getImageHeight();

        $imageHeight = ($totalHeight - ($spacing * (count($imagePaths) - 1))) / count($imagePaths);
        $y = 0;

        foreach ($imagePaths as $path) {
            $image = new Imagick($path);
            $image->thumbnailImage($width, (int)$imageHeight, true);

            $this->imagick->compositeImage($image, Imagick::COMPOSITE_OVER, 0, (int)$y);
            $image->clear();

            $y += $imageHeight + $spacing;
        }
    }

    /**
     * 应用圆角效果
     *
     * @param Imagick $image 图像对象
     * @param int $radius 圆角半径
     * @return void
     */
    private function applyRoundCorners(Imagick $image, int $radius): void
    {
        try {
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            // 创建圆角蒙版
            $mask = new Imagick();
            $mask->newImage($width, $height, new ImagickPixel('transparent'));

            $draw = new ImagickDraw();
            $draw->setFillColor(new ImagickPixel('white'));
            $draw->roundRectangle(0, 0, $width, $height, $radius, $radius);

            $mask->drawImage($draw);
            $image->setImageMatte(true);
            $image->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

            $mask->clear();
            $draw->destroy();
        } catch (ImagickException $e) {
            // 忽略圆角应用失败的情况
        }
    }

    /**
     * 应用边框效果
     *
     * @param Imagick $image 图像对象
     * @param int $width 边框宽度
     * @param string $color 边框颜色
     * @return void
     */
    private function applyBorder(Imagick $image, int $width, string $color): void
    {
        try {
            $borderColor = $this->createImagickPixel($color);
            $image->borderImage($borderColor, $width, $width);
        } catch (ImagickException $e) {
            // 忽略边框应用失败的情况
        }
    }

    // ==========================================
    // 公共工具方法
    // ==========================================

    /**
     * 清理资源
     *
     * @return void
     */
    private function cleanup(): void
    {
        // 清理临时资源
        foreach ($this->tempResources as $resource) {
            try {
                if ($resource instanceof Imagick) {
                    $resource->clear();
                }
            } catch (ImagickException $e) {
                // 忽略清理异常
            }
        }
        $this->tempResources = [];

        // 重置资源状态
        $this->resourceLoaded = false;
    }

    /**
     * 获取图像信息
     *
     * @return array 图像信息数组
     * @throws ImagickException 当获取信息失败时抛出异常
     */
    public function getImageInfo(): array
    {
        $this->validateResource();

        try {
            return [
                'width' => $this->imagick->getImageWidth(),
                'height' => $this->imagick->getImageHeight(),
                'format' => $this->imagick->getImageFormat(),
                'mime_type' => $this->imagick->getImageMimeType(),
                'file_size' => $this->imagick->getImageLength(),
                'colorspace' => $this->imagick->getImageColorspace(),
                'compression' => $this->imagick->getImageCompression(),
                'resolution' => [
                    'x' => $this->imagick->getImageResolution()['x'] ?? 0,
                    'y' => $this->imagick->getImageResolution()['y'] ?? 0,
                ],
                'has_alpha' => $this->hasAlphaChannel(),
                'depth' => $this->imagick->getImageDepth(),
                'number_images' => $this->imagick->getNumberImages(),
            ];
        } catch (ImagickException $e) {
            throw new ImagickException("获取图像信息失败: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * 获取图像宽度
     *
     * @return int 图像宽度（像素）
     * @throws ImagickException
     */
    public function getWidth(): int
    {
        $this->validateResource();
        return $this->imagick->getImageWidth();
    }

    /**
     * 获取图像高度
     *
     * @return int 图像高度（像素）
     * @throws ImagickException
     */
    public function getHeight(): int
    {
        $this->validateResource();
        return $this->imagick->getImageHeight();
    }

    /**
     * 获取图像格式
     *
     * @return string 图像格式
     * @throws ImagickException
     */
    public function getFormat(): string
    {
        $this->validateResource();
        return $this->imagick->getImageFormat();
    }

    /**
     * 设置图像格式
     *
     * @param string $format 图像格式
     * @return self
     * @throws ImagickException
     */
    public function setFormat(string $format): self
    {
        $this->validateResource();
        $this->imagick->setImageFormat($format);
        return $this;
    }

    /**
     * 重置图像状态
     *
     * @return self 返回当前对象实例，支持链式调用
     */
    public function reset(): self
    {
        $this->cleanup();
        $this->imagick->clear();
        $this->resourceLoaded = false;
        return $this;
    }

    /**
     * 获取 Imagick 实例（用于高级操作）
     *
     * @return Imagick Imagick实例
     */
    public function getImagickInstance(): Imagick
    {
        return $this->imagick;
    }

    /**
     * 检查资源是否已加载
     *
     * @return bool 资源已加载返回true，否则返回false
     */
    public function isResourceLoaded(): bool
    {
        return $this->resourceLoaded;
    }

    /**
     * 获取位置常量映射表
     *
     * @return array 位置常量映射表
     */
    public static function getPositionMap(): array
    {
        return self::$positionMap;
    }

    /**
     * 获取布局常量映射表
     *
     * @return array 布局常量映射表
     */
    public static function getLayoutMap(): array
    {
        return self::$layoutMap;
    }

    /**
     * 获取页面尺寸映射表
     *
     * @return array 页面尺寸映射表
     */
    public static function getPageSizes(): array
    {
        return self::$pageSizes;
    }

    // ==========================================
    // 魔术方法 - 调用原生Imagick方法
    // ==========================================

    /**
     * 魔术方法，调用原生Imagick方法
     *
     * 当调用不存在的方法时，自动转发到原生Imagick实例
     *
     * @param string $method 方法名
     * @param array $arguments 参数数组
     * @return mixed 方法执行结果
     * @throws Exception 当方法不存在时抛出异常
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->imagick, $method)) {
            return $this->imagick->{$method}(...$arguments);
        }

        throw new Exception("方法不存在: {$method}");
    }

    /**
     * 析构函数，自动清理资源
     */
    public function __destruct()
    {
        $this->cleanup();
        if (isset($this->imagick)) {
            try {
                $this->imagick->clear();
            } catch (ImagickException $e) {
                // 忽略析构过程中的异常
            }
        }
    }

    /**
     * 克隆方法，确保深度复制
     */
    public function __clone()
    {
        if (isset($this->imagick)) {
            $this->imagick = clone $this->imagick;
        }
        $this->tempResources = [];
        $this->resourceLoaded = isset($this->imagick);
    }

    /**
     * 序列化方法
     *
     * @return array 序列化数据
     */
    public function __serialize(): array
    {
        return [
            'imagick_blob' => $this->resourceLoaded ? $this->imagick->getImageBlob() : null,
            'resource_loaded' => $this->resourceLoaded,
        ];
    }

    /**
     * 反序列化方法
     *
     * @param array $data 序列化数据
     */
    public function __unserialize(array $data): void
    {
        $this->imagick = new Imagick();
        $this->tempResources = [];

        if (!empty($data['imagick_blob']) && $data['resource_loaded']) {
            try {
                $this->imagick->readImageBlob($data['imagick_blob']);
                $this->resourceLoaded = true;
            } catch (ImagickException $e) {
                $this->resourceLoaded = false;
            }
        } else {
            $this->resourceLoaded = false;
        }
    }
}