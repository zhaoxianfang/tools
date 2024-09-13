<?php

namespace zxf\Tools;

// +---------------------------------------------------------------------
// | 图片转ico格式
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
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
        if (!isset(self::$instance) || is_null(self::$instance)) {
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
        // 创建一个空的ICO图片
        $ico = imagecreatetruecolor($size, $size);

        // 开启alpha通道以支持透明度
        imagesavealpha($ico, true);
        $trans_color = imagecolorallocatealpha($ico, 0, 0, 0, 127);
        imagefill($ico, 0, 0, $trans_color);

        // 获取输入图片信息
        $imageInfo = getimagesize($sourceImage);
        $width     = $imageInfo[0];
        $height    = $imageInfo[1];

        // 读取输入图片并复制到ICO图片中
        $inputImage = imagecreatefromstring(file_get_contents($sourceImage));
        imagecopyresampled($ico, $inputImage, 0, 0, 0, 0, $size, $size, $width, $height);

        // 保存ICO图片
        $this->resizeIm = $ico;
        // imagepng($ico, $outputIcoPath);

        // 释放内存
        imagedestroy($ico);
        imagedestroy($inputImage);

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
            // header('Content-Type: image/png');
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

