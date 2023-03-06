<?php

namespace zxf\ali\Pay;

use Exception;
use zxf\ali\Pay\Contracts\BasicAliPay;

/**
 * 支付宝电子面单下载
 */
class Bill extends BasicAliPay
{
    /**
     * Bill constructor.
     *
     * @param array $options
     *
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options->set('method', 'alipay.data.dataservice.bill.downloadurl.query');
    }

    /**
     * 创建数据操作
     *
     * @param array $options
     *
     * @return array|bool
     * @throws Exception
     */
    public function apply($options)
    {
        return $this->getResult($options);
    }
}