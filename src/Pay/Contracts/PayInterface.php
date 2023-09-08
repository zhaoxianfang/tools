<?php

namespace zxf\Pay\Contracts;

interface PayInterface
{
    /**
     * 创建支付订单|预下单
     */
    public function preOrder();

    /**
     * 微信支付订单号查询
     */
    public function queryPay();

    /**
     * 商户订单号查询
     */
    public function queryPayByOutTradeNo();

    /**
     * 关闭订单
     */
    public function close();

    /**
     * 发起支付
     */
    public function pay();

    /**
     * 支付回调
     */
    public function payed(\Closure $func);

    /**
     * 发起退款|申请退款
     */
    public function refund();

    /**
     * 退款回调
     */
    public function refunded(\Closure $func);

    /**
     * 查询退款单
     */
    public function queryRefund();

    /**
     * 查询交易账单
     */
    public function queryBill();

    /**
     * 查询资金账单
     */
    public function queryFlowBill();

    /**
     * 下载账单
     */
    public function downloadBill();

}