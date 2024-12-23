<?php

namespace zxf\TnCode;

use Illuminate\Http\Response;

class AssetController
{
    protected array $jsFiles = [];

    protected array $cssFiles = [];

    public function __construct()
    {
        $this->jsFiles  = [
            dirname(__DIR__) . '/TnCode/Resources/tn_code.min.js',
        ];
        $this->cssFiles = [
            dirname(__DIR__) . '/TnCode/Resources/style.css',
        ];
    }

    /**
     * 获取调试js
     */
    public function js()
    {
        $content = '';
        foreach ($this->jsFiles as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        $response = new Response($content, 200, [
            'Content-Type' => 'text/javascript',
        ]);

        return $this->cacheResponse($response);
    }

    public function img($path)
    {
        $image_file = dirname(__DIR__) . '/TnCode/Resources/img/'.$path;
        // 设置适当的 Content-Type 头信息
        header('Content-Type: image/png');

// 如果需要，还可以设置其他头信息，比如缓存控制
// header('Cache-Control: public, max-age=86400'); // 缓存一天

// 输出图片内容
        readfile($image_file);
        die;
        return file_get_contents($path);
    }

    /**
     * 获取调试css
     */
    public function css()
    {
        $content = '';
        foreach ($this->cssFiles as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        $response = new Response($content, 200, [
            'Content-Type' => 'text/css',
        ]);

        return $this->cacheResponse($response);
    }

    /**
     * Cache the response 1 year (31536000 sec)
     */
    protected function cacheResponse(Response $response)
    {
//        $response->setSharedMaxAge(31536000);
//        $response->setMaxAge(31536000);
//        $response->setExpires(new \DateTime('+1 year'));
        return $response;
    }
}
