<?php

namespace zxf\Tools;

// +---------------------------------------------------------------------
// | 图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------
use Closure;
use Exception;
use GdImage;

/**
 * 功能：图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
 *
 * 调用示例：
 *        # 实例化对象
 *        $compressor = new Compressor();
 *        OR
 *        $compressor = Compressor::instance();
 *
 *        # 使用原始尺寸 仅压缩
 *        $result = $compressor->set('001.jpg')->get();
 *        # 设置压缩率[0~100]; eg:压缩70%
 *        $result = $compressor->set('001.jpg')->compress(70)->get();
 *        # 改变尺寸并保存到指定位置
 *        $result = $compressor->set('001.jpg', './resizeOnly.jpg')->resize(500, 500)->get();
 *        # 压缩且改变尺寸并保存到指定位置
 *        $result = $compressor->set('001.jpg', './resizeAndCompress.png')->resize(0, 500)->compress(70)->get();
 *        #  压缩且按照比例压缩
 *        $result = $compressor->set('001.jpg', './resizeAndCompress.png')->proportion(0.5)->compress(70)->get();
 *        # 在压缩前获取图片信息
 *        $result = $compressor->set($realPath)->proportion($input['proportion'])->get(function($res){ ... });
 *        return $result;
 *  参数说明：
 *        set(原图路径,保存后的路径); // 第二个参数为空表示返回base64，否则表示设置图片保存路径
 *        resize(设置宽度,设置高度);//如果有一个参数为0，则保持宽高比例
 *        proportion(压缩比例);//0.1~1 根据比例压缩
 *        compress(压缩级别);//0~100，压缩级别，级别越高就图片越小也就越模糊
 *        get();//获取生成后的结果
 *  提示：
 *        proportion 方法 回去调用 resize 方法，因此他们两个方法只需要选择调用一个即可
 */
class Compressor
{
    protected static $instance;

    // 原图片信息

    // 原图片和目标图片信息存储
    private array $res = [
        'original' => [
            'name' => '', // 源图片名称
            'type' => '', // 图片类型
            'size' => '', // 图片大小
            'bits' => '', // 图片位数
            'width' => '', // 图片宽度：单位px
            'height' => '', // 图片高度：单位px
            'file_path' => '', // 源图片地址
        ],
        'compressed' => [
            'name' => '',
            'type' => '',
            'bits' => '',
            'size' => '',
            'ratio' => '0%', // 压缩比例
            'save_path' => '',
        ],
    ];

    /**
     * 图片对象
     */
    private GdImage|false|null $image;

    /**
     * @var int 压缩比例（0-100）[值越大，图片质量越差(得到的图片越小)]
     */
    private int $quality = 70;

