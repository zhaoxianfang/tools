<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信交易保障
 */
class TransactionGuarantee extends WeChatBase
{
    public bool $useToken = false;

    /**
     * 获取小程序交易体验分违规记录
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/transaction-guarantee/GetPenaltyList.html
     *
     * @param  int  $offset  获取从第offset条开始的limit条记录（序号从 0 开始），最大不超过总记录数
     * @param  int  $limit  获取从第offset条开始的limit条记录（序号从 0 开始），最大不超过 100
     * @return array
     *
     * @throws Exception
     */
    public function getPenaltyList(int $offset, int $limit)
    {
        return $this->get('wxaapi/wxamptrade/get_penalty_list', [], [
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    /**
     * 获取交易保障标状态
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/transaction-guarantee/GetGuaranteeStatus.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function getGuaranteeStatus()
    {
        return $this->get('wxaapi/wxamptrade/get_guarantee_status');
    }
}
