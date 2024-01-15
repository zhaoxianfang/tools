<?php

namespace zxf\Tools;

// +---------------------------------------------------------------------
// | 文字转图片类
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://http://www.0l0.net
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

/**
 * 文字转图片类[会自定计算文字字号大小]
 * 使用:
 *     TextToPNG::instance()->setFontStyle($font='lishu')->setText($text)->setSize('900', '500')->setColor($color)->setBackgroundColor($bgcolor)->setTransparent(false)->setRotate($rotate)->draw();
 */
class TextToPNG
{

    protected static $instance; // object 对象实例

    private static $calculateNum = 0; //自动计算文字次数

    private $font           = './../resource/font/pmzdxx.ttf'; //默认字体. 相对于脚本存放目录的相对路径.
    private $text           = "Hello!"; // 默认文字.
    private $size           = 24; //默认字体大小，会自动适配
    private $rot            = 0; // 旋转角度.
    private $transparent    = false; // 文字是否设置为透明.
    private $red            = '0xFF'; // 16进制 白色字体
    private $grn            = '0xFF';
    private $blu            = '0xFF';
    private $bg_red         = "0x00"; //16进制 蓝色背景
    private $bg_grn         = "0x00";
    private $bg_blu         = '0xFF';
    private $imgWidth       = '900'; //生成图片宽
    private $imgHeight      = '500'; //生成图片高
    private $imgWidthUsage  = 0.9; //生成图片宽度使用率，例如0.9表示 使用90%，留空10%
    private $imgHeightUsage = 0.9; //生成图片高度使用率，例如0.9表示 使用90%，留空10%
    private $isAllowWrap    = true; //是否允许文字自动换行

