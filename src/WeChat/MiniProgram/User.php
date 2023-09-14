<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 用户信息
 */
class User extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 获取插件用户openpid
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/basic-info/getPluginOpenPId.html
     *
     * @param string $code 通过 wx.pluginLogin 获得的插件用户标志凭证 code，有效时间为5分钟，一个 code 只能获取一次 openpid。
     *
     * @return mixed
     * @throws Exception
     */
    public function getPluginOpenPId(string $code)
    {
        return $this->post('wxa/getpluginopenpid', [
            'login_code' => $code,
        ]);
    }

    /**
     * 检查加密信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/basic-info/checkEncryptedData.html
     *
     * @param string $encrypted_msg_hash 加密数据的sha256，通过Hex（Base16）编码后的字符串
     *
     * @return mixed
     * @throws Exception
     */
    public function checkEncryptedData(string $encrypted_msg_hash)
    {
        return $this->post('wxa/business/checkencryptedmsg', [
            'encrypted_msg_hash' => $encrypted_msg_hash,
        ]);
    }

    /**
     * 支付后获取 Unionid
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/basic-info/getPaidUnionid.html
     *
     * @param string      $openid         支付用户唯一标识
     * @param string      $transaction_id 微信支付订单号
     * @param string|null $mch_id         微信支付分配的商户号，和商户订单号配合使用
     * @param string|null $out_trade_no   微信支付商户订单号，和商户号配合使用
     *
     * @return mixed
     * @throws Exception
     */
    public function getPaidUnionid(string $openid, string $transaction_id, ?string $mch_id, ?string $out_trade_no)
    {
        return $this->get('wxa/getpaidunionid', [], [
            'openid'         => $openid,
            'transaction_id' => $transaction_id,
            'mch_id'         => $mch_id,
            'out_trade_no'   => $out_trade_no,
        ]);
    }

    /**
     * 获取用户encryptKey
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/internet/getUserEncryptKey.html
     *
     * @param string $openid     用户的openid
     * @param string $signature  用sessionkey对空字符串签名得到的结果。session_key可通过code2Session接口获得
     * @param string $sig_method 签名方法，只支持 hmac_sha256
     *
     * @return mixed
     * @throws Exception
     */
    public function getUserEncryptKey(string $openid, string $signature, string $sig_method)
    {
        return $this->post('wxa/business/getuserencryptkey', [
            'openid'     => $openid,
            'signature'  => $signature,
            'sig_method' => $sig_method,
        ]);
    }


    /**
     * 通过授权码换取手机号
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
     *
     * @param string $code 手机号获取凭证
     *                     https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
     *
     * @return array
     * @throws Exception
     */
    public function getPhoneNumber(string $code)
    {
        return $this->post('wxa/business/getuserphonenumber', ['code' => $code]);
    }
}