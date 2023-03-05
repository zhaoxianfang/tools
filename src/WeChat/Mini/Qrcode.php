<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;


/**
 * 微信小程序二维码管理
 * Class Qrcode
 *
 * @package WeMini
 */
class Qrcode extends WeChatBase
{

    /**
     * 获取小程序码（永久有效）
     * 接口A: 适用于需要的码数量较少的业务场景
     *
     * @param string      $path       不能为空，最大长度 128 字节
     * @param integer     $width      二维码的宽度
     * @param bool        $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array       $line_color auto_color 为 false 时生效
     * @param boolean     $is_hyaline 是否需要透明底色
     * @param null|string $outType    输出类型
     *
     * @return array|string
     * @throws Exception
     */
    public function createMiniPath($path, $width = 430, $auto_color = false, $line_color = ["r" => "0", "g" => "0", "b" => "0"], $is_hyaline = true, $outType = null)
    {
        $data = ["path" => $path, "width" => $width, "auto_color" => $auto_color, "line_color" => $line_color, "is_hyaline" => $is_hyaline];
        return $this->post("wxa/getwxacode", $data);
    }

    /**
     * 获取小程序码（永久有效）
     * 接口B：适用于需要的码数量极多的业务场景
     *
     * @param string      $scene      最大32个可见字符，只支持数字
     * @param string      $page       必须是已经发布的小程序存在的页面
     * @param integer     $width      二维码的宽度
     * @param bool        $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array       $line_color auto_color 为 false 时生效
     * @param boolean     $is_hyaline 是否需要透明底色
     * @param null|string $outType    输出类型
     *
     * @return array|string
     * @throws Exception
     */
    public function createMiniScene($scene, $page, $width = 430, $auto_color = false, $line_color = ["r" => "0", "g" => "0", "b" => "0"], $is_hyaline = true, $outType = null)
    {
        $data = ["scene" => $scene, "width" => $width, "auto_color" => $auto_color, "page" => $page, "line_color" => $line_color, "is_hyaline" => $is_hyaline];
        return $this->post("wxa/getwxacodeunlimit", $data);
    }

    /**
     * 获取小程序二维码（永久有效）
     * 接口C：适用于需要的码数量较少的业务场景
     *
     * @param string      $path    不能为空，最大长度 128 字节
     * @param integer     $width   二维码的宽度
     * @param null|string $outType 输出类型
     *
     * @return array|string
     * @throws Exception
     */
    public function createDefault($path, $width = 430, $outType = null)
    {
        return $this->post("cgi-bin/wxaapp/createwxaqrcode", ["path" => $path, "width" => $width]);
    }
}