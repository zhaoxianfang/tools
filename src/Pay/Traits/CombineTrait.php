<?php

namespace zxf\Pay\Traits;

/**
 * 合单支付
 */
trait CombineTrait
{

    protected bool $combine = false;// 是否为合单支付

    /**
     * 是否为合单支付
     */
    public function isCombine(): bool
    {
        return (bool)$this->combine;
    }

    /**
     * 设置为合单支付
     */
    public function useCombine(bool $combine = true): self
    {
        $this->combine = $combine;
        return $this;
    }
}