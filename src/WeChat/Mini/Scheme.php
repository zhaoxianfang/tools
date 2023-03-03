<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序 URL-Scheme
 * Class Scheme
 *
 * @package WeMini
 */
class Scheme extends WeChatBase
{

    /**
     * 创建 URL-Scheme
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
     * @param string $scheme
     *
     * @return array
     * @throws Exception
     */
    public function query($scheme)
    {
        return $this->post('wxa/queryscheme', ['scheme' => $scheme]);
    }

    /**
     * 创建 URL-Link
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function urlLink($data)
    {
        return $this->post("wxa/generate_urllink", $data);
    }

    /**
     * 查询 URL-Link
     *
     * @param string $urllink
     *
     * @return array
     * @throws Exception
     */
    public function urlQuery($urllink)
    {
        return $this->post('wxa/query_urllink', ['url_link' => $urllink]);
    }
}