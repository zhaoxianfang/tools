<?php

namespace zxf\Tools;

// +---------------------------------------------------------------------
// | 图片转ico格式
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://http://www.0l0.net
// +---------------------------------------------------------------------
// | Date       | 2022-02-25
// +---------------------------------------------------------------------
use Exception;

/**
 * 图片转ico格式
 * 使用:
 *     $imgurl = "./test.jpeg";
 *     // 下载到浏览器
 *     ImgToIco::instance()->set($imgurl, 32)->generate();
 *     // 保存到指定文件夹
 *     ImgToIco::instance()->set($imgurl, 32)->generate('E:/www');
 */
class ImgToIco
{
    protected static $instance;

    private $resizeIm;

    /**
     * 初始化
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 将PNG、JPEG或GIF图像转换为指定大小的ICO文件。
     *
     * @param string $sourceImage 源图像路径
     * @param int    $size        ICO图标像素大小（例如：16, 32, 64, 128等）
     *
     * @throws Exception 如果源图像不存在或不受支持。
     */
    public function set(string $sourceImage = '', int $size = 16)
    {
        // 检查源图像文件是否存在
        if (!file_exists($sourceImage)) {
            throw new Exception("源图像文件不存在：{$sourceImage}");
        }

        // 获取源图像类型
        $imageType = exif_imagetype($sourceImage);
        if ($imageType === false) {
            throw new Exception("不支持的图像类型：{$sourceImage}");
        }

        // 创建GD图像资源
        $imageResource = null;
        switch ($imageType) {
            case IMAGETYPE_PNG:
                $imageResource = imagecreatefrompng($sourceImage);
                break;
            case IMAGETYPE_JPEG:
                $imageResource = imagecreatefromjpeg($sourceImage);
                break;
            case IMAGETYPE_GIF:
                $imageResource = imagecreatefromgif($sourceImage);
                break;
            default:
                throw new Exception("不支持的图像类型：{$sourceImage}");
        }

        // 创建一个临时图像资源来缩放图像
        $tempResource = imagecreatetruecolor($size, $size);

        // 将源图像缩放到临时图像资源中
        imagecopyresampled($tempResource, $imageResource, 0, 0, 0, 0, $size, $size, imagesx($imageResource), imagesy($imageResource));

        // 释放源图像资源内存
        imagedestroy($imageResource);

        // 创建ICO图像资源
        $icoResource = imagecreatetruecolor($size, $size);

        // 如果是PNG图像，则保留透明度
        if ($imageType === IMAGETYPE_PNG) {
            // 保存透明度信息
            imagesavealpha($icoResource, true);
            // 分配一个完全透明的颜色
            $transparentColor = imagecolorallocatealpha($icoResource, 0, 0, 0, 127);
            // 用透明颜色填充整个图像
            imagefill($icoResource, 0, 0, $transparentColor);
            // 禁用混色模式以保留透明度
            imagealphablending($icoResource, false);
        }

        // 将临时图像复制到ICO图像资源中
        imagecopy($icoResource, $tempResource, 0, 0, 0, 0, $size, $size);

        $this->resizeIm = $icoResource;
        // 释放临时图像资源内存
        imagedestroy($tempResource);

        return $this;

    }

    /**
     * 处理 生成的ico图片
     *
     * @param bool|string $savePath    如果需要保存到指定文件夹就填写保存路径，默认直接下载到浏览器
     * @param int         $permissions 文件夹权限
     */
    public function generate(bool|string $savePath = false, int $permissions = 0755)
    {
        if ($savePath) {
            // 将图像保存到文件
            create_dir($savePath, $permissions);
            $path = $savePath . DIRECTORY_SEPARATOR . date("Ymdhis") . rand(1, 1000) . "_favicon.ico";
            imagepng($this->resizeIm, $path, 9);
            // 释放ICO图像资源内存
            imagedestroy($this->resizeIm);
            return $savePath;
        } else {
            // // 下载到浏览器
            // header('Content-Type: image/x-icon');
            // imagepng($this->resizeIm);
            // // 释放ICO图像资源内存
            // imagedestroy($this->resizeIm);
            // exit;

            // 返回base64
            // 将缩放后的图像转换为PNG格式的字符串
            ob_start();
            imagepng($this->resizeIm);
            $pngString = ob_get_clean();

            // 释放临时图像资源内存
            imagedestroy($this->resizeIm);

            // 返回Base64编码的PNG图像字符串
            return 'data:image/png;base64,' . base64_encode($pngString);
        }
    }

    /**
     * 下载文件到浏览器
     *
     * @param string $filename 文件路径
     * @param array  $title    输出的文件名
     *
     * @return void
     */
    private function output_for_download($filename, $title)
    {
        $file = fopen($filename, "rb");
        Header("Content-type:  application/octet-stream ");
        Header("Accept-Ranges:  bytes ");
        Header("Content-Disposition:  attachment;  filename= $title");
        while (!feof($file)) {
            echo fread($file, 8192);
            ob_flush();
            flush();
        }
        fclose($file);
        unlink($filename);
        exit;
    }
}

