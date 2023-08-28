<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序 URL-Scheme
 */
class Scheme extends WeChatBase
{
    public $useToken = true;


    /**
     * 创建 URL-Scheme
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/url-scheme/generateScheme.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        return $this->post('wxa/generatescheme', $data);
    }

    /**
     * 查询 URL-Scheme
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/url-scheme/queryScheme.html
     *
     * @param string $scheme 小程序 scheme 码
     *
     * @return array
     * @throws Exception
     */
    public function query(string $scheme)
    {
        return $this->post('wxa/queryscheme', ['scheme' => $scheme]);
    }

    /**
     * 获取 NFC 的小程序 scheme
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/url-scheme/generateNFCScheme.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function generateNFCScheme(array $data)
    {
        return $this->post('wxa/generatenfcscheme', $data);
    }

    /**
     * 创建 URL-Link
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/url-link/generateUrlLink.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function urlLink(array $data)
    {
        return $this->post('wxa/generate_urllink', $data);
    }

    /**
     * 查询 URL-Link
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/url-link/queryUrlLink.html
     *
     * @param string $urllink 小程序 url_link
     *
     * @return array
     * @throws Exception
     */
    public function urlQuery(string $urllink)
    {
        return $this->post('wxa/query_urllink', ['url_link' => $urllink]);
    }

    /**
     * 获取ShortLink
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/short-link/generateShortLink.html
     *
     * @param string      $page_url     通过 Short Link 进入的小程序页面路径，必须是已经发布的小程序存在的页面，可携带 query，最大1024个字符
     * @param string|null $page_title   页面标题，不能包含违法信息，超过20字符会用... 截断代替
     * @param bool|null   $is_permanent 默认值false。生成的 Short Link 类型，短期有效：false，永久有效：true
     *
     * @return array
     * @throws Exception
     */
    public function generateShortLink(string $page_url, ?string $page_title, ?bool $is_permanent = false)
    {
        $data = [
            'page_url'     => $page_url,
            'page_title'   => $page_title,
            'is_permanent' => $is_permanent,
        ];
        return $this->post('wxa/genwxashortlink', $data);
    }
}