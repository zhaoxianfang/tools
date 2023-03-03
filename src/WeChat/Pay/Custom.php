<?php



namespace zxf\WeChat\Pay;

use zxf\WeChat\Contracts\BasicWePay;

/**
 * 微信扩展上报海关
 * Class Custom
 * @package WePay
 */
class Custom extends BasicWePay
{

    /**
     * 订单附加信息提交接口
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function add(array $options = [])
    {
        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/customs/customdeclareorder';
        return $this->callPostApi($url, $options, false, 'MD5', false, false);
    }

    /**
     * 订单附加信息查询接口
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function get(array $options = [])
    {
        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/customs/customdeclarequery';
        return $this->callPostApi($url, $options, false, 'MD5', true, false);
    }


    /**
     * 订单附加信息重推接口
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function reset(array $options = [])
    {
        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/newcustoms/customdeclareredeclare';
        return $this->callPostApi($url, $options, false, 'MD5', true, false);
    }

}