<?php

/*
 * tncode 1.2
 * https://gitee.com/zhaoxianfang/tncode
 */

namespace zxf\TnCode;

use GdImage;

class TnCode
{
    // 完整背景图片资源，初始化为null，类型为GdImage|null，用于存储从文件加载的完整背景图片
    private GdImage|null $im_fullbg = null;
    // 主要背景图片资源，初始化为null，类型为GdImage|null，用于存储裁剪等处理后的背景图片
    private GdImage|null $im_bg = null;
    // 滑块图片资源，初始化为null，类型为GdImage|null，用于存储滑块相关的图片
    private GdImage|null $im_slide = null;
    // 最终合成图片资源，初始化为null，类型为GdImage|null，用于存储整个验证码图片最终合成结果
    private GdImage|null $im = null;
    // 背景图片宽度，类型为int，单位为像素，用于定义背景图片的横向尺寸
    private int $bg_width = 240;
    // 背景图片高度，类型为int，单位为像素，用于定义背景图片的纵向尺寸
    private int $bg_height = 150;
    // 滑块标记的宽度，类型为int，单位为像素，用于定义滑块标记的横向尺寸
    private int $mark_width = 50;
    // 滑块标记的高度，类型为int，单位为像素，用于定义滑块标记的纵向尺寸
    private int $mark_height = 50;
    // 背景图片的数量，类型为int，用于表示可供选择的不同背景图片的个数
    private int $bg_num = 6;
    // 滑块在背景上的横坐标位置，初始化为0，类型为int，用于记录滑块在背景图片中的横向位置
    private int $_x = 0;
    // 滑块在背景上的纵坐标位置，初始化为0，类型为int，用于记录滑块在背景图片中的纵向位置
    private int $_y = 0;
    // 容错像素值，类型为int，越大用户体验越好，但验证码破解难度越低，越小则破解难度越高
    private int $_fault = 3;

    // 滑块验证码效果图配置
    private array $slideConfig = [];

    /**
     * 构造函数，用于初始化一些必要的设置等
     *
     * @param array $slideConfig 滑动验证码配置【一般不传此参数】
     */
    public function __construct(array $slideConfig = [])
    {
        // 关闭显示错误（可根据实际需求调整，这里保持原逻辑），将错误显示设置为关闭状态，不在页面上显示PHP错误信息
        // ini_set('display_errors', 'Off');
        // 设置错误报告级别为0，即抑制所有错误报告（同样可按需调整，此处维持原做法）
        // error_reporting(0);
        // 检查是否已经开启会话，如果没有则开启会话，以便后续在会话中存储验证码相关的信息，如滑块位置、验证错误次数等
//        if (!isset($_SESSION)) {
//            session_start();
//        }

        $defaultSlideConfig = [
            'tool_icon_img'         => dirname(__DIR__) . '/TnCode/Resources/img/icon.png', // 前端使用的图标组图片
            'slide_dark_img'        => dirname(__DIR__) . '/TnCode/Resources/img/mark2.png', // 黑色滑块图片
            'slide_transparent_img' => dirname(__DIR__) . '/TnCode/Resources/img/mark.png', // 透明滑块图片
        ];
        $this->slideConfig  = !empty($slideConfig) ? array_merge($defaultSlideConfig, $slideConfig) : $defaultSlideConfig;
    }

    /**
     * 生成验证码图片的主方法，按步骤调用多个私有方法来完成验证码图片的创建、合成及输出等操作
     *
     * @param array $bgImg 自定义背景图片路径，图片规格为 240x150，如果不传此参数，则使用默认背景图片
     *
     * @return void
     */
    public function make(array $bgImg = []): void
    {
        $this->init($bgImg);
        $this->createSlide();
        $this->createBg();
        $this->merge();
        $this->imgout();
        $this->destroy();
        die;
    }

