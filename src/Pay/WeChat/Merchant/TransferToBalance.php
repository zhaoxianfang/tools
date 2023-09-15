<?php

namespace zxf\Pay\WeChat\Merchant;

use Exception;
use zxf\Pay\WeChat\WeChatPayBase;

/**
 * 商家转账到零钱（针对普通商户）
 *      请在商户平台-商家转账产品设置中开通产品权限
 *
 * @link https://pay.weixin.qq.com/wiki/doc/apiv3/open/pay/chapter4_3_4.shtml
 *       https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/initiate-batch-transfer.html
 */
class TransferToBalance extends WeChatPayBase
{
    public function checkMode()
    {
        if ($this->isService()) {
            $this->error('仅普通商户可以使用商家转账到零钱功能');
        }
    }

    /**
     * 发起商家转账到零钱
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/initiate-batch-transfer.html
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function initiateBatchTransfer(array $data = [])
    {
        $this->checkMode();
        $url = "v3/transfer/batches";
        $this->withHeaderSerial()->withRequestFields(['appid']);
        return $this->url($url)->post($data);
    }

    /**
     * 通过微信批次单号查询批次单
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/get-transfer-batch-by-no.html
     *
     * @param string $batch_id          【微信批次单号】 微信批次单号，微信商家转账系统返回的唯一标识
     * @param bool   $need_query_detail 【是否查询转账明细单】
     *                                  true-是；false-否，默认否。商户可选择是否查询指定状态的转账明细单，当转账批次单状态为“FINISHED”（已完成）时，才会返回满足条件的转账明细单
     * @param int    $offset            请求资源起始位置】
     *                                  该次请求资源的起始位置。返回的明细是按照设置的明细条数进行分页展示的，一次查询可能无法返回所有明细，我们使用该参数标识查询开始位置，默认值为0
     * @param int    $limit             【最大资源条数】 该次请求可返回的最大明细条数，最小20条，最大100条，不传则默认20条。不足20条按实际条数返回
     * @param string $detail_status     【明细状态】 WAIT_PAY: 待确认。待商户确认, 符合免密条件时, 系统会自动扭转为转账中
     *                                  ALL:全部。需要同时查询转账成功、失败和待确认的明细单
     *                                  SUCCESS:转账成功
     *                                  FAIL:转账失败。需要确认失败原因后，再决定是否重新发起对该笔明细单的转账（并非整个转账批次单）
     *
     * @return mixed
     */
    public function getTransferBatchByNo(string $batch_id = '', bool $need_query_detail = false, int $offset = 0, int $limit = 20, string $detail_status = '')
    {
        $this->checkMode();
        $url = "v3/transfer/batches/batch-id/{$batch_id}?need_query_detail={$need_query_detail}&offset={$offset}&limit={$limit}&detail_status={$detail_status}";
        return $this->url($url)->get();
    }

    /**
     * 通过商家批次单号查询批次单
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-batch/get-transfer-batch-by-out-no.html
     *
     * @param string $out_batch_no      商家批次单号】 商户系统内部的商家批次单号，在商户系统内部唯一
     * @param bool   $need_query_detail 【是否查询转账明细单】
     *                                  true-是；false-否，默认否。商户可选择是否查询指定状态的转账明细单，当转账批次单状态为“FINISHED”（已完成）时，才会返回满足条件的转账明细单
     * @param int    $offset            请求资源起始位置】
     *                                  该次请求资源的起始位置。返回的明细是按照设置的明细条数进行分页展示的，一次查询可能无法返回所有明细，我们使用该参数标识查询开始位置，默认值为0
     * @param int    $limit             【最大资源条数】 该次请求可返回的最大明细条数，最小20条，最大100条，不传则默认20条。不足20条按实际条数返回
     * @param string $detail_status     【明细状态】 WAIT_PAY: 待确认。待商户确认, 符合免密条件时, 系统会自动扭转为转账中
     *                                  ALL:全部。需要同时查询转账成功、失败和待确认的明细单
     *                                  SUCCESS:转账成功
     *                                  FAIL:转账失败。需要确认失败原因后，再决定是否重新发起对该笔明细单的转账（并非整个转账批次单）
     *
     * @return mixed
     */
    public function getTransferBatchByOutNo(string $out_batch_no = '', bool $need_query_detail = false, int $offset = 0, int $limit = 20, string $detail_status = '')
    {
        $this->checkMode();
        $url = "v3/transfer/batches/out-batch-no/{$out_batch_no}?need_query_detail={$need_query_detail}&offset={$offset}&limit={$limit}&detail_status={$detail_status}";
        return $this->url($url)->get();
    }

