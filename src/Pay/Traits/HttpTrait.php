<?php

namespace zxf\Pay\Traits;

use Exception;

/**
 * Http 网络请求
 */
trait HttpTrait
{
    protected array $httpReqData = [];
    protected bool  $useJson     = true;

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
        $data = ($data && $this->body) ? array_merge($this->body, $data) : (!empty($data) ? $data : $this->body);
        $data = $data ?? [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'POST', $data);

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $this->httpReqData = $data;
        $this->useJson     = $useJson;

        $data = $useJson ? json_encode($data) : $data;

        $result = $this->http->setHeader($header, false)->setParams($data)->post($url);
        if ($this->checkReqIsFail($result)) {
            return $this->retryHttp('POST');
        }
        $this->clear();
        return $result;
    }

    public function get(array $data = [])
    {
        $data   = ($data && $this->body) ? array_merge($this->body, $data) : (!empty($data) ? $data : $this->body);
        $data   = $data ?? [];
        $params = $this->appendBody($data);

        $url    = $this->parseUrl($this->url, $params);
        $header = $this->v3CreateHeader($url, 'GET', '');

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $this->httpReqData = $params;
        $this->useJson     = false;

        $result = $this->http->setHeader($header, false)->get($url);
        if ($this->checkReqIsFail($result)) {
            return $this->retryHttp('GET');
        }
        $this->clear();
        return $result;
    }

    public function delete(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : (!empty($data) ? $data : $this->body);
        $data = $data ?? [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'DELETE', $data);

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $this->httpReqData = $data;
        $this->useJson     = false;

        $result = $this->http->setHeader($header, false)->setParams($data)->delete($url);
        if ($this->checkReqIsFail($result)) {
            return $this->retryHttp('delete');
        }
        $this->clear();
        return $result;
    }

    public function put(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : (!empty($data) ? $data : $this->body);
        $data = $data ?? [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'PUT', $data);

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $this->httpReqData = $data;
        $this->useJson     = false;

        $result = $this->http->setHeader($header, false)->setParams($data)->put($url);
        if ($this->checkReqIsFail($result)) {
            return $this->retryHttp('put');
        }
        $this->clear();
        return $result;
    }

    public function patch(array $data = [])
    {
        $data = ($data && $this->body) ? array_merge($this->body, $data) : (!empty($data) ? $data : $this->body);
        $data = $data ?? [];
        $this->appendBody($data);

        $url    = $this->parseUrl($this->url);
        $header = $this->v3CreateHeader($url, 'PATCH', $data);

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $this->httpReqData = $data;
        $this->useJson     = false;

        $result = $this->http->setHeader($header, false)->setParams($data)->patch($url);
        if ($this->checkReqIsFail($result)) {
            return $this->retryHttp('patch');
        }
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

    /**
     * 解析微信容灾备用地址
     *
     * @param string|null $url
     * @param array|null  $params
     *
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    protected function parseBackupUrl(?string $url = '', ?array $params = [])
    {
        $url = $url ?? $this->url;

        if (empty($url)) {
            return $this->error("接口请求地址不能为空");
        }

        $baseUrl  = str_starts_with($url, "http") ? $url : $this->backupUrlBase;
        $url      = str_replace(["API_URL"], [$url], $baseUrl);
        $urlQuery = !empty($params) ? http_build_query($params) : "";

        if (!empty($urlQuery) && is_bool(stripos($url, $urlQuery))) {
            $url = trim($url, '?');
            $url .= ((stripos($url, "?")) ? "&" : "?") . $urlQuery;
        }

        return $url;
    }

    /**
     * 检查请求结果是否正常
     *
     * @param string $httpResult
     *
     * @return bool
     */
    private function checkReqIsFail(mixed $httpResult = ''): bool
    {
        return is_string($httpResult) && str_contains($httpResult, 'Could not resolve host');
    }

    /**
     * 重新使用微信容灾域名发起请求
     *
     * @param string $method
     *
     * @return mixed
     * @throws Exception
     */
    private function retryHttp(string $method = 'post')
    {
        // 重试 请求微信容灾备用域名

        if (strtoupper($method) == 'GET') {
            $url    = $this->parseUrl($this->url, $this->httpReqData);
            $header = $this->v3CreateHeader($url, 'GET', '');
        } else {
            $url    = $this->parseBackupUrl($this->url);
            $header = $this->v3CreateHeader($url, strtoupper($method), $this->httpReqData);
        }

        if ($this->useSerialHeader) {
            $header = array_merge($header, $this->getSerialHeader());
        }

        $method = strtolower($method);
        $data   = $this->useJson ? json_encode($this->httpReqData) : $this->httpReqData;

        $result = $this->http->setHeader($header, false)->setParams($data)->$method($url);
        $this->clear();
        return $result;
    }
}
