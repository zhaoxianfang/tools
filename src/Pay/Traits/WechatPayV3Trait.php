<?php

namespace zxf\Pay\Traits;

use Exception;

trait WechatPayV3Trait
{
    // 每个 use WechatPayV3Trait 的类都必须定义这个属性
    // protected $driverName = 'jsapi';// jsapi,app,h5,native // JSAPI 和小程序使用 的都是 jsapi

    /**
     * 创建支付订单|预下单
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_1.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_1.shtml
     */
    public function preOrder(array $data = [])
    {
        $url = $this->isService() ? "v3/pay/partner/transactions/{$this->driverName}" : "v3/pay/transactions/{$this->driverName}";

        $this->withRequestFields($this->isService() ? ['sp_appid', 'sp_mchid', 'notify_url'] : ['appid', 'mchid', 'notify_url']);
        if ($this->isCombine()) {
            $this->withRequestFields(['combine_appid', 'combine_mchid', 'notify_url']);
            $url = "v3/combine-transactions/{$this->driverName}"; // 合单支付
        }

        return $this->url($url)->body($data)->post();
    }

    /**
     * 微信支付订单号查询
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_2.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_2.shtml
     */
    public function queryPay(string $transaction_id = '')
    {
        if ($this->isCombine()) {
            $this->error('合单支付不支持微信支付订单号查询');
        }
        $this->isService() ? $this->withRequestFields(['sp_mchid', 'sub_mchid']) : $this->withRequestFields(['mchid']);
        return $this->url($this->isService() ? "v3/pay/partner/transactions/id/{$transaction_id}" : "v3/pay/transactions/id/{$transaction_id}")->get();
    }

    /**
     * 商户订单号查询
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_2.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_2.shtml
     */
    public function queryPayByOutTradeNo(string $out_trade_no = '')
    {
        $url = $this->isService() ? "v3/pay/partner/transactions/out-trade-no/{$out_trade_no}" : "v3/pay/transactions/out-trade-no/{$out_trade_no}";
        $this->isService() ? $this->withRequestFields(['sp_mchid', 'sub_mchid']) : $this->withRequestFields(['mchid']);
        if ($this->isCombine()) {
            $this->withRequestFields([]);
            $url = "v3/combine-transactions/out-trade-no/{$out_trade_no}";
        }

        return $this->url($url)->get();
    }

    /**
     * 关闭订单
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_3.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_3.shtml
     */
    public function close(string $out_trade_no = '', ?array $data = [])
    {
        $url = $this->isService() ? "v3/pay/partner/transactions/out-trade-no/{$out_trade_no}/close" : "v3/pay/transactions/out-trade-no/{$out_trade_no}/close";
        $this->isService() ? $this->withRequestFields(['sp_mchid', 'sub_mchid']) : $this->withRequestFields(['mchid']);
        if ($this->isCombine()) {
            $this->withRequestFields(['combine_appid']);
            $url = "v3/combine-transactions/out-trade-no/{$out_trade_no}/close";
        }
        return $this->url($url)->body($data)->post();
    }

    /**
     * 发起支付
     * (获取 $this->driverName 支付参数)
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_4.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_4.shtml
     */
    public function pay(?string $prepay_id = '')
    {
        throw new Exception('请重写 pay 方法');
    }

    /**
     * 支付回调
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_5.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_5.shtml
     */
    public function payed(\Closure $func)
    {
        $callbackData = $this->parseCallbackData();
        $res          = $func($callbackData);// 判断闭包返回数据 true or false
        http_response_code(200);
        ob_clean(); // 清空缓冲区
        echo $res ? json_encode([
            "code"    => "SUCCESS",
            "message" => "成功",
        ]) : json_encode([
            "code"    => "FAIL",
            "message" => "失败",
        ]);
        flush(); // 强制输出到浏览器
        die;
    }

    /**
     * 退款回调
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_11.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_11.shtml
     */
    public function refunded(\Closure $func)
    {
        $this->payed($func);
    }

    /**
     * 发起退款|申请退款
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_9.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_9.shtml
     */
    public function refund(array $data = [])
    {
        $this->withRequestFields(['notify_url']);
        return $this->url('v3/refund/domestic/refunds')->body($data)->post();
    }

    /**
     * 查询单笔退款单
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_10.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_10.shtml
     */
    public function queryRefund(string $out_refund_no = '')
    {
        $this->isService() && $this->withRequestFields(['sub_mchid']);
        return $this->url("v3/refund/domestic/refunds/{$out_refund_no}")->get();
    }


    /**
     * 查询交易账单
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_6.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_6.shtml
     *
     * @param array  $data    查询参数
     * @param string $saveDir 保存目录
     *
     * @return string
     */
    public function queryBill(array $data = [], string $saveDir = '')
    {
        $res = $this->url("v3/bill/tradebill")->body($data)->get();
        if (!empty($res['download_url'])) {
            return $this->downloadBill($res['download_url'], $saveDir);
        }
        return $res;
    }

    // 输出CSV到浏览器
    protected function toBrowser($content)
    {
        $appId = $this->isService() ? $this->config['sub_mchid'] : $this->config['mchid'];
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $appId . '_' . date('YmdHis') . '.csv');
        header('Cache-Control: max-age=0');
        echo $content;
        die;
    }

    /**
     * 查询资金账单
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_7.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_7.shtml
     */
    public function queryFlowBill(array $data = [], string $saveDir = '')
    {
        $res = $this->url("v3/bill/fundflowbill")->body($data)->get();
        if (!empty($res['download_url'])) {
            return $this->downloadBill($res['download_url'], $saveDir);
        }
    }

    /**
     * 下载账单
     * 通过申请账单接口获取到“download_url”，URL有效期30s
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_8.shtml
     *       https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_8.shtml
     */
    public function downloadBill(string $download_url = '', string $saveDir = '')
    {
        $content = $this->url($download_url)->body([])->get();
        if (!empty($saveDir)) {
            is_dir($saveDir) || mkdir($saveDir, 0755, true);
            $appId    = $this->isService() ? $this->config['sub_mchid'] : $this->config['mchid'];
            $fileName = $saveDir . DIRECTORY_SEPARATOR . $appId . '_' . date('YmdHis') . '.csv';
            file_put_contents($fileName, $content);
            return $fileName;
        } else {
            $this->toBrowser($content);
        }
    }

    /**
     * 申请单个子商户资金账单API
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter4_1_12.shtml
     */
    public function subMerchantFundflowbill(array $data = [])
    {
        if (!$this->isService()) {
            throw new Exception('普通商户模式不支持申请单个子商户资金账单');
        }
        return $this->url("v3/bill/sub-merchant-fundflowbill")->body($data)->get();
    }
}