<?php

namespace zxf\QrCode;

use ErrorException;
use GdImage;
use zxf\QrCode\Common\EccLevel;
use zxf\QrCode\Extend\WithTextOrLogo;
use zxf\QrCode\Output\QRCodeOutputException;

class QRCodeGenerate
{

    /** @var QRCode 二维码生成对象 */
    protected QRCode $qrcode;

    /** @var QROptions 二维码配置 */
    protected QROptions $options;

    protected array $opts;

    /** @var string 二维码底部需要显示的文本 */
    protected string $text = '';

    /** @var int 文本大小 */
    protected int $textSize = 0;

    /** @var string 字体路径 */
    protected string $fontPath = '';

    /** @var string logo路径 */
    protected string $logoPath = '';

    protected WithTextOrLogo $withTextOrLogo;

    public function __construct(array $options = [])
    {
        $defaultOptions = [
            // 二维码版本（1-40），数值越大，二维码越复杂
            'version'          => \zxf\QrCode\Common\Version::AUTO, // 自动调整版本
            'eccLevel'         => EccLevel::H,  // 纠错级别（L:7% (default)、M:15%、Q:25%、H:30%）
            'scale'            => 3,  // 每个模块的像素大小
            'outputBase64'     => false,  // 切换 base64 数据 URI 或原始数据输出（如果适用） 默认true
            'imageTransparent' => false,  // 是否使用透明背景
            'addQuietzone'     => true,  // 是否添加静默区 二维码的 margin
            'quietzoneSize'    => 4,  // margin 大小（0 ... $moduleCount / 2） 默认为 4
            'cachefile'        => null, // 缓存文件
        ];
        // 配置选项
        $this->opts    = $options ? array_merge($defaultOptions, $options) : $defaultOptions;
        $this->options = new QROptions($this->opts);
        $this->qrcode  = new QRCode($this->options);
    }

    /**
     * 设置二维码内容
     *
     * @param string $content
     *
     * @return $this
     */
    public function content(string $content = '')
    {
        $this->qrcode->addByteSegment($content);
        return $this;
    }

    /**
     * 设置二维码底部文字
     *
     * @param string $text
     * @param string $fontPath
     * @param int    $textSize
     *
     * @return $this
     */
    public function withText(string $text = '', string $fontPath = '', int $textSize = 0)
    {
        $this->text     = $text;
        $this->fontPath = $fontPath;
        $this->textSize = $textSize;

        return $this;
    }

    /**
     * 设置二维码logo
     *
     * @param string $logoPath
     *
     * @return $this
     */
    public function withLogo(string $logoPath = '')
    {
        $this->logoPath = $logoPath;
        return $this;
    }

    /**
     * 渲染带logo或文字的二维码
     *
     * @param int         $type     渲染类型
     * @param string|null $savePath 保存路径
     *
     * @return GdImage|string|null
     * @throws ErrorException
     * @throws QRCodeOutputException
     */
    private function draw(int $type = WithTextOrLogo::HANDLE_TYPE_TO_BROWSER, string|null $savePath = null): GdImage|string|null
    {
        if (!empty($this->logoPath)) {
            $this->options->addLogoSpace    = true; // 是否在 QR 码中添加 Logo 空间
            $this->options->logoSpaceWidth  = 10; // Logo 空间的宽度, 如果仅给出 QROptions::$logoSpaceWidth，则徽标空间被假定为该大小的正方形
            $this->options->logoSpaceHeight = 10; // Logo 空间的高度
        }
        // 带logo或文字的二维码
        $obj = new WithTextOrLogo($this->options, $this->qrcode->getQRMatrix());
        $obj->setText($this->text, $this->fontPath, $this->textSize);
        $obj->setLogo($this->logoPath);
        $obj->setHandleType($type);
        return $obj->dump($savePath);
    }

    /**
     * 把生成的图片直接渲染到浏览器中
     *
     * @return void
     * @throws ErrorException
     * @throws QRCodeOutputException
     */
    public function toBrowser(): void
    {
        $this->draw(WithTextOrLogo::HANDLE_TYPE_TO_BROWSER);
    }

    /**
     * 把生成的图片保存到指定路径
     *
     * @param string $savePath
     *
     * @return string
     * @throws ErrorException
     * @throws QRCodeOutputException
     */
    public function toFile(string $savePath = ''): string
    {
        return $this->draw(WithTextOrLogo::HANDLE_TYPE_TO_FILE, $savePath);
    }

    /**
     * 生成base64图片字符串
     *
     * @return string
     * @throws ErrorException
     * @throws QRCodeOutputException
     */
    public function toBase64(): string
    {
        return $this->draw(WithTextOrLogo::HANDLE_TYPE_TO_BASE_64);
    }

    /**
     * 把生成的图片资源返回
     *
     * @return string
     * @throws ErrorException
     * @throws QRCodeOutputException
     */
    public function toImg(): string
    {
        return $this->draw(WithTextOrLogo::HANDLE_TYPE_TO_IMG);
    }

    /**
     * 调用不存在的方法时，调用QRCode类的方法
     *
     * @param string $method 调用的方法名
     * @param mixed  $arg    参数
     *
     * @return mixed
     */
    public function __call(string $method, mixed $arg)
    {
        return $this->qrcode->$method(...$arg);
    }
}
