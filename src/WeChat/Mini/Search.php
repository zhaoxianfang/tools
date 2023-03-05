<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序搜索
 * Class Search
 *
 * @package WeMini
 */
class Search extends WeChatBase
{
    /**
     * 提交小程序页面url及参数信息
     *
     * @param array $pages
     *
     * @return array
     * @throws Exception
     */
    public function submitPages($pages)
    {
        return $this->post("cgi-bin/guide/getguideacct", ["pages" => $pages]);
    }
}