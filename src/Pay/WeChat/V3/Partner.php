<?php

namespace zxf\Pay\WeChat\V3;

use Exception;
use zxf\Pay\WeChat\WeChatPayBase;

/**
 * 商户进件>特约商户进件
 * 微信服务商/合作伙伴特有
 * 服务商（银行、支付机构、电商平台不可用）
 */
class Partner extends WeChatPayBase
{

    public function __construct(string $connectionName = 'default')
    {
        parent::__construct($connectionName);
        if (!$this->isService()) {
            $this->error('当前模式不是服务商/合作伙伴模式');
        }
        $this->withHeaderSerial();
    }

    /**
     * 特约商户进件（提交申请单）
     *     返回申请单号 applyment_id
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter11_1_1.shtml
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function applyMent(array $data = [])
    {
        $url = "v3/applyment4sub/applyment/";
        return $this->url($url)->body($data)->post();
    }

    /**
     * 查询申请单状态API > 通过业务申请编号查询申请状态
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter11_1_2.shtml
     *
     * @param string $business_code
     *                             1、只能由数字、字母或下划线组成，建议前缀为服务商商户号。
     *                             2、服务商自定义的唯一编号。
     *                             3、每个编号对应一个申请单，每个申请单审核通过后生成一个微信支付商户号。
     *                             4、若申请单被驳回，可填写相同的“业务申请编号”，即可覆盖修改原申请单信息。
     *                             示例值：1900013511_10000
     *
     * @return mixed
     */
    public function getStatusByCode(string $business_code = '')
    {
        $url = "v3/applyment4sub/applyment/business_code/{$business_code}";
        return $this->url($url)->get();
    }


    /**
     * 查询申请单状态API > 通过申请单号查询申请状态
     *
     * @link https://pay.weixin.qq.com/wiki/doc/apiv3_partner/apis/chapter11_1_2.shtml
     *
     * @param string $applyment_id 微信支付分配的申请单号。    示例值：2000001234567890
     *
     * @return mixed
     */
    public function getStatusById(string $applyment_id = '')
    {
        $url = "v3/applyment4sub/applyment/applyment_id/{$applyment_id}";
        return $this->url($url)->get();
    }

    /**
     * 修改结算账户
     *
     * @link https://pay.weixin.qq.com/docs/partner/apis/modify-settlement/sub-merchants/modify-settlement.html
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function modifySettlement(array $data = [])
    {
        $sub_mchid = $this->config['sub_mchid']; // 【特约商户/二级商户号】 请输入本服务商进件、已签约的特约商户/二级商户号。
        $url       = "v3/apply4sub/sub_merchants/{$sub_mchid}/modify-settlement";
        return $this->url($url)->body($data)->post();
    }

    /**
     * 查询结算账户
     *
     * @link https://pay.weixin.qq.com/docs/partner/apis/modify-settlement/sub-merchants/get-settlement.html
     *
     * @return mixed
     */
    public function getSettlement()
    {
        $sub_mchid = $this->config['sub_mchid']; // 【特约商户/二级商户号】 请输入本服务商进件、已签约的特约商户/二级商户号。
        $url       = "v3/apply4sub/sub_merchants/{$sub_mchid}/settlement";
        return $this->url($url)->get();
    }


    /**
     * 查询结算账户修改申请状态
     *
     * @link https://pay.weixin.qq.com/docs/partner/apis/modify-settlement/sub-merchants/get-application.html
     *
     * @param string $application_no 【修改结算账户申请单号】 提交二级商户修改结算账户申请后，由微信支付返回的单号，作为查询申请状态的唯一标识。
     *
     * @return mixed
     */
    public function getApplication(string $application_no = '')
    {
        $sub_mchid = $this->config['sub_mchid']; // 【特约商户/二级商户号】 请输入本服务商进件、已签约的特约商户/二级商户号。
        $url       = "v3/apply4sub/sub_merchants/{$sub_mchid}/application/{$application_no}";
        return $this->url($url)->get();
    }
}