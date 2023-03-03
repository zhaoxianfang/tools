<?php



namespace zxf\WeChat\Pay;

use zxf\WeChat\Contracts\BasicWePay;

/**
 * 微信商户打款到零钱
 * Class Transfers
 * @package WePay
 */
class Transfers extends BasicWePay
{

    /**
     * 企业付款到零钱
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function create(array $options)
    {
        $this->params->offsetUnset('appid');
        $this->params->offsetUnset('mch_id');
        $this->params->set('mchid', $this->config['mch_id']);
        $this->params->set('mch_appid', $this->config['appid']);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        return $this->callPostApi($url, $options, true, 'MD5', false);
    }

    /**
     * 查询企业付款到零钱
     * @param string $partnerTradeNo 商户调用企业付款API时使用的商户订单号
     * @return array
     * @throws Exception
     */
    public function query($partnerTradeNo)
    {
        $this->params->offsetUnset('mchid');
        $this->params->offsetUnset('mch_appid');
        $this->params->set('appid', $this->config['appid']);
        $this->params->set('mch_id', $this->config['mch_id']);
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        return $this->callPostApi($url, ['partner_trade_no' => $partnerTradeNo], true, 'MD5', false);
    }

}