    /**
     * 初始化
     *
     * @param array $options 参数
     */
    public static function instance(array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 设置文本
     *
     * 
     * @DateTime 2019-03-20
     *
     * @param string $text [description]
     */
    public function setText(string $text = 'Hello!')
    {
        $this->text = $text;
        return $this;
    }

    /**
     * 是否自动设置换行
     * @param $val
     * @return $this
     */
    public function allowWrap($val = true)
    {
        $this->isAllowWrap = (bool)$val;
        return $this;
    }

    /**
     * 进行绘画
     * 
     * @DateTime 2019-03-20
     * @return   [type]       [description]
     */
    public function draw()
    {
        self::$calculateNum = 0;

        // 自动调整文字宽度和字体大小
        $this->autowrap($this->isAllowWrap);

        // 确定边框高度.
        $bounds = imagettfbbox($this->size, $this->rot, $this->font, $this->text);
        $minX = min($bounds[0], $bounds[2], $bounds[4], $bounds[6]);
        $maxX = max($bounds[0], $bounds[2], $bounds[4], $bounds[6]);
        $minY = min($bounds[1], $bounds[3], $bounds[5], $bounds[7]);
        $maxY = max($bounds[1], $bounds[3], $bounds[5], $bounds[7]);
        $textWidth = bcsub($maxX, $minX);
        $textHeight = bcsub($maxY, $minY);
        // 居中显示
        $offset_x = ($this->imgWidth - $textWidth) / 2 + abs($minX);
        $offset_y = ($this->imgHeight - $textHeight) / 2 + abs($minY);

        $image = imagecreate($this->imgWidth, $this->imgHeight);
        $background = imagecolorallocate($image, hexdec($this->bg_red), hexdec($this->bg_grn), hexdec($this->bg_blu));
        $foreground = imagecolorallocate($image, hexdec($this->red), hexdec($this->grn), hexdec($this->blu));

        if ($this->transparent) {
            imagecolortransparent($image, $background);
        }

        imageinterlace($image, false);

        // 画图
        imagettftext($image, $this->size, $this->rot, $offset_x, $offset_y, $foreground, $this->font, $this->text);

        Header("Content-type: image/png");
        // 输出为png格式.
        imagepng($image);
        die;
    }

    // 自动调整文字宽度和字体大小
    public function autowrap($init = true)
    {
        if (self::$calculateNum > 8) {
            return false;
        }
        self::$calculateNum++;

        $rot = $init ? 0 : $this->rot; // 旋转角度

        $imgUseWidth = $this->imgWidth * $this->imgWidthUsage;
        $imgUseHeight = $this->imgHeight * $this->imgHeightUsage;

        // 将字符串拆分成一个个单字 保存到数组 letter 中
        for ($i = 0; $i < mb_strlen($this->text); $i++) {
            $letter[] = mb_substr($this->text, $i, 1, 'utf-8');
        }

        $content = "";
        $fontIsTooHeight = false;
        for ($i = 0; $i < count($letter); $i++) {
            // 换行处理
            if ($letter[$i] == "/" && $letter[$i + 1] == "n") {
                $content .= PHP_EOL;// 换行
                $letter[$i + 1] = '';
                continue;
            }

            $teststr = $content . " " . $letter[$i];
            // 测量宽度和高度
            $testbox = imagettfbbox($this->size, $rot, $this->font, $teststr);
            $minX = min($testbox[0], $testbox[2], $testbox[4], $testbox[6]);
            $maxX = max($testbox[0], $testbox[2], $testbox[4], $testbox[6]);
            $minY = min($testbox[1], $testbox[3], $testbox[5], $testbox[7]);
            $maxY = max($testbox[1], $testbox[3], $testbox[5], $testbox[7]);
            $testWidth = bcsub($maxX, $minX);
            $testHeight = bcsub($maxY, $minY);
            // 宽度最大显示90%
            if ($init && ($testWidth > $imgUseWidth) && ($content !== "")) {
                $content .= PHP_EOL;// 换行
            }
            if ($testHeight > $imgUseHeight) {
                // 文字太高了
                $fontIsTooHeight = true;
                break;
            }

            $content .= $letter[$i];
        }

        if ($fontIsTooHeight) {
            $this->size = floor(bcmul($this->size, bcdiv($imgUseHeight, $testHeight, 2)));
            return $this->autowrap($init);
        }

        if (!$this->isAllowWrap && ($testWidth > $imgUseWidth * 0.7)) {
            $this->size = floor(bcmul($this->size, bcdiv($imgUseWidth, $testWidth, 2)));

            return $this->autowrap($init);
        }
        if (!$this->isAllowWrap && ($testWidth < $imgUseWidth * 0.7)) {
            $this->size = $this->size + 2;
            return $this->autowrap($init);
        }

        if ($this->isAllowWrap && ($testHeight < floor($imgUseHeight * 0.45))) {
            $this->size = bcadd($this->size, 3);
            return $this->autowrap($init);
        }

        // 文字面积
        $txtArea = bcmul($testWidth, $testHeight);
        // 图片面积
        $imgArea = floor(bcmul($imgUseWidth, $imgUseHeight));
        // 文字空白占比
        $emptyRatio = bcsub(1, bcdiv($txtArea, $imgArea, 3), 1);
        // 允许 0.4 ~ 0.8 的空白
        $setRatio = (($this->rot > 30 && $this->rot < 150) || ($this->rot > 210 && $this->rot < 330)) ? 0.8 : 0.4;
        if (!$this->isAllowWrap && ($emptyRatio > $setRatio)) {
            // 字小了
            $this->size = bcadd($this->size, $emptyRatio * 5);
            return $this->autowrap($init);
        }
        if ($emptyRatio < 0.1) {
            // 字大了
            $this->size = bcsub($this->size, abs($emptyRatio * 10)) - 1; // 防止 0%
            return $this->autowrap($init);
        }

        $this->text = $content;
        if ($init === true) {
            return $this->autowrap(false);
        }
        return true;
    }

    /**
     * 设置文字颜色
     * 
     * @DateTime 2019-03-20
     * @param string $hexVal [16进制背景色]
     */
    public function setColor($hexVal = 'FFFFFF')
    {
        $this->transformColor($hexVal, false);
        return $this;
    }

    /**
     * 设置背景色
     * 
     * @DateTime 2019-03-20
     * @param string $hexVal [16进制背景色]
     */
    public function setBackgroundColor($hexVal = '0000FF')
    {
        $this->transformColor($hexVal, true);
        return $this;
    }

    /**
     * 颜色值转换
     * @param string $hexVal 颜色值（支持1位，3位，6位）
     * @param $isBackground
     * @return $this
     * @throws \Exception
     */
    private function transformColor($hexVal = '0000FF', $isBackground = true)
    {
        $len = strlen($hexVal);
        $red = $isBackground ? 'bg_red' : 'red';
        $grn = $isBackground ? 'bg_grn' : 'grn';
        $blu = $isBackground ? 'bg_blu' : 'blu';
        switch ($len) {
            case '6':
                $this->$red = '0x' . substr($hexVal, 0, 2); //截取两位
                $this->$grn = '0x' . substr($hexVal, 2, 2); //截取两位
                $this->$blu = '0x' . substr($hexVal, 4, 2); //截取两位
                break;
            case '3':
                $this->$red = '0x' . substr($hexVal, 0, 1) . substr($hexVal, 0, 1); //截取一位
                $this->$grn = '0x' . substr($hexVal, 1, 1) . substr($hexVal, 1, 1); //截取一位
                $this->$blu = '0x' . substr($hexVal, 2, 1) . substr($hexVal, 2, 1); //截取一位
                break;
            case '1':
                $this->$red = '0x' . $hexVal . $hexVal;
                $this->$grn = '0x' . $hexVal . $hexVal;
                $this->$blu = '0x' . $hexVal . $hexVal;
                break;
            default:
                throw new \Exception(($isBackground ? '背景' : '前景') . '色有误');
                break;
        }
        return $this;
    }

    /**
     * 设置画布大小
     * 
     * @DateTime 2019-03-20
     * @param string $width [description]
     * @param string $height [description]
     */
    public function setSize($width = '900', $height = '500')
    {
        $this->imgWidth = abs((int)$width);
        $this->imgHeight = abs((int)$height);

        return $this;
    }

    /**
     * 设置字体
     * 
     * @DateTime 2019-03-20
     * @param string $filepath [description]
     */
    public function setFontPath($filepath = '')
    {
        $this->font = $filepath;
        if (!is_file($this->font)) {
            throw new \Exception('字体文件不存在:' . $filepath);
        }
        return $this;
    }

    public function setFontStyle($style = 'pmzdxx')
    {
        $this->font = dirname(dirname(__FILE__)) . '/resource/font/' . $style . '.ttf';
        if (!is_file($this->font)) {
            throw new \Exception('不支持的字体:' . $style);
        }
        return $this;
    }


    /**
     * 设置图片是否透明
     * 
     * @DateTime 2019-03-20
     * @param boolean $val [description]
     */
    public function setTransparent($val = false)
    {
        $this->transparent = (bool)$val;
        return $this;
    }

    /**
     * 设置旋转角度
     * 
     * @DateTime 2019-03-20
     * @param integer $rotate [description]
     */
    public function setRotate($rotate = 0)
    {
        // 角度转换到0-360度内
        $rotate = $rotate > 0 ? ($rotate % 360) : ($rotate % -360);
        $rotate = $rotate > 0 ? $rotate : (360 + $rotate);
        $this->rot = (int)$rotate;
        return $this;
    }

}
