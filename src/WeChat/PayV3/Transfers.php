<?php



namespace WePayV3;

use WePayV3\Contracts\BasicWePay;

/**
 * 普通商户商家转账到零钱
 * Class Transfers
 * @package WePayV3
 */
class Transfers extends BasicWePay
{
    /**
     * 发起商家批量转账
     * @param array $body
     * @return array
     * @throws Exception
     */
    public function batchs($body)
    {
        return $this->doRequest('POST', '/v3/transfer/batches', json_encode($body, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 通过微信批次单号查询批次单
     * @param string $batchId 微信批次单号(二选一)
     * @param string $outBatchNo 商家批次单号(二选一)
     * @param boolean $needQueryDetail 查询指定状态
     * @param integer $offset 请求资源的起始位置
     * @param integer $limit 最大明细条数
     * @param string $detailStatus 查询指定状态
     * @return array
     * @throws Exception
     */
    public function query($batchId = '', $outBatchNo = '', $needQueryDetail = true, $offset = 0, $limit = 20, $detailStatus = 'ALL')
    {
        if (empty($batchId)) {
            $pathinfo = "/v3/transfer/batches/out-batch-no/{$outBatchNo}";
        } else {
            $pathinfo = "/v3/transfer/batches/batch-id/{$batchId}";
        }
        $params = http_build_query([
            'limit'             => $limit,
            'offset'            => $offset,
            'detail_status'     => $detailStatus,
            'need_query_detail' => $needQueryDetail ? 'true' : 'false',
        ]);
        return $this->doRequest('GET', "{$pathinfo}?{$params}", '', true);
    }

    /**
     * 通过微信明细单号查询明细单
     * @param string $batchId 微信批次单号
     * @param string $detailId 微信支付系统内部区分转账批次单下不同转账明细单的唯一标识
     * @return array
     * @throws Exception
     */
    public function detailBatchId($batchId, $detailId)
    {
        $pathinfo = "/v3/transfer/batches/batch-id/{$batchId}/details/detail-id/{$detailId}";
        return $this->doRequest('GET', $pathinfo, '', true);
    }

    /**
     * 通过商家明细单号查询明细单
     * @param string $outBatchNo 商户系统内部的商家批次单号，在商户系统内部唯一
     * @param string $outDetailNo 商户系统内部区分转账批次单下不同转账明细单的唯一标识
     * @return array
     * @throws Exception
     */
    public function detailOutBatchNo($outBatchNo, $outDetailNo)
    {
        $pathinfo = "/v3/transfer/batches/out-batch-no/{$outBatchNo}/details/out-detail-no/{$outDetailNo}";
        return $this->doRequest('GET', $pathinfo, '', true);
    }
}