    /**
     * 通过微信明细单号查询明细单
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-no.html
     *
     * @param string $batch_id  【微信批次单号】 微信批次单号，微信商家转账系统返回的唯一标识
     * @param string $detail_id 【微信明细单号】 微信支付系统内部区分转账批次单下不同转账明细单的唯一标识
     *
     * @return mixed
     */
    public function getTransferDetailByNo(string $batch_id = '', string $detail_id = '')
    {
        $this->checkMode();
        $url = "v3/transfer/batches/batch-id/{$batch_id}/details/detail-id/{$detail_id}";
        return $this->url($url)->get();
    }

    /**
     * 通过商家明细单号查询明细单
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-out-no.html
     *
     * @param string $out_batch_no  【商家明细单号】 商户系统内部区分转账批次单下不同转账明细单的唯一标识
     * @param string $out_detail_no 【商家批次单号】 商户系统内部的商家批次单号，在商户系统内部唯一
     *
     * @return mixed
     */
    public function getTransferDetailByOuNo(string $out_batch_no = '', string $out_detail_no = '')
    {
        $this->checkMode();
        $url = "v3/transfer/batches/out-batch-no/{$out_batch_no}/details/out-detail-no/{$out_detail_no}";
        return $this->url($url)->get();
    }

    /**
     * 转账账单电子回单申请受理接口
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-signature/create-electronic-signature.html
     *
     * @param string $out_batch_no 【商家批次单号】 商户系统内部的商家批次单号，在商户系统内部唯一。需要电子回单的批次单号
     *
     * @return mixed
     * @throws Exception
     */
    public function createElectronicSignature(string $out_batch_no = '')
    {
        $this->checkMode();
        $url = "v3/transfer/bill-receipt";
        return $this->url($url)->body(['out_batch_no' => $out_batch_no])->post();
    }

    /**
     * 查询转账账单电子回单接口
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-signature/get-electronic-signature-by-out-no.html
     *
     * @param string $out_batch_no 【商家批次单号】 商户系统内部的商家批次单号，在商户系统内部唯一。需要电子回单的批次单号
     *
     * @return mixed
     * @throws Exception
     */
    public function getElectronicSignatureByOutNo(string $out_batch_no = '')
    {
        $this->checkMode();
        $url = "v3/transfer/bill-receipt/{$out_batch_no}";
        return $this->url($url)->get();
    }

    /**
     * 受理转账明细电子回单API
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-receipt-api/create-electronic-receipt.html
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function createElectronicReceipt(array $data = [])
    {
        $this->checkMode();
        $url = "v3/transfer-detail/electronic-receipts";
        return $this->url($url)->body($data)->post();
    }

    /**
     * 查询转账明细电子回单受理结果API
     *
     * @link https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/electronic-receipt-api/query-electronic-receipt.html
     *
     * @param string $out_detail_no 【商家转账明细单号】 该单号为商户申请转账时生成的商家转账明细单号。 1.受理类型为BATCH_TRANSFER时填写商家批量转账明细单号。2.
     *                              受理类型为TRANSFER_TO_POCKET或TRANSFER_TO_BANK时填写商家转账单号。
     * @param string $accept_type   【受理类型】 电子回单受理类型：BATCH_TRANSFER：批量转账明细电子回单 TRANSFER_TO_POCKET：企业付款至零钱电子回单
     *                              TRANSFER_TO_BANK：企业付款至银行卡电子回单 可选取值： BATCH_TRANSFER: 批量转账明细电子回单 TRANSFER_TO_POCKET:
     *                              企业付款至零钱电子回单 TRANSFER_TO_BANK: 企业付款至银行卡电子回单
     * @param string $out_batch_no  【商家转账批次单号】
     *                              需要电子回单的批量转账明细单所在的转账批次的单号，该单号为商户申请转账时生成的商户单号。受理类型为BATCH_TRANSFER时该单号必填，否则该单号留空。
     *
     * @return mixed
     */
    public function queryElectronicReceipt(string $out_detail_no = '', string $accept_type = '', string $out_batch_no = '')
    {
        $this->checkMode();
        $url  = "v3/transfer-detail/electronic-receipts";
        $data = [
            'out_detail_no' => $out_detail_no,
            'accept_type'   => $accept_type,
        ];
        $out_batch_no && $data['out_batch_no'] = $out_batch_no;
        return $this->url($url)->body($data)->get();
    }
}