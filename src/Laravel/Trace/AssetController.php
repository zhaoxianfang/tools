<?php

namespace zxf\Laravel\Trace;

use Illuminate\Http\Response;

class AssetController
{
    protected array $jsFiles = [];

    protected array $cssFiles = [];

    public function __construct()
    {
        $this->jsFiles  = [
            dirname(__DIR__, 2) . '/resource/js/trace.js',
        ];
        $this->cssFiles = [
            dirname(__DIR__, 2) . '/resource/css/trace.css',
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
        $response->setSharedMaxAge(31536000);
        $response->setMaxAge(31536000);
        $response->setExpires(new \DateTime('+1 year'));
        return $response;
    }
}