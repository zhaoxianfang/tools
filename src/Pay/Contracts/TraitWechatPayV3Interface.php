<?php

namespace zxf\Pay\Contracts;

interface TraitWechatPayV3Interface
{
    /**
     * 是否为合单支付
     */
    public function isCombine(): bool;
}