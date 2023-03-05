<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序运维中心
 * Class Operation
 *
 * @package WeMini
 */
class Operation extends WeChatBase
{

    /**
     * 实时日志查询
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function realtimelogSearch($data)
    {
        return $this->post("wxaapi/userlog/userlog_search", $data);
    }
}