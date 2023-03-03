<?php

namespace zxf\WeChat\Pay;

use zxf\WeChat\Contracts\BasicWePay;

use Exception;

/**
 * 微信商户账单及评论
 * Class Bill
 * @package WePay
 */
class Bill extends BasicWePay
{
    /**
     * 下载对账单
     * @param array $options 静音参数
     * @param null|string $outType 输出类型
     * @return bool|string
     * @throws Exception
     */
    public function download(array $options, $outType = null)
    {
        $this->params->set('sign_type', 'MD5');
        $params = $this->params->merge($options);
        $params['sign'] = $this->getPaySign($params, 'MD5');
        $result = Tools::post('https://api.mch.weixin.qq.com/pay/downloadbill', Tools::arr2xml($params));
        if (is_array($jsonData = Tools::xml3arr($result))) {
            if ($jsonData['return_code'] !== 'SUCCESS') {
                throw new Exception($jsonData['return_msg'], '0');
            }
        }
        return is_null($outType) ? $result : $outType($result);
    }


    /**
     * 拉取订单评价数据
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function comment(array $options)
    {
        $url = 'https://api.mch.weixin.qq.com/billcommentsp/batchquerycomment';
        return $this->callPostApi($url, $options, true);
    }
}