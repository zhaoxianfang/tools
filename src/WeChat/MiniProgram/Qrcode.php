<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信小程序二维码管理
 */
class Qrcode extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 获取小程序码（永久有效）
     * 接口A: 适用于需要的码数量较少的业务场景
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param string     $path       不能为空，最大长度 128 字节
     * @param int|null   $width      二维码的宽度
     * @param bool       $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array|null $line_color auto_color 为 false 时生效
     * @param boolean    $is_hyaline 是否需要透明底色
     *
     * @return array|string
     * @throws Exception
     */
    public function createMiniPath(string $path, ?int $width = 430, ?bool $auto_color = false, ?array $line_color = ["r" => "0", "g" => "0", "b" => "0"], ?bool $is_hyaline = true)
    {
        $data = ['path' => $path, 'width' => $width, 'auto_color' => $auto_color, 'line_color' => $line_color, 'is_hyaline' => $is_hyaline];
        return $this->post('wxa/getwxacode', json_encode($data));
    }

    /**
     * 获取小程序码（永久有效）
     * 接口B：适用于需要的码数量极多的业务场景
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     *
     * @param string      $scene      最大32个可见字符，只支持数字
     * @param string|null $page       必须是已经发布的小程序存在的页面
     * @param int|null    $width      二维码的宽度
     * @param bool        $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array|null  $line_color auto_color 为 false 时生效
     * @param boolean     $is_hyaline 是否需要透明底色
     * @param array|null  $extra      其他参数
     *
     * @return array|string
     * @throws Exception
     */
    public function createMiniScene(string $scene, ?string $page, ?int $width = 430, ?bool $auto_color = false, ?array $line_color = ["r" => "0", "g" => "0", "b" => "0"], ?bool $is_hyaline = true, ?array $extra = [])
    {
        $data = array_merge(['scene' => $scene, 'width' => $width, 'auto_color' => $auto_color, 'page' => $page, 'line_color' => $line_color, 'is_hyaline' => $is_hyaline], $extra);

        return $this->post('wxa/getwxacodeunlimit', json_encode($data));
    }

    /**
     * 获取小程序二维码（永久有效）
     * 接口C：适用于需要的码数量较少的业务场景
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/createQRCode.html
     *
     * @param string   $path  不能为空，最大长度 128 字节
     * @param int|null $width 二维码的宽度
     *
     * @return array|string
     * @throws Exception
     */
    public function createDefault(string $path, ?int $width = 430)
    {
        return $this->post('cgi-bin/wxaapp/createwxaqrcode', json_encode(['path' => $path, 'width' => $width]));
    }
}