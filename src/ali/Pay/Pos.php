<?php

namespace zxf\ali\Pay;

use zxf\ali\Pay\Contracts\BasicAliPay;

/**
 * 支付宝刷卡支付
 * Class Pos
 * @package AliPay
 */
class Pos extends BasicAliPay
{
    /**
     * Pos constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options->set('method', 'alipay.trade.pay');
        $this->params->set('product_code', 'FACE_TO_FACE_PAYMENT');
    }

    /**
     * 创建数据操作
     * @param array $options
     * @return array|bool
     * @throws \Exception
     */
    public function apply($options)
    {
        return $this->getResult($options);
    }
}