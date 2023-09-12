<?php

namespace zxf\Pay\Traits;

use Exception;

/**
 * Http 网络请求
 */
trait HttpTrait
{
    /**
     * 发送post 请求
     *
     * @param array $data
     * @param bool  $useJson 发送的数据是否使用 json 格式(json_encode)
     *
     * @return mixed
     * @throws Exception
     */
    public function post(array $data = [], bool $useJson = true)
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : ($data ?? $this->body);
        $data = $data ?: [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'POST', $data);

        $data   = $useJson ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        $result = $this->http->setHeader($header, false)->setParams($data)->post($url);
        $this->clear();
        return $result;
    }

    public function get(array $data = [])
    {
        $data   = ($data && $this->body) ? array_merge($this->body, $data) : ($data ?? $this->body);
        $data   = $data ?: [];
        $params = $this->appendBody($data);

        $url    = $this->parseUrl($this->url, $params);
        $header = $this->v3CreateHeader($url, 'GET', '');

        $result = $this->http->setHeader($header, false)->get($this->parseUrl($this->url, $params));
        $this->clear();
        return $result;
    }

    public function delete(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : ($data ?? $this->body);
        $data = $data ?: [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'DELETE', $data);

        $result = $this->http->setHeader($header, false)->setParams($data)->delete($url);
        $this->clear();
        return $result;
    }

    public function put(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : ($data ?? $this->body);
        $data = $data ?: [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'PUT', $data);

        $result = $this->http->setHeader($header, false)->setParams($data)->put($url);
        $this->clear();
        return $result;
    }

    public function patch(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : ($data ?? $this->body);
        $data = $data ?: [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'PATCH', $data);

        $result = $this->http->setHeader($header, false)->setParams($data)->patch($url);
        $this->clear();
        return $result;
    }

    protected function parseUrl(?string $url = '', ?array $params = [])
    {
        $url = $url ?? $this->url;

        if (empty($url)) {
            return $this->error("接口请求地址不能为空");
        }

        $baseUrl  = str_starts_with($url, "http") ? $url : $this->urlBase;
        $url      = str_replace(["API_URL"], [$url], $baseUrl);
        $urlQuery = !empty($params) ? http_build_query($params) : "";

        if (!empty($urlQuery) && is_bool(stripos($url, $urlQuery))) {
            $url = trim($url, '?');
            $url .= ((stripos($url, "?")) ? "&" : "?") . $urlQuery;
        }

        return $url;
    }
}