    // 检查用户输入的滑块偏移量是否正确的方法，根据与存储的正确位置对比及容错值来判断
    public function check($offset = ''): bool
    {
        // 如果会话中不存在正确的滑块位置信息，则直接返回验证失败
        if (empty(i_session('tncode_r'))) {
            return false;
        }
        // 如果传入的偏移量参数为空，则尝试从请求参数中获取名为'tn_r'的滑块偏移量值
        if (!$offset) {
            $offset = $_REQUEST['tn_r'];
        }
        // 计算用户输入的偏移量与正确的滑块位置的差值绝对值是否在容错范围内，返回比较结果
        $ret = abs(i_session('tncode_r') - $offset) <= $this->_fault;
        if ($ret) {
            // 如果验证通过，移除会话中存储的正确滑块位置信息
            i_session('tncode_r', '');
        } else {
            // 如果验证失败，增加验证错误次数的计数
            i_session('tncode_r', i_session('tncode_r') + 1);
            // 如果错误次数超过10次，则强制刷新（移除正确滑块位置信息）
            if (i_session('tncode_err') > 10) {
                i_session('tncode_r', '');
            }
        }
        return $ret;
    }

    // 初始化相关图片资源及设置滑块初始位置、验证错误次数等的私有方法
    private function init(array $bgImg = []): void
    {
        // // 随机选择一个背景图片编号，范围从1到背景图片总数
        // $bg = mt_rand(1, $this->bg_num);
        // // 拼接背景图片文件的完整路径，根据类文件所在目录和背景图片编号及文件名后缀来确定
        // $file_bg = dirname(__FILE__). '/bg/'. $bg. '.png';

        // 背景图
        $bgImg = !empty($bgImg) ? $bgImg : [
            dirname(__DIR__) . '/TnCode/Resources/bg/1.png',
            dirname(__DIR__) . '/TnCode/Resources/bg/2.png',
            dirname(__DIR__) . '/TnCode/Resources/bg/3.png',
            dirname(__DIR__) . '/TnCode/Resources/bg/4.png',
            dirname(__DIR__) . '/TnCode/Resources/bg/5.png',
        ];

        // 从 $bgImg 里面随机取出一个元素作为背景图
        $file_bg = $bgImg[array_rand($bgImg)];

        // 从指定的PNG文件创建一个新的GD图像资源，作为完整背景图片
        $this->im_fullbg = imagecreatefrompng($file_bg);
        // 创建一个指定宽度和高度的真彩色GD图像资源，用于作为主要背景图片
        $this->im_bg = imagecreatetruecolor($this->bg_width, $this->bg_height);
        // 将完整背景图片复制到主要背景图片上，从坐标(0, 0)开始，按主要背景图片的尺寸进行复制
        imagecopy($this->im_bg, $this->im_fullbg, 0, 0, 0, 0, $this->bg_width, $this->bg_height);
        // 创建一个用于滑块的真彩色GD图像资源，尺寸根据滑块标记的宽度和背景图片高度确定
        $this->im_slide = imagecreatetruecolor($this->mark_width, $this->bg_height);
        // 随机生成滑块的横坐标位置，范围在50到背景图片宽度减去滑块标记宽度减1之间，并将其存储在会话中，同时赋值给类属性
        $x = $this->_x = mt_rand(50, $this->bg_width - $this->mark_width - 1);
        i_session('tncode_r', $x);
        // 初始化验证错误次数为0，并存储在会话中
        i_session('tncode_err', 0);
        // 随机生成滑块的纵坐标位置，范围在0到背景图片高度减去滑块标记高度减1之间
        $this->_y = mt_rand(0, $this->bg_height - $this->mark_height - 1);
    }

    // 销毁相关图片资源的私有方法，释放内存
    private function destroy(): void
    {
        // 销毁最终合成的图片资源
        imagedestroy($this->im);
        // 销毁完整背景图片资源
        imagedestroy($this->im_fullbg);
        // 销毁主要背景图片资源
        imagedestroy($this->im_bg);
        // 销毁滑块图片资源
        imagedestroy($this->im_slide);
    }

    // 输出图片到浏览器的私有方法，根据条件选择合适的图片格式及质量进行输出
    private function imgout(): void
    {
        // 判断是否不需要网页格式（通过GET参数'nowebp'判断）且当前环境支持imagewebp函数（用于输出webp格式图片），优先选择webp格式，因其有超高压缩率
        if (!isset($_GET['nowebp']) && function_exists('imagewebp')) {
            $type    = 'webp';
            $quality = 40; // webp格式图片质量，取值范围0 - 100
        } else {
            $type    = 'png';
            $quality = 7; // png格式图片质量，取值范围0 - 9
        }
        // 设置输出的HTTP头信息，指定内容类型为相应的图片格式
        header('Content-Type: image/' . $type);
        // 根据选择的图片格式动态构建对应的输出函数名，如'imagewebp'或'imagepng'
        $func = "image" . $type;
        // 调用对应的图片输出函数，将最终合成的图片输出到浏览器，第二个参数null表示按默认设置输出，第三个参数为指定的图片质量
        $func($this->im, null, $quality);
    }

    // 合并图片的私有方法，将背景图片、滑块图片等按一定布局合并到最终的验证码图片上
    private function merge(): void
    {
        // 创建一个新的真彩色GD图像资源，高度为背景图片高度的3倍，用于合并各个部分的图片
        $this->im = imagecreatetruecolor($this->bg_width, $this->bg_height * 3);
        // 将主要背景图片复制到最终合成图片的顶部（坐标(0, 0)处），按主要背景图片的尺寸进行复制
        imagecopy($this->im, $this->im_bg, 0, 0, 0, 0, $this->bg_width, $this->bg_height);
        // 将滑块图片复制到最终合成图片的中间部分（纵坐标为背景图片高度处），按滑块图片的尺寸进行复制
        imagecopy($this->im, $this->im_slide, 0, $this->bg_height, 0, 0, $this->mark_width, $this->bg_height);
        // 将完整背景图片再次复制到最终合成图片的底部（纵坐标为背景图片高度的2倍处），按完整背景图片的尺寸进行复制
        imagecopy($this->im, $this->im_fullbg, 0, $this->bg_height * 2, 0, 0, $this->bg_width, $this->bg_height);
        // 设置图像的透明颜色为白色（颜色值16777215对应的是白色），使相应颜色部分变为透明
        imagecolortransparent($this->im, 0);
    }

    // 在背景图片上创建滑块标记（复制标记图片到背景相应位置）的私有方法
    private function createBg(): void
    {
        // 拼接滑块标记图片的文件路径，指向类文件所在目录下的/img/mark.png文件
        // $file_mark = dirname(__FILE__) . '/img/mark.png';
        $file_mark = $this->slideConfig['slide_transparent_img'];
        // 从指定的PNG文件创建一个新的GD图像资源，作为滑块标记图片
        $im = imagecreatefrompng($file_mark);
        // 设置输出的HTTP头信息为PNG图片格式（这里在创建滑块标记到背景时设置了头信息，可根据实际情况调整位置，比如统一在imgout方法处设置等）
        header('Content-Type: image/png');
        // 设置图像的透明颜色为白色（颜色值16777215对应的是白色），使相应颜色部分变为透明
        imagecolortransparent($im, 0);
        // 将滑块标记图片复制到主要背景图片的指定位置（根据滑块的横纵坐标确定），按滑块标记图片的尺寸进行复制
        imagecopy($this->im_bg, $im, $this->_x, $this->_y, 0, 0, $this->mark_width, $this->mark_height);
        // 销毁创建的滑块标记图片资源，释放内存
        imagedestroy($im);
    }

    // 创建滑块图片的私有方法，将相关图片复制组合到滑块图片上
    private function createSlide(): void
    {
        // 拼接另一个滑块相关标记图片（这里假设是用于滑块显示效果等的图片）的文件路径，指向类文件所在目录下的/img/mark2.png文件
        // $file_mark = dirname(__FILE__) . '/img/mark2.png';
        $file_mark = $this->slideConfig['slide_dark_img'];
        // 从指定的PNG文件创建一个新的GD图像资源，作为滑块的相关标记图片
        $img_mark = imagecreatefrompng($file_mark);
        // 先将完整背景图片的一部分复制到滑块图片上，根据滑块的横纵坐标确定复制的起始位置和尺寸
        imagecopy($this->im_slide, $this->im_fullbg, 0, $this->_y, $this->_x, $this->_y, $this->mark_width, $this->mark_height);
        // 再将滑块的相关标记图片复制到滑块图片上，覆盖在刚才复制的背景部分之上，按滑块相关标记图片的尺寸进行复制
        imagecopy($this->im_slide, $img_mark, 0, $this->_y, 0, 0, $this->mark_width, $this->mark_height);
        // 设置滑块图片的透明颜色为白色（颜色值16777215对应的是白色），使相应颜色部分变为透明
        imagecolortransparent($this->im_slide, 0);
        // 销毁创建的滑块相关标记图片资源，释放内存
        imagedestroy($img_mark);
    }
}
