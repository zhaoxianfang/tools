<?php

namespace zxf\tools;

// +---------------------------------------------------------------------
// | 图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.weisifang.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

/**
 * 功能：图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
 * 
 * @DateTime 2019-03-08
 *
 * 调用示例：
 *        # 实例化对象
 *        $Compressor = new Compressor();
 *        OR
 *        $Compressor = Compressor::instance();
 *
 *        # 使用原始尺寸 压缩图片大小并输出到浏览器
 *        $result = $Compressor->set('001.jpg')->proportion(1)->get();
 *        # 仅压缩
 *        $result = $Compressor->set('001.jpg')->compress(5)->get();
 *        # 仅改变尺寸并保存到指定位置
 *        $result = $Compressor->set('001.jpg', './resizeOnly.jpg')->resize(500, 500)->get();
 *        # 压缩且改变尺寸并保存到指定位置
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->resize(0, 500)->compress(5)->get();
 *        #  压缩且按照比例压缩
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->proportion(0.5)->compress(5)->get();
 *        # 返回base64图片信息
 *        $result = $Compressor->set($realPath)->proportion($input['proportion'])->get(true);
 *        return $result;
 *  参数说明：
 *        set(原图路径,保存后的路径); // 如果要直接输出到浏览器则只传第一个参数即可
 *        resize(设置宽度,设置高度);//如果有一个参数为0，则保持宽高比例
 *        proportion(压缩比例);//0.1~1 根据比例压缩
 *        compress(压缩级别);//0~9，压缩级别，级别越高就图片越小也就越模糊
 *        get();//获取生成后的结果
 *  提示：
 *        proportion 方法 回去调用 resize 方法，因此他们两个方法只需要选择调用一个即可
 */
