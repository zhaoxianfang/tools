<?php

namespace zxf\tools;

// +---------------------------------------------------------------------
// | 图片转ico格式
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2022-02-25
// +---------------------------------------------------------------------

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

    private $fileType = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    /**
     * 图片大小
     * 例如 32 * 32 大小
     */
    private $icoSize = [
        16, 32, 64, 128,
    ];

    /**
     * [
     *      "0": 879,
     *      "1": 623,
     *      "2": 2,
     *      "3": "width=\"879\" height=\"623\"",
     *      "bits": 8,
     *      "channels": 3,
     *      "mime": "image/jpeg"
     *      "path": "xxx/jpeg"
     *      "size": "xxx/jpeg" // 单位bit
     *  ]
     */
    private $imageInfo;
    private $resizeIm;

    public function __construct()
    {
    }

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
     * @param $imagePath 图片地址
     * @param $icoSize 生成图片大小 [16,32,64,128]
     * @return $this
     * @throws \Exception
     */
    public function set($imagePath = '', $icoSize = 32)
    {
        try {
            $this->imageInfo         = getImageSize($imagePath);
            $this->imageInfo['path'] = $imagePath;
            $this->imageInfo['size'] = filesize($imagePath); // 单位bit
            if ($this->imageInfo['size'] > 204800) {
                throw new \Exception(__METHOD__ . '你上传的文件过大，最大不能超过200KB');
            }
        } catch (\Exception $e) {
            throw new \Exception(__METHOD__ . '不是图片类型');
        }
        if (!in_array($this->imageInfo['mime'], $this->fileType)) {
            throw new \Exception(__METHOD__ . '不支持的文件格式');
        }
        switch ($this->imageInfo['mime']) {
            case 'image/jpeg':
                $im = imagecreatefromjpeg($this->imageInfo['path']);
                break;
            case 'image/png':
                $im = imagecreatefrompng($this->imageInfo['path']);
                break;
            case 'image/gif':
                $im = imagecreatefromgif($this->imageInfo['path']);
                break;
            default:
                ;
        }
        $size      = in_array($icoSize, $this->icoSize) ? $icoSize : 32;
        $resize_im = imagecreatetruecolor($size, $size);

        $bg = imagecolorallocatealpha($resize_im, 0, 0, 0, 127);//拾取一个完全透明的颜色，不要用imagecolorallocate拾色
        imagefill($resize_im, 0, 0, $bg);//填充
        imagecopyresampled($resize_im, $im, 0, 0, 0, 0, $size, $size, $this->imageInfo[0], $this->imageInfo[1]);

        $this->resizeIm = $resize_im;
        return $this;

    }

    /**
     * 开始 生成ico图片
     * @param $savePath 如果需要保存到指定文件夹就填写保存路径，默认直接下载到浏览器
     * @return false|string|void
     */
    public function generate($savePath = false)
    {
        $gd_image_array = array($this->resizeIm);
        $icon_data      = $this->GD2ICOstring($gd_image_array);
        $savePath       = $savePath ? ($savePath . DIRECTORY_SEPARATOR . date("Ymdhis") . rand(1, 1000) . "_favicon.ico") : false;
        if ($savePath) {
            // 保存到指定文件夹
            return file_put_contents($savePath, $icon_data) ? $savePath : false;
        } else {
            // 新建一个临时文件
            $temp_file = tempnam(sys_get_temp_dir(), 'ICO_');
            if (file_put_contents($temp_file, $icon_data)) {
                // 下载到浏览器
                $this->output_for_download($temp_file, 'favicon.ico');
            }
        }
    }

    /**
     * 下载文件到浏览器
     *
     * @param string $filename 文件路径
     * @param array $title 输出的文件名
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

    private function GD2ICOstring(&$gd_image_array)
    {
        foreach ($gd_image_array as $key => $gd_image) {
            $icANDmask          = [];
            $icANDmask[$key]    = [];
            $ImageWidths[$key]  = ImageSX($gd_image);
            $ImageHeights[$key] = ImageSY($gd_image);
            $bpp[$key]          = ImageIsTrueColor($gd_image) ? 32 : 24;
            $totalcolors[$key]  = ImageColorsTotal($gd_image);

            $icXOR[$key] = '';
            for ($y = $ImageHeights[$key] - 1; $y >= 0; $y--) {
                $icANDmask[$key][$y] = '';
                for ($x = 0; $x < $ImageWidths[$key]; $x++) {
                    $argb = $this->GetPixelColor($gd_image, $x, $y);
                    $a    = round(255 * ((127 - $argb['alpha']) / 127));
                    $r    = $argb['red'];
                    $g    = $argb['green'];
                    $b    = $argb['blue'];

                    if ($bpp[$key] == 32) {
                        $icXOR[$key] .= chr($b) . chr($g) . chr($r) . chr($a);
                    } elseif ($bpp[$key] == 24) {
                        $icXOR[$key] .= chr($b) . chr($g) . chr($r);
                    }

                    if ($a < 128) {
                        $icANDmask[$key][$y] .= '1';
                    } else {
                        $icANDmask[$key][$y] .= '0';
                    }
                }
                // mask bits are 32-bit aligned per scanline
                while (strlen($icANDmask[$key][$y]) % 32) {
                    $icANDmask[$key][$y] .= '0';
                }
            }
            $icAND[$key] = '';
            foreach ($icANDmask[$key] as $y => $scanlinemaskbits) {
                for ($i = 0; $i < strlen($scanlinemaskbits); $i += 8) {
                    $icAND[$key] .= chr(bindec(str_pad(substr($scanlinemaskbits, $i, 8), 8, '0', STR_PAD_LEFT)));
                }
            }
        }

        foreach ($gd_image_array as $key => $gd_image) {
            $biSizeImage = $ImageWidths[$key] * $ImageHeights[$key] * ($bpp[$key] / 8);

            // BITMAPINFOHEADER - 40 bytes
            $BitmapInfoHeader[$key] = '';
            $BitmapInfoHeader[$key] .= "\x28\x00\x00\x00";                              // DWORD  biSize;
            $BitmapInfoHeader[$key] .= $this->LittleEndian2String($ImageWidths[$key], 4);      // LONG   biWidth;
            // The biHeight member specifies the combined
            // height of the XOR and AND masks.
            $BitmapInfoHeader[$key] .= $this->LittleEndian2String($ImageHeights[$key] * 2, 4); // LONG   biHeight;
            $BitmapInfoHeader[$key] .= "\x01\x00";                                      // WORD   biPlanes;
            $BitmapInfoHeader[$key] .= chr($bpp[$key]) . "\x00";                          // wBitCount;
            $BitmapInfoHeader[$key] .= "\x00\x00\x00\x00";                              // DWORD  biCompression;
            $BitmapInfoHeader[$key] .= $this->LittleEndian2String($biSizeImage, 4);            // DWORD  biSizeImage;
            $BitmapInfoHeader[$key] .= "\x00\x00\x00\x00";                              // LONG   biXPelsPerMeter;
            $BitmapInfoHeader[$key] .= "\x00\x00\x00\x00";                              // LONG   biYPelsPerMeter;
            $BitmapInfoHeader[$key] .= "\x00\x00\x00\x00";                              // DWORD  biClrUsed;
            $BitmapInfoHeader[$key] .= "\x00\x00\x00\x00";                              // DWORD  biClrImportant;
        }

        $icondata = "\x00\x00";                                      // idReserved;   // Reserved (must be 0)
        $icondata .= "\x01\x00";                                      // idType;       // Resource Type (1 for icons)
        $icondata .= $this->LittleEndian2String(count($gd_image_array), 2);  // idCount;      // How many images?

        $dwImageOffset = 6 + (count($gd_image_array) * 16);
        foreach ($gd_image_array as $key => $gd_image) {
            // ICONDIRENTRY   idEntries[1]; // An entry for each image (idCount of 'em)

            $icondata .= chr($ImageWidths[$key]);                     // bWidth;          // Width, in pixels, of the image
            $icondata .= chr($ImageHeights[$key]);                    // bHeight;         // Height, in pixels, of the image
            $icondata .= chr($totalcolors[$key]);                     // bColorCount;     // Number of colors in image (0 if >=8bpp)
            $icondata .= "\x00";                                      // bReserved;       // Reserved ( must be 0)
            $icondata .= "\x01\x00";                                  // wPlanes;         // Color Planes
            $icondata .= chr($bpp[$key]) . "\x00";                    // wBitCount;       // Bits per pixel

            $dwBytesInRes  = 40 + strlen($icXOR[$key]) + strlen($icAND[$key]);
            $icondata      .= $this->LittleEndian2String($dwBytesInRes, 4);       // dwBytesInRes;    // How many bytes in this resource?
            $icondata      .= $this->LittleEndian2String($dwImageOffset, 4);      // dwImageOffset;   // Where in the file is this image?
            $dwImageOffset += strlen($BitmapInfoHeader[$key]);
            $dwImageOffset += strlen($icXOR[$key]);
            $dwImageOffset += strlen($icAND[$key]);
        }

        foreach ($gd_image_array as $key => $gd_image) {
            $icondata .= $BitmapInfoHeader[$key];
            $icondata .= $icXOR[$key];
            $icondata .= $icAND[$key];
        }

        return $icondata;
    }

    private function LittleEndian2String($number, $minbytes = 1)
    {
        $intstring = '';
        while ($number > 0) {
            $intstring = $intstring . chr($number & 255);
            $number    >>= 8;
        }
        return str_pad($intstring, $minbytes, "\x00", STR_PAD_RIGHT);
    }

    private function GetPixelColor(&$img, $x, $y)
    {
        if (!is_resource($img)) {
            return false;
        }
        return ImageColorsForIndex($img, ImageColorAt($img, $x, $y));
    }
}

