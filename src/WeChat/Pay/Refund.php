<?php



namespace zxf\WeChat\Pay;

use zxf\WeChat\Contracts\BasicWePay;


use Exception;

/**
 * 微信商户退款
 * Class Refund
 * @package WePay
 */
class Refund extends BasicWePay
{

    /**
     * 创建退款订单
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function create(array $options)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        return $this->callPostApi($url, $options, true);
    }

    /**
     * 查询退款
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function query(array $options)
    {
        $url = "https://api.mch.weixin.qq.com/pay/refundquery";
        return $this->callPostApi($url, $options);
    }

    /**
     * 获取退款通知
     * @return array
     * @throws Exception
     */
    public function getNotify()
    {
        $data = Tools::xml2arr(file_get_contents("php://input"));
        if (!isset($data["return_code"]) || $data["return_code"] !== "SUCCESS") {
            throw new Exception("获取退款通知XML失败！");
        }
        try {
            $key = md5($this->config["mch_key"]);
            $decrypt = base64_decode($data["req_info"]);
            $response = openssl_decrypt($decrypt, "aes-256-ecb", $key, OPENSSL_RAW_DATA);
            $data["result"] = Tools::xml2arr($response);
            return $data;
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}