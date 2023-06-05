<?php

// +----------------------------------------------------------------------
// | WeChatDeveloper
// +----------------------------------------------------------------------

namespace zxf\WeChat\WePayV3;

use zxf\WeChat\WePayV3\Contracts\BasicWePay;

/**
 * 普通商户商家分账
 * Class Profitsharing
 * @package WePayV3
 */
class ProfitSharing extends BasicWePay
{
    /**
     * 请求分账
     * @param array $options
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function create($options)
    {
        $options['appid'] = $this->config['appid'];
        return $this->doRequest('POST', '/v3/profitsharing/orders', json_encode($options, JSON_UNESCAPED_UNICODE), true);
    }


    /**
     * 查询分账结果
     * @param string $outOrderNo 商户分账单号
     * @param string $transactionId 微信订单号
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function query($outOrderNo, $transactionId)
    {
        $pathinfo = "/v3/profitsharing/orders/{$outOrderNo}?&transaction_id={$transactionId}";
        return $this->doRequest('GET', $pathinfo, '', true);
    }

    /**
     * 解冻剩余资金
     * @param array $options
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function unfreeze($options)
    {
        return $this->doRequest('POST', '/v3/profitsharing/orders/unfreeze', json_encode($options, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 查询剩余待分金额
     * @param string $transactionId 微信订单号
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function amounts($transactionId)
    {
        $pathinfo = "/v3/profitsharing/transactions/{$transactionId}/amounts";
        return $this->doRequest('GET', $pathinfo, '', true);
    }

    /**
     * 添加分账接收方
     * @param array $options
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function addReceiver($options)
    {
        $options['appid'] = $this->config['appid'];
        return $this->doRequest('POST', "/v3/profitsharing/receivers/add", json_encode($options, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 删除分账接收方
     * @param array $options
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function deleteReceiver($options)
    {
        $options['appid'] = $this->config['appid'];
        return $this->doRequest('POST', "/v3/profitsharing/receivers/delete", json_encode($options, JSON_UNESCAPED_UNICODE), true);
    }
}
