<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;
use zxf\WeChat\MiniProgram\crypt\WXBizDataCrypt;

/**
 * 数据加密处理
 */
class Crypt extends WeChatBase
{
    public $useToken = true;

    /**
     * 数据签名校验
     *
     * @param string $iv
     * @param string $sessionKey
     * @param string $encryptedData
     *
     * @return bool|array
     */
    public function decode(string $iv, string $sessionKey, string $encryptedData)
    {
        $pc      = new WXBizDataCrypt($this->config->get('appid'), $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode == 0) {
            return json_decode($data, true);
        }
        return false;
    }

    /**
     * 小程序登录 - 登录凭证校验
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
     *
     * @param string $code 登录时获取的 code
     *
     * @return array
     * @throws Exception
     */
    public function session(string $code)
    {
        $appid  = $this->config->get('appid');
        $secret = $this->config->get('appsecret');
        return $this->get('sns/jscode2session', [], ['appid' => $appid, 'secret' => $secret, 'js_code' => $code, 'grant_type' => 'authorization_code']);
    }

    /**
     * 换取用户信息
     *
     * @param string $code          用户登录凭证（有效期五分钟）
     * @param string $iv            加密算法的初始向量
     * @param string $encryptedData 加密数据( encryptedData )
     *
     * @return array
     * @throws Exception
     */
    public function userInfo(string $code, string $iv, string $encryptedData)
    {
        $result = $this->session($code);
        if (empty($result['session_key'])) {
            $this->error('Code 换取 SessionKey 失败', 403);
        }
        $userinfo = $this->decode($iv, $result['session_key'], $encryptedData);
        if (empty($userinfo)) {
            $this->error('用户信息解析失败', 403);
        }
        return array_merge($result, $userinfo);
    }

    /**
     * 用户支付完成后，获取该用户的 UnionId
     *
     * @param string      $openid         支付用户唯一标识
     * @param null|string $transaction_id 微信支付订单号
     * @param null|string $mch_id         微信支付分配的商户号，和商户订单号配合使用
     * @param null|string $out_trade_no   微信支付商户订单号，和商户号配合使用
     *
     * @return array
     * @throws Exception
     */
    public function getPaidUnionId(string $openid, ?string $transaction_id = null, ?string $mch_id = null, ?string $out_trade_no = null)
    {
        $options = ['openid' => $openid];
        is_null($mch_id) || $options['mch_id'] = $mch_id;
        is_null($out_trade_no) || $options['out_trade_no'] = $out_trade_no;
        is_null($transaction_id) || $options['transaction_id'] = $transaction_id;

        return $this->get('wxa/getpaidunionid', [], $options);
    }
}