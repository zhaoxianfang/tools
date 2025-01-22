<?php

namespace zxf\QRCode;

use GdImage;
use zxf\QRCode\Common\EccLevel;
use zxf\QRCode\Extend\WithTextOrLogo;
use zxf\QRCode\Output\QRCodeOutputException;

class QRCodePlus
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
            'version'          => 10, // 二维码版本（1-40），数值越大，二维码越复杂
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

        // 默认初始化一个内容
        $this->content('Hello!');
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
     * @param int    $type     渲染类型
     * @param string $savePath 保存路径
     *
     * @return GdImage|string
     * @throws QRCodeOutputException
     * @throws \ErrorException
     */
    public function run(int $type = WithTextOrLogo::HANDLE_TYPE_TO_BROWSER, string $savePath = ''): GdImage|string
    {
        if (!empty($this->logoPath)) {
            $this->options->addLogoSpace    = true; // 是否在 QR 码中添加 Logo 空间
            $this->options->logoSpaceWidth  = 20; // Logo 空间的宽度, 如果仅给出 QROptions::$logoSpaceWidth，则徽标空间被假定为该大小的正方形
            $this->options->logoSpaceHeight = 20; // Logo 空间的高度
        }
        // 带logo或文字的二维码
        $obj = new WithTextOrLogo($this->options, $this->qrcode->getQRMatrix());
        $obj->setText($this->text, $this->fontPath, $this->textSize);
        $obj->setLogo($this->logoPath);
        $obj->setHandleType($type);
        return $obj->dump($savePath);
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
