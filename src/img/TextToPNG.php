<?php
namespace zxf\img; 

// +---------------------------------------------------------------------
// | 文字转图片类
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
 * 文字转图片类
 * 使用:
 *     TextToPNG::instance()->setFontStyle($font='lishu')->setText($text)->setSize('900', '500')->setColor($color)->setBackgroundColor($bgcolor)->setTransparent(false)->setRotate($rotate)->draw();
 */
Header("Content-type: image/png");
class TextToPNG
{

    protected static $instance; // object 对象实例

    private $font        = './../resource/font/lishu.ttf'; //默认字体. 相对于脚本存放目录的相对路径.
    private $text        = "undefined"; // 默认文字.
    private $size        = 24; //默认字体大小，会自动适配
    private $rot         = 0; // 旋转角度.
    private $transparent = false; // 文字是否设置为透明.
    private $red         = '0xFF'; // 16进制 白色字体
    private $grn         = '0xFF';
    private $blu         = '0xFF';
    private $bg_red      = "0x00"; //16进制 蓝色背景
    private $bg_grn      = "0x00";
    private $bg_blu      = '0xFF';
    private $imgWidth    = '900'; //生成图片宽
    private $imgHeight   = '500'; //生成图片高

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 设置文本
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    string       $text [description]
     */
    public function setText($text = 'undefined')
    {
        $this->text = $text;
        return $this;

    }

    /**
     * 进行绘画
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @return   [type]       [description]
     */
    public function draw()
    {

        $width    = 0;
        $height   = 0;
        $offset_x = 0;
        $offset_y = 0;
        $bounds   = array();
        $image    = "";

        // 确定文字高度.
        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, "W");
        if ($this->rot < 0) {
            $font_height = abs($bounds[7] - $bounds[1]);
        } else if ($this->rot > 0) {
            $font_height = abs($bounds[1] - $bounds[7]);
        } else {
            $font_height = abs($bounds[7] - $bounds[1]);
        }
        $textLen    = mb_strlen($this->text); //字数
        $font_width = abs($bounds[4] - $bounds[6]); //字宽
        if ($this->imgWidth > $font_width) {
            $this->size = floor(($this->imgWidth * 0.8 - $font_width) / $textLen / 1.15);
            $this->size = $this->size > $this->imgHeight ? floor($this->imgHeight * 0.8) : $this->size;
        }
        if ($this->imgHeight > $font_height) {
            $this->size = $this->size  * 0.8;
        }
        if($textLen < 3){
            $this->size = $this->size  * 1.5;
        }

        // 确定边框高度.
        $bounds = ImageTTFBBox($this->size, $this->rot, $this->font, $this->text);
        if ($this->rot < 0) {
            $width    = abs($bounds[4] - $bounds[0]);
            $height   = abs($bounds[3] - $bounds[7]);
            $offset_x = 0;

        } else if ($this->rot > 0) {
            $width    = abs($bounds[2] - $bounds[6]);
            $height   = abs($bounds[1] - $bounds[5]);
            $offset_x = abs($bounds[0] - $bounds[6]);

        } else {
            $width    = abs($bounds[4] - $bounds[6]);
            $height   = abs($bounds[7] - $bounds[1]);
            $offset_x = 0;
        }

        // -30 是用于y坐标误差值
        $offset_y = ($this->imgHeight - $height) / 2 + $height * 1.1 - ($textLen < 4 ? 25 : 15) ;

        $image = imagecreate($this->imgWidth, $this->imgHeight);

        $background = ImageColorAllocate($image, hexdec($this->bg_red), hexdec($this->bg_grn), hexdec($this->bg_blu));
        $foreground = ImageColorAllocate($image, hexdec($this->red), hexdec($this->grn), hexdec($this->blu));

        if ($this->transparent) {
            ImageColorTransparent($image, $background);
        }

        ImageInterlace($image, false);
        $ttx = ($this->imgWidth - $width * 1.1) / 2;

        // 画图 
        ImageTTFText($image, $this->size, $this->rot, $offset_x + $ttx, $offset_y, $foreground, $this->font, $this->text);

        // 输出为png格式.
        imagePNG($image);
        die;
    }
    /**
     * 设置文字颜色
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    string       $hexVal [16进制背景色]
     */
    public function setColor($hexVal = 'FFFFFF')
    {
        $len = strlen($hexVal);
        switch ($len) {
            case '6':
                $this->red = '0x' . substr($hexVal, 0, 2); //截取两位
                $this->grn = '0x' . substr($hexVal, 2, 2); //截取两位
                $this->blu = '0x' . substr($hexVal, 4, 2); //截取两位
                break;
            case '3':
                $this->red = '0x' . substr($hexVal, 0, 1) . substr($hexVal, 0, 1); //截取一位
                $this->grn = '0x' . substr($hexVal, 1, 1) . substr($hexVal, 1, 1); //截取一位
                $this->blu = '0x' . substr($hexVal, 2, 1) . substr($hexVal, 2, 1); //截取一位
                break;
            case '1':
                $this->red = '0x' . $hexVal . $hexVal;
                $this->grn = '0x' . $hexVal . $hexVal;
                $this->blu = '0x' . $hexVal . $hexVal;
                break;
            default:
                # code...
                break;
        }
        return $this;
    }

    /**
     * 设置背景色
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    string       $hexVal [16进制背景色]
     */
    public function setBackgroundColor($hexVal = '0000FF')
    {
        $len = strlen($hexVal);
        switch ($len) {
            case '6':
                $this->bg_red = '0x' . substr($hexVal, 0, 2); //截取两位
                $this->bg_grn = '0x' . substr($hexVal, 2, 2); //截取两位
                $this->bg_blu = '0x' . substr($hexVal, 4, 2); //截取两位
                break;
            case '3':
                $this->bg_red = '0x' . substr($hexVal, 0, 1) . substr($hexVal, 0, 1); //截取一位
                $this->bg_grn = '0x' . substr($hexVal, 1, 1) . substr($hexVal, 1, 1); //截取一位
                $this->bg_blu = '0x' . substr($hexVal, 2, 1) . substr($hexVal, 2, 1); //截取一位
                break;
            case '1':
                $this->bg_red = '0x' . $hexVal . $hexVal;
                $this->bg_grn = '0x' . $hexVal . $hexVal;
                $this->bg_blu = '0x' . $hexVal . $hexVal;
                break;
            default:
                # code...
                break;
        }
        return $this;
    }

    /**
     * 设置画布大小
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    string       $width  [description]
     * @param    string       $height [description]
     */
    public function setSize($width = '900', $height = '500')
    {
        $this->imgWidth  = (int) $width;
        $this->imgHeight = (int) $height;

        return $this;
    }

    /**
     * 设置字体
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    string       $filepath [description]
     */
    public function setFontPath($filepath = '')
    {
        $this->font = $filepath;
        return $this;
    }

    public function setFontStyle($style = 'xingshu')
    {
        $this->font = dirname(dirname(__FILE__)) .'/resource/font/'.$style.'.ttf';
        return $this;
    }
    

    /**
     * 设置图片是否透明
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    boolean      $val [description]
     */
    public function setTransparent($val = false)
    {
        $this->transparent = (bool) $val;
        return $this;
    }
    /**
     * 设置旋转角度
     * @Author   ZhaoXianFang
     * @DateTime 2019-03-20
     * @param    integer      $rotate [description]
     */
    public function setRotate($rotate = 0)
    {
        $this->rot = (int) $rotate;
        return $this;
    }

}