class Compressor
{
    protected static $instance;
    /**
     * 可供压缩的类型
     */
    private $setting = [
        'file_type' => [
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
    ];

    /**
     * 图片压缩级别 [0~9]，级别越高 图片越小[图片越小越不清晰,形如马赛克模糊][php 的默认值是75]
     */
    private $level = 7.5;

    /**
     * 图片对象
     */
    private $image;

    /**
     * 压缩之后的存储路径
     */
    private $imageCompressPath;

    /**
     * [
     *      "0": 879,
     *      "1": 623,
     *      "2": 2,
     *      "3": "width=\"879\" height=\"623\"",
     *      "bits": 8,
     *      "channels": 3,
     *      "mime": "image/jpeg"
     *  ]
     */
    private $imageInfo;

    private $res = [
        'code'       => 0,
        'original'   => [
            'name'      => 'fileName',
            'type'      => 'imageType',
            'size'      => 'imageSize',
            'bits'      => 'imageBits',
            'file_path' => 'filePath', // 源图片地址
        ],
        'compressed' => [
            'name'      => 'newName',
            'type'      => 'imageType',
            'bits'      => 'imageBits',
            'size'      => 'imageSize',
            'save_path' => 'savePath',
        ],

    ];

    public function __construct($fileType = false)
    {
        if ($fileType) {
            $this->setting['file_type'] = $fileType;
        }

    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 设置 被压缩图片路径, 压缩之后的存储路径
     *
     * @param $imagePath
     * @param $compressImageName
     * @return $this
     * @throws \Exception
     * 
     */
    public function set($imagePath, $savePath = null)
    {
        try {
            $this->imageInfo = getImageSize($imagePath);
        } catch (\Exception $e) {
            throw new \Exception('不是图片类型');
        }

        $filesize              = filesize($imagePath); // 单位bit
        $this->res['original'] = [
            'name'      => basename($imagePath),
            'type'      => $this->imageInfo['mime'],
            'size'      => $filesize, // 单位bit
            'bits'      => $this->imageInfo['bits'],
            'file_path' => $imagePath, // 源图片地址
        ];

        $this->res['compressed'] = [
            'name'      => basename($imagePath),
            'type'      => $this->imageInfo['mime'],
            'bits'      => $this->imageInfo['bits'],
            'save_path' => $savePath, // 保存地址，可以为NULL
        ];

        if (in_array($this->imageInfo['mime'], $this->setting['file_type'])) {
            $this->image = ('imagecreatefrom' . basename($this->imageInfo['mime']))($imagePath);
            return $this;
        }

        throw new \Exception(__METHOD__);
    }

    /**
     * 压缩级别，级别越高 图片越小[图片越小越不清晰]
     * @param int $level
     * @return ImgCompressor
     * @throws \Exception
     * 
     */
    public function compress($level = 7.5)
    {
        if ($level < 0 || $level > 9) {
            throw new \Exception(__METHOD__ . 'Compression level: [0, 9]');
        }

        $this->level = $level;
        return $this;
    }

    /**
     * 判断图片是否为 透明 图片
     *
     * @param $image
     * @return bool
     */
    private function ifTransparent($image)
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

    /**
     * 等比例缩放
     * 
     * @DateTime 2019-03-08
     * @param string $percent [设置比例 0.1~1][图片压缩比例，0-1 #原图压缩，不缩放，但体积大大降低]
     * @return   [type]                [description]
     */
    public function proportion($percent = '1')
    {
        $width  = $this->imageInfo['0'] * $percent;
        $height = $this->imageInfo['1'] * $percent;
        return $this->resize($width, $height);
    }

    /**
     * 尺寸变更
     *     如果有一个参数为0 ，则保留宽高比例缩放
     * @param $width
     * @param $height
     * @return $this
     * @throws \Exception
     * 
     */
    public function resize($width, $height)
    {
        if ($width == 0 && $height > 0) {
            $width = ($height / $this->imageInfo['1']) * $this->imageInfo['0'];
        } else if ($width > 0 && $height == 0) {
            $height = ($width / $this->imageInfo['0']) * $this->imageInfo['1'];
        } else if ($width <= 0 && $height <= 0) {
            throw new \Exception('illegal size!');
        }
        ini_set('memory_limit','3072M'); // 处理图片过大导致 imagecreatetruecolor 提示空白错误问题
        $image_thump = imagecreatetruecolor($width, $height);
        if ($this->imageInfo['mime'] == 'image/png') {
            //分配颜色 + alpha，将颜色填充到新图上
            $alpha = imagecolorallocatealpha($image_thump, 0, 0, 0, 127);
            imagefill($image_thump, 0, 0, $alpha);
        }
        //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        imagecopyresampled($image_thump, $this->image, 0, 0, 0, 0, $width, $height, $this->imageInfo['0'], $this->imageInfo['1']);
        if ($this->imageInfo['mime'] == 'image/png') {
            imagesavealpha($image_thump, true);
        }
        imagedestroy($this->image);
        $this->image = $image_thump;

        return $this;
    }

    private function printOrSaveImage($toBase64String = false)
    {
        $type     = $this->imageInfo['mime'];
        $savePath = empty($this->res['compressed']['save_path']) ? null : $this->res['compressed']['save_path'];
        if (empty($savePath)) {
            if ($toBase64String) {
                ob_start();
            } else {
                // header("Content-type:image/jpeg");
                header("Content-type:" . $type);
            }
        }

        if ($type == 'image/jpeg') {
            imagejpeg($this->image, $savePath, (100 - ($this->level * 10)));
        } else if ($type == 'image/gif') {
            if ($this->ifTransparent($this->image)) {
                // 保留图片透明状态
                imageAlphaBlending($this->image, true);
                imageSaveAlpha($this->image, true);
                imagegif($this->image, $savePath);
            } else {
                imagegif($this->image, $savePath);
            }
        } else if ($type == 'image/png') {
            if ($this->ifTransparent($this->image)) {
                imageAlphaBlending($this->image, true);
                imageSaveAlpha($this->image, true);
                imagepng($this->image, $savePath, $this->level);
            } else {
                imagepng($this->image, $savePath, $this->level);
            }
        }
        // 销毁图片
        imagedestroy($this->image);

        if (!empty($savePath)) {
            $this->res['compressed']['size'] = filesize($savePath);
            return $this->res;
        } else {
            if ($toBase64String) {
                $imagedata = ob_get_contents();
                // Clear the output buffer
                ob_end_clean();
                // 返回base64 图片
                return 'data:' . $type . ';base64,' . base64_encode($imagedata);
            }
        }
        die;
    }

    /**
     * 获取结果
     *
     * @return 保存的图片信息或者 直接输出到浏览器[由是否保存本地来决定]
     * 
     */
    public function get($toBase64String = false)
    {
        return $this->printOrSaveImage($toBase64String);
    }
}
