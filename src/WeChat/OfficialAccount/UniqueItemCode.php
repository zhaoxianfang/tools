<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;


/**
 * 一物一码
 *
 * @link https://developers.weixin.qq.com/doc/offiaccount/Unique_Item_Code/Unique_Item_Code_Op_Guide.html
 */
class UniqueItemCode extends WeChatBase
{
    public $useToken = true;

    /**
     * 申请二维码
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Unique_Item_Code/Unique_Item_Code_API_Documentation.html#4
     *
     * @param int    $code_count         申请码的数量
     * @param string $isv_application_id 外部单号
     *
     * @return string
     * @throws Exception
     */
    public function applyCode(int $code_count, string $isv_application_id)
    {
        $data = [
            'code_count'         => $code_count,
            'isv_application_id' => $isv_application_id,
        ];
        return $this->post('intp/marketcode/applycode', $data);
    }

    /**
     * 查询二维码申请单接口
     *
     * @param int    $code_count
     * @param string $isv_application_id
     *
     * @return string
     * @throws Exception
     */
    public function applyCodeQuery(int $code_count, string $isv_application_id)
    {
        $data = [
            'code_count'         => $code_count,
            'isv_application_id' => $isv_application_id,
        ];
        return $this->post('intp/marketcode/applycodequery', $data);
    }

    /**
     * 下载二维码包接口
     *
     * @param int $application_id 申请单号
     * @param int $code_start     开始位置
     * @param int $code_end       结束位置
     *
     * @return string
     * @throws Exception
     */
    public function applyCodeDownload(int $application_id, int $code_start, int $code_end)
    {
        $data = [
            'application_id' => $application_id,
            'code_start'     => $code_start,
            'code_end'       => $code_end,
        ];
        return $this->post('intp/marketcode/applycodedownload', $data);
    }

    /**
     * 激活二维码接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Unique_Item_Code/Unique_Item_Code_API_Documentation.html#7
     *
     * @param array $data
     *
     * @return string
     * @throws Exception
     */
    public function codeActive(array $data)
    {
        return $this->post('intp/marketcode/codeactive', $data);
    }

    /**
     * 查询二维码激活状态接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Unique_Item_Code/Unique_Item_Code_API_Documentation.html#8
     *
     * @param int|null    $application_id 申请单号    Uint64    N    无
     * @param int|null    $code_index     该码在批次中的偏移量    Uint64    N    传入application_id时必填
     * @param string|null $code_url       28位普通码字符    String128    N    code与code_url二选一
     * @param string|null $code           九位的字符串原始码    String16    N    code与code_url二选一
     *
     * @return string
     * @throws Exception
     */
    public function codeActiveQuery(?int $application_id, ?int $code_index, ?string $code_url, ?string $code)
    {
        $data = [
            'application_id' => $application_id,
            'code_index'     => $code_index,
            'code_url'       => $code_url,
            'code'           => $code,
        ];
        return $this->post('intp/marketcode/codeactivequery', $data);
    }

    /**
     * code_ticket换code接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Unique_Item_Code/Unique_Item_Code_API_Documentation.html#9
     *
     * @param string $openid      用户的openid
     * @param string $code_ticket 跳转时带上的code_ticket参数
     *
     * @return string
     * @throws Exception
     */
    public function ticketToCode(string $openid, string $code_ticket)
    {
        $data = [
            'openid'      => $openid,
            'code_ticket' => $code_ticket,
        ];
        return $this->post('intp/marketcode/tickettocode', $data);
    }
}