    /**
     * 初始化
     */
    public static function instance()
    {
        if (! isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * 设置 被压缩图片路径, 压缩之后的存储路径
     *
     * @param  string  $srcPath  被压缩图片路径
     * @param  string|null  $savePath  string:图片存储路径;null:返回base64
     *
     * @throws Exception
     */
    public function set(string $srcPath, ?string $savePath = null)
    {
        try {
            // 获取源图像的 MIME 类型和尺寸信息
            $imageInfo = getimagesize($srcPath);
        } catch (Exception $e) {
            throw new Exception("{$srcPath} 不是图片类型");
        }

        $mimeType = $imageInfo['mime'];

        // 根据 MIME 类型加载图像
        switch ($mimeType) {
            case 'image/png':
                $srcImage = imagecreatefrompng($srcPath);
                break;
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = imagecreatefromjpeg($srcPath);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($srcPath);
                break;
            default:
                throw new Exception('不支持的图像格式');
        }

        if (! $srcImage) {
            throw new Exception('无法加载图像');
        }

        $this->image = $srcImage;

        $filesize = filesize($srcPath); // 单位bit
        $this->res['original'] = [
            'name' => basename($srcPath),
            'type' => $mimeType,
            'size' => byteFormat($filesize),
            'bits' => $filesize,
            'width' => $imageInfo[0], // 图片宽度：单位px
            'height' => $imageInfo[1], // 图片高度：单位px
            'file_path' => $srcPath, // 源图片地址
        ];

        $this->res['compressed'] = [
            'name' => basename($srcPath),
            'type' => $mimeType,
            'bits' => 0,
            'size' => 0,
            'width' => $imageInfo[0], // 目标图片宽度
            'height' => $imageInfo[1], // 目标图片高度
            'save_path' => $savePath, // 保存地址，null表示返回base64
        ];

        return $this;
    }

    /**
     * 尺寸变更
     *     如果有一个参数为0 ，则保留宽高比例缩放
     *
     * @param  int  $width  目标图片宽度
     * @param  int  $height  目标图片高度
     * @return $this
     *
     * @throws Exception
     */
    public function resize(int $width = 0, int $height = 0)
    {
        if ($width == 0 && $height > 0) {
            $width = ($height / $this->res['original']['height']) * $this->res['original']['width'];
        } elseif ($width > 0 && $height == 0) {
            $height = ($width / $this->res['original']['width']) * $this->res['original']['height'];
        } elseif ($width < 0 || $height < 0) {
            throw new Exception('illegal size!');
        }
        ini_set('memory_limit', '3072M'); // 处理图片过大导致 imagecreatetruecolor 提示空白错误问题
        if ($width > 0 && $height > 0) {
            // 设置目标图片的处理尺寸
            $this->res['compressed']['width'] = $width;
            $this->res['compressed']['height'] = $height;
        }

        return $this;
    }

    /**
     * 图片压缩质量
     *
     * @param  int  $quality  压缩质量（0-100）[值越大，图片质量越差(得到的图片越小)]
     *
     * @throws Exception
     */
    public function compress(int $quality = 70)
    {
        if ($quality < 0 || $quality > 100) {
            throw new Exception(__METHOD__.'图片压缩质量: [0, 100]');
        }

        $this->quality = $quality;

        return $this;
    }

    /**
     * 图片尺寸等比例缩放
     *
     * @param  float  $percent  <1:等比例缩小图片；>1:等比例放大图片；=1:保留图片原尺寸
     *
     * @throws Exception
     */
    public function proportion(float $percent = 1)
    {
        if ($percent <= 0) {
            throw new Exception('图片缩放比例必须大于0且小于等于1');
        }
        $width = $this->res['original']['width'] * $percent;
        $height = $this->res['original']['height'] * $percent;

        return $this->resize($width, $height);
    }

    /**
     * 生成目标图片
     * 高级图像压缩函数
     * 支持 PNG、JPEG 和 WebP 格式的图像压缩
     *
     * @param  Closure|null  $beforeFunc  支持在返回目标对象前执行一写操作
     * @return bool|string 返回保存是否成功或base64图片
     *
     * @throws Exception
     */
    public function get(?Closure $beforeFunc = null): bool|string
    {
        if (empty($this->image)) {
            throw new Exception('未设置需要处理的图片');
        }
        try {
            $originalWidth = $this->res['original']['width'];
            $originalHeight = $this->res['original']['height'];
            $newWidth = $this->res['compressed']['width'];
            $newHeight = $this->res['compressed']['height'];
            $mimeType = $this->res['original']['type'];
            $dest = $this->res['compressed']['save_path'];

            $isPng = $mimeType === 'image/png';
            $isJpg = $mimeType === 'image/jpeg' || $mimeType === 'image/jpg';
            $isWebp = $mimeType === 'image/webp';

            // 对于 PNG 图像，创建一个真彩色图像以支持 Alpha 通道
            $trueColorImage = imagecreatetruecolor($newWidth, $newHeight);
            if (! $trueColorImage) {
                throw new Exception('无法创建真彩色图像');
            }
            // 原图是否为透明图
            $srcImgIsTransparent = $isPng && $this->isTransparent($this->image);
            // 将原图复制到新的真彩色图像中，保留透明度和抗锯齿效果
            imagecopyresampled($trueColorImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            imagedestroy($this->image);
            $this->image = $trueColorImage;

            // 如果需要更高的压缩率，可以尝试转换为调色板图像（最大256色）
            // 注意：此步骤可能会导致一些透明度信息丢失，因此需谨慎使用
            imagetruecolortopalette($this->image, false, 256);

            if ($isPng) {
                // 启用透明度支持并保存全透明色
                imagealphablending($this->image, true);
                imagesavealpha($this->image, true);

                // 分配一个完全透明的颜色，并填充新图像的背景
                $transparentColor = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
                imagefill($this->image, 0, 0, $transparentColor);

                // 确保透明色设置正确
                $transparentIndex = imagecolortransparent($this->image);
                if ($transparentIndex >= 0) {
                    // 获取透明色的信息
                    $transparentColor = imagecolorsforindex($this->image, $transparentIndex);
                    // 设置透明色
                    imagecolorset($this->image, $transparentIndex, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                    imagecolortransparent($this->image, $transparentIndex);
                } else {
                    if ($srcImgIsTransparent) {
                        // 如果没有透明色，则尝试找到最接近透明的颜色并设置
                        $transparentColor = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
                        imagecolortransparent($this->image, $transparentColor);
                    }
                }
                // 计算压缩级别（质量转为压缩级别） 压缩级别：从 0（不压缩）到 9。 默认值（-1）使用 zlib 压缩默认值
                $compressionValue = min(9, max(0, round(($this->quality * 0.09))));
            } elseif ($isJpg) {
                // 对于 JPEG 图像，直接调整质量参数
                // 范围从 0（最差质量，文件最小）到 100（最佳质量，文件最大）。默认值（-1）使用 IJG 默认的质量值（大约 75）
                $compressionValue = max(0, min(100, 100 - $this->quality)); // 确保质量在 0-100 之间

                // 添加轻微高斯模糊以减少细节冗余，提高压缩率
                // 注意：模糊程度应适中，避免过度模糊影响质量
                imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);

                // 保存为渐进式 JPEG 以优化加载速度
                imageinterlace($this->image, true);
            } elseif ($isWebp) {
                // 对于 WebP 图像，使用内置的 WebP 压缩
                // 范围从 0（最低质量，最小文件体积）到 100（最好质量, 最大文件体积）
                $compressionValue = max(0, min(100, 100 - $this->quality)); // 确保质量在 0-100 之间
            } else {
                throw new Exception('不支持的图像格式');
            }

            if (empty($dest)) {
                // 返回 Base64 格式的 JPEG 数据
                ob_start();
                $isPng && imagepng($this->image, null, $compressionValue);
                $isJpg && imagejpeg($this->image, null, $compressionValue);
                $isWebp && imagewebp($this->image, null, $compressionValue);
                $imageData = ob_get_clean();
                $this->res['compressed']['bits'] = strlen($imageData); // 字节大小
                $result = 'data:image/jpeg;base64,'.base64_encode($imageData);
            } else {
                // 保存到指定路径
                if ($isPng) {
                    $result = imagepng($this->image, $dest, $compressionValue);
                } elseif ($isJpg) {
                    $result = imagejpeg($this->image, $dest, $compressionValue);
                } elseif ($isWebp) {
                    $result = imagewebp($this->image, $dest, $compressionValue);
                }
                $this->res['compressed']['bits'] = filesize($dest); // 字节大小人性化展示
            }
            $this->res['compressed']['size'] = byteFormat($this->res['compressed']['bits']); // 字节大小人性化展示
            // 压缩比例
            $ratio = bcmul(bcdiv(bcsub($this->res['original']['bits'], $this->res['compressed']['bits'], 4), $this->res['original']['bits'], 4), 100, 2).'%';
            $this->res['compressed']['ratio'] = $ratio; // 压缩比例
            if ($beforeFunc instanceof Closure) {
                $beforeFunc($this->res);
            }

            return $result;
        } finally {
            // 释放资源
            imagedestroy($this->image);
            imagedestroy($trueColorImage);
        }
    }

    /**
     * 判断图片是否为 透明 图片
     */
    private function isTransparent($image): bool
    {
        for ($x = 0; $x < imagesx($image); $x++) {
            for ($y = 0; $y < imagesy($image); $y++) {
                if ((imagecolorat($image, $x, $y) & 0x7F000000) >> 24) {
                    return true;
                }
            }
        }

        return false;
    }
}
