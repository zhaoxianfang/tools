<?php
namespace zxf\verify;

use Exception;

/**
 * 图片滑动式验证码
 * @Author   ZhaoXianFang
 * @DateTime 2019-09-30
 */
class ImgCode
{
    //@object 对象实例
    protected static $instance;
    protected $im         = null;
    protected $imFullBg   = null;
    protected $imBg       = null;
    protected $imSlide    = null;
    protected $bgWidth    = 240;
    protected $bgHeight   = 150;
    protected $markWidth  = 50;
    protected $markHeight = 50;
    protected $_x         = 0;
    protected $_y         = 0;

    protected $bgImgPath       = ""; // 图片背景地址
    protected $markImgPath     = ""; // 背景图卡口图片地址
    protected $moveMarkImgPath = ""; // 用户滑动的卡口图片地址
    //容错象素 越大体验越好，越小破解难道越高
    protected $_fault      = 3;
    protected $requestTime = null; //API请求者请求时间
    protected $cachePrefix = 'none'; //为API请求者设置附加缓存前缀

    public function __construct()
    {
        //ini_set('display_errors','On');
        error_reporting(0);
        if (!isset($_SESSION)) {
            try {
                session_start();
            } catch (Exception $e) {
                throw new Exception('session 未启用');
            }

        }
        // $options = [
        //     // 缓存类型为File
        //     'type'   => 'File',
        //     // 缓存有效期为半小时有效
        //     'expire' => 1800,
        //     // 指定缓存目录
        //     'path'   => Env::get('runtime_path') . 'cache/img_code/',
        // ];
        // // 缓存初始化
        // // 不进行缓存初始化的话，默认使用配置文件中的缓存配置
        // cache($options);

    }
    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [], $init = true)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }
    /**
     * 设置参数
     * @Author   ZhaoXianFang
     * @DateTime 2019-09-30
     * @param    string       $imgPath      [必填]验证码背景图
     * @param    string       $markPath     [建议为空]卡口图
     * @param    string       $moveMarkPath [建议为空]用户滑动卡口图
     */
    public function setOptions($imgPath = '', $markPath = '', $moveMarkPath = '')
    {
        if (!$imgPath) {
            throw new \Exception('图片地址不能为空');
        }
        $this->bgImgPath       = $imgPath;
        $this->markImgPath     = $markPath ? $markPath : dirname(__FILE__) . '/img/mark.png';
        $this->moveMarkImgPath = $moveMarkPath ? $moveMarkPath : dirname(__FILE__) . '/img/mark2.png';

        return $this;
    }

    public function make()
    {
        // 先设置验证码为false
        $this->clear();
        $this->_setRequesterCode();
        $this->_init();
        $this->_createSlide();
        $this->_createBg();
        $this->_merge();
        $this->_imgout();
        $this->_destroy();
    }
    /**
     * 验证验证码
     * @Author   ZhaoXianFang
     * @DateTime 2019-11-19
     * @param    boolean      $isFirst [是否为第一次验证，第一次发生在图片滑动验证，第二次发生在表单提交验证，没有第三次]
     * @param    string       $offset  [验证码偏移量]
     * @return   [type]                [description]
     */
    public function check($isFirst = true, $offset = '')
    {
        $this->_setRequesterCode();

        if (!$offset) {
            $offset = $_REQUEST['check_x'];
        }
        if (!$_SESSION[$this->cacheImgX]) {
            if (!$offset) {
                // 验证成功后的再次确认 要求3分钟内
                $checkTime = $this->requestTime - $_SESSION[$this->cacheCheckSuccessTime];
                if ($_SESSION[$this->cacheCheckSuccess] == 1 && $checkTime <= 180 && $checkTime > 0) {
                    if (!$isFirst) {
                        $this->clear();
                    }
                    return true;
                }
            }
            return false;
        }

        $ret = abs($_SESSION[$this->cacheImgX] - $offset) <= $this->_fault;
        if ($ret) {
            $_SESSION[$this->cacheImgX]             = null;
            $_SESSION[$this->cacheCheckSuccess]     = 1; //验证通过
            $_SESSION[$this->cacheCheckSuccessTime] = $this->requestTime; //验证通过时间
            if (!$isFirst) {
                $this->clear();
            }
            return true;
        } else {
            $_SESSION[$this->cacheErr] = $_SESSION[$this->cacheErr] + 1;
            if ($_SESSION[$this->cacheErr] > 10) {
                //错误10次必须刷新
                $_SESSION[$this->cacheImgX] = null;
            }
        }
        return $ret;
    }

    // 清除
    public function clear()
    {
        $_SESSION[$this->cacheImgX]             = null;
        $_SESSION[$this->cacheCheckSuccess]     = 0; //验证通过
        $_SESSION[$this->cacheCheckSuccessTime] = 0; //验证通过时间
    }

    private function _init()
    {

        $file_bg = $this->bgImgPath;
        if (!$file_bg) {
            throw new \Exception('图片地址不能为空');
        }
        $this->imFullBg = imagecreatefrompng($file_bg);
        $this->imBg     = imagecreatetruecolor($this->bgWidth, $this->bgHeight);
        imagecopy($this->imBg, $this->imFullBg, 0, 0, 0, 0, $this->bgWidth, $this->bgHeight);
        $this->imSlide                      = imagecreatetruecolor($this->markWidth, $this->bgHeight);
        $_SESSION[$this->cacheImgX]         = $this->_x         = mt_rand(50, $this->bgWidth - $this->markWidth - 1);
        $_SESSION[$this->cacheRequestTime]  = $this->requestTime;
        $_SESSION[$this->cacheErr]          = 0;
        $_SESSION[$this->cacheCheckSuccess] = 0; //是否验证通过

        $this->_y = mt_rand(0, $this->bgHeight - $this->markHeight - 1);

    }

    /**
     * 请求者识别码
     * @Author   ZhaoXianFang
     * @DateTime 2019-10-14
     * @return   [type]       [description]
     */
    private function _setRequesterCode()
    {
        $remoteAddr        = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $remotePort        = isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : ''; //动态端口号
        $this->requestTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : '';

        if (!$remoteAddr || !$remotePort) {
            $this->cachePrefix = 'none';
            throw new \Exception('无法识别的请求');
        } else {
            // $this->cachePrefix           = $remoteAddr . '_' . $remotePort;
            $this->cachePrefix           = $remoteAddr;
            $this->cacheImgX             = $this->cachePrefix . '_img_x';
            $this->cacheRequestTime      = $this->cachePrefix . '_request_time';
            $this->cacheErr              = $this->cachePrefix . '_imgcode_err';
            $this->cacheCheckSuccess     = $this->cachePrefix . '_imgcode_check_success';
            $this->cacheCheckSuccessTime = $this->cachePrefix . '_imgcode_check_success_time';
        }
    }

    private function _destroy()
    {
        imagedestroy($this->im);
        imagedestroy($this->imFullBg);
        imagedestroy($this->imBg);
        imagedestroy($this->imSlide);
    }
    private function _imgout()
    {
        if (!$_GET['nowebp'] && function_exists('imagewebp')) {
            //优先webp格式，超高压缩率
            $type    = 'webp';
            $quality = 40; //图片质量 0-100
        } else {
            $type    = 'png';
            $quality = 7; //图片质量 0-9
        }
        header('Content-Type: image/' . $type);
        $func = "image" . $type;
        $func($this->im, null, $quality);
    }
    private function _merge()
    {
        $this->im = imagecreatetruecolor($this->bgWidth, $this->bgHeight * 3);
        imagecopy($this->im, $this->imBg, 0, 0, 0, 0, $this->bgWidth, $this->bgHeight);
        imagecopy($this->im, $this->imSlide, 0, $this->bgHeight, 0, 0, $this->markWidth, $this->bgHeight);
        imagecopy($this->im, $this->imFullBg, 0, $this->bgHeight * 2, 0, 0, $this->bgWidth, $this->bgHeight);
        imagecolortransparent($this->im, 0); //16777215
    }

    private function _createBg()
    {
        $file_mark = $this->markImgPath;
        if (!$file_mark) {
            throw new \Exception('mark图片地址不能为空');
        }
        $im = imagecreatefrompng($file_mark);
        header('Content-Type: image/png');
        //imagealphablending( $im, true);
        imagecolortransparent($im, 0); //16777215
        //imagepng($im);exit;
        imagecopy($this->imBg, $im, $this->_x, $this->_y, 0, 0, $this->markWidth, $this->markHeight);
        imagedestroy($im);
    }

    private function _createSlide()
    {
        $file_mark = $this->moveMarkImgPath;
        if (!$file_mark) {
            throw new \Exception('mark图片地址不能为空');
        }
        $img_mark = imagecreatefrompng($file_mark);
        imagecopy($this->imSlide, $this->imFullBg, 0, $this->_y, $this->_x, $this->_y, $this->markWidth, $this->markHeight);
        imagecopy($this->imSlide, $img_mark, 0, $this->_y, 0, 0, $this->markWidth, $this->markHeight);
        imagecolortransparent($this->imSlide, 0); //16777215
        //header('Content-Type: image/png');
        //imagepng($this->imSlide);exit;
        imagedestroy($img_mark);
    }

}
