<?php
namespace zxf\img; 

// +---------------------------------------------------------------------
// | 图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.itzxf.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

/**
 * 功能：图片压缩类（可改变图片大小和压缩质量以及保留宽高压缩）
 * @Author   ZhaoXianFang
 * @DateTime 2019-03-08
 *
 * 调用示例：
 *        $Compressor = new Compressor(); 
 *        OR 
 *        $Compressor = Compressor::instance()
 *        # 仅压缩
 *        $result = $Compressor->set('001.jpg', './compressOnly.png')->compress(5)->get();
 *        # 仅改变尺寸
 *        $result = $Compressor->set('001.jpg', './resizeOnly.jpg')->resize(500, 500)->get();
 *        # 压缩且改变尺寸
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->resize(0, 500)->compress(5)->get();
 *        #  压缩且按照比例压缩
 *        $result = $Compressor->set('001.jpg', './resizeAndCompress.png')->proportion(0.5)->compress(5)->get();
 *        return $result;
 *  参数说明：
 *        set(原图路径,保存后的路径);
 *        resize(设置宽度,设置高度);//如果有一个参数为0，则保持宽高比例
 *        proportion(压缩比例);//0.1~1 根据比例压缩
 *        compress(压缩级别);//0~9，压缩级别，级别越高 图片越小
 *        get();//获取生成后的结果
 *  提示：
 *        如果使用到compress 方法，先设置其他参数最后一步再执行 compress 压缩方法
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
     * 被处理的图片原始路径
     */
    private $imagePath;

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
            'name' => 'oldName',
            'type' => 'imageType',
            'size' => 'imageSize',
        ],
        'compressed' => [
            'name' => 'newName',
            'type' => 'imageType',
            'size' => 'imageSize',
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
     * 压缩级别，级别越高 图片越小
     * @param int $level
     * @return ImgCompressor
     * @throws \Exception
     * @author 19/1/17 CLZ.
     */
    public function compress($level = 0)
    {
        if ($level < 0 || $level > 9) {
            throw new \Exception(__METHOD__ . 'Compression level: [0, 9]');
        }

        $compressImageName = $this->imageCompressPath;

        $type = $this->imageInfo['mime'];

        $image = ('imagecreatefrom' . basename($type))($this->imagePath);

        if ($type == 'image/jpeg') {
            imagejpeg($image, $compressImageName, (100 - ($level * 10)));
        } else if ($type == 'image/gif') {
            if ($this->ifTransparent($image)) {
                // 保留图片透明状态
                imageAlphaBlending($image, true);
                imageSaveAlpha($image, true);
                imagegif($image, $compressImageName);
            } else {
                imagegif($image, $compressImageName);
            }

        } else if ($type == 'image/png') {
            if ($this->ifTransparent($image)) {
                imageAlphaBlending($image, true);
                imageSaveAlpha($image, true);
                imagepng($image, $compressImageName, $level);
            } else {
                imagepng($image, $compressImageName, $level);
            }

        }

        // 销毁图片
        imagedestroy($image);

        $this->res['compressed']['size'] = filesize($compressImageName);

        return $this;
    }

    /**
     * 判断图片是否为 透明 图片
     *
     * @param $image
     * @return bool
     * @author 19/1/16 CLZ.
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
     * 设置 被压缩图片路径, 压缩之后的存储路径
     *
     * @param $image
     * @param $compressImageName
     * @return $this
     * @author 19/1/17 CLZ.
     * @throws \Exception
     */
    public function set($image, $compressImageName)
    {
        try {
            $this->imageInfo = getImageSize($image);
        } catch (\Exception $e) {
            throw new \Exception('不是图片类型');
        }

        $this->imagePath         = $image;
        $this->imageCompressPath = $compressImageName;

        $this->res['original'] = [
            'name' => $this->imagePath,
            'type' => $this->imageInfo['mime'],
            'size' => filesize($this->imagePath),
        ];

        $this->res['compressed'] = [
            'name' => $this->imageCompressPath,
            'type' => $this->imageInfo['mime'],
            'size' => '',
        ];

        if (in_array($this->imageInfo['mime'], $this->setting['file_type'])) {
            return $this;
        }

        throw new \Exception(__METHOD__);
    }

    /**
     * 等比例缩放
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-08
     * @param    string       $percent [设置比例 0.1~1]
     * @return   [type]                [description]
     */
    public function proportion($percent = '0.5')
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
     * @author 19/1/17 CLZ.
     * @throws \Exception
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

        $imageSrc = ('imagecreatefrom' . basename($this->imageInfo['mime']))($this->imagePath);

        $image = imagecreatetruecolor($width, $height); //创建一个彩色的底图
        imagecopyresampled($image, $imageSrc, 0, 0, 0, 0, $width, $height, $this->imageInfo[0], $this->imageInfo[1]);

        ('image' . basename($this->imageInfo['mime']))($image, $this->imageCompressPath);

        $this->imagePath = $this->imageCompressPath;

        $this->res['compressed']['size'] = filesize($this->imageCompressPath);
        // 销毁图片
        imagedestroy($image);
        imagedestroy($imageSrc);

        return $this;
    }

    /**
     * 获取结果
     *
     * @return array
     * @author 19/1/17 CLZ.
     */
    public function get()
    {
        return $this->res;
    }
}
