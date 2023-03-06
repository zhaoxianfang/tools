<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 卡券管理
 * Class Card
 *
 * @package WeChat
 */
class Card extends WeChatBase
{
    /**
     * 创建卡券
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function create(array $data)
    {
        return $this->post("card/create", $data);
    }

    /**
     * 设置买单接口
     *
     * @param string $card_id
     * @param bool   $is_open
     *
     * @return array
     * @throws Exception
     */
    public function setPaycell($card_id, $is_open = true)
    {
        return $this->post("card/paycell/set", ["card_id" => $card_id, "is_open" => $is_open]);
    }

    /**
     * 设置自助核销接口
     *
     * @param string $card_id
     * @param bool   $is_open
     *
     * @return array
     * @throws Exception
     */
    public function setConsumeCell($card_id, $is_open = true)
    {
        return $this->post("card/selfconsumecell/set", ["card_id" => $card_id, "is_open" => $is_open]);
    }

    /**
     * 创建二维码接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function createQrc(array $data)
    {
        return $this->post("card/qrcode/create", $data);
    }

    /**
     * 创建货架接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function createLandingPage(array $data)
    {
        return $this->post("card/landingpage/create", $data);
    }

    /**
     * 导入自定义code
     *
     * @param string $card_id
     * @param array  $code
     *
     * @return array
     * @throws Exception
     */
    public function deposit($card_id, array $code)
    {
        return $this->post("card/code/deposit", ["card_id" => $card_id, "code" => $code]);
    }

    /**
     * 查询导入code数目
     *
     * @param string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function getDepositCount($card_id)
    {
        return $this->post("card/code/getdepositcount", ["card_id" => $card_id]);
    }

    /**
     * 核查code接口
     *
     * @param string $card_id 进行导入code的卡券ID
     * @param array  $code    已经微信卡券后台的自定义code，上限为100个
     *
     * @return array
     * @throws Exception
     */
    public function checkCode($card_id, array $code)
    {
        return $this->post("card/code/checkcode", ["card_id" => $card_id, "code" => $code]);
    }

    /**
     * 图文消息群发卡券
     *
     * @param string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function getNewsHtml($card_id)
    {
        return $this->post("card/mpnews/gethtml", ["card_id" => $card_id]);
    }

    /**
     * 设置测试白名单
     *
     * @param array $openids
     * @param array $usernames
     *
     * @return array
     * @throws Exception
     */
    public function setTestWhiteList($openids = [], $usernames = [])
    {
        return $this->post("card/testwhitelist/set", ["openid" => $openids, "username" => $usernames]);
    }

    /**
     * 线下核销查询Code
     *
     * @param string $code          单张卡券的唯一标准
     * @param string $card_id       卡券ID代表一类卡券。自定义code卡券必填
     * @param bool   $check_consume 是否校验code核销状态，填入true和false时的code异常状态返回数据不同
     *
     * @return array
     * @throws Exception
     */
    public function getCode($code, $card_id = null, $check_consume = null)
    {
        $data = ["code" => $code];
        is_null($card_id) || $data["card_id"] = $card_id;
        is_null($check_consume) || $data["check_consume"] = $check_consume;

        return $this->post("card/code/get", $data);
    }

    /**
     * 线下核销核销Code
     *
     * @param string $code    需核销的Code码
     * @param null   $card_id 券ID。创建卡券时use_custom_code填写true时必填。非自定义Code不必填写
     *
     * @return array
     * @throws Exception
     */
    public function consume($code, $card_id = null)
    {
        $data = ["code" => $code];
        is_null($card_id) || $data["card_id"] = $card_id;

        return $this->post("card/code/consume", $data);
    }

    /**
     * Code解码接口
     *
     * @param string $encrypt_code
     *
     * @return array
     * @throws Exception
     */
    public function decrypt($encrypt_code)
    {
        return $this->post("card/code/decrypt", ["encrypt_code" => $encrypt_code]);
    }

    /**
     * 获取用户已领取卡券接口
     *
     * @param string      $openid
     * @param null|string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function getCardList($openid, $card_id = null)
    {
        $data = ["openid" => $openid];
        is_null($card_id) || $data["card_id"] = $card_id;

        return $this->post("card/user/getcardlist", $data);
    }

    /**
     * 查看卡券详情
     *
     * @param string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function getCard($card_id)
    {
        return $this->post("card/get", ["card_id" => $card_id]);
    }

    /**
     * 批量查询卡券列表
     *
     * @param int   $offset      查询卡列表的起始偏移量，从0开始，即offset: 5是指从从列表里的第六个开始读取
     * @param int   $count       需要查询的卡片的数量（数量最大50）
     * @param array $status_list 支持开发者拉出指定状态的卡券列表
     *
     * @return array
     * @throws Exception
     */
    public function batchGet($offset, $count = 50, array $status_list = [])
    {
        $data = ["offset" => $offset, "count" => $count];
        empty($status_list) || $data["status_list"] = $status_list;
        return $this->post("card/batchget", $data);
    }

    /**
     * 更改卡券信息接口
     *
     * @param string $card_id
     * @param array  $member_card
     *
     * @return array
     * @throws Exception
     */
    public function updateCard($card_id, array $member_card)
    {
        return $this->post("card/update", ["card_id" => $card_id, "member_card" => $member_card]);
    }

    /**
     * 修改库存接口
     *
     * @param string       $card_id              卡券ID
     * @param null|integer $increase_stock_value 增加多少库存，支持不填或填0
     * @param null|integer $reduce_stock_value   减少多少库存，可以不填或填0
     *
     * @return array
     * @throws Exception
     */
    public function modifyStock($card_id, $increase_stock_value = null, $reduce_stock_value = null)
    {
        $data = ["card_id" => $card_id];
        is_null($increase_stock_value) || $data["increase_stock_value"] = $increase_stock_value;
        is_null($reduce_stock_value) || $data["reduce_stock_value"] = $reduce_stock_value;

        return $this->post("card/modifystock", $data);
    }

    /**
     * 更改Code接口
     *
     * @param string      $code     需变更的Code码
     * @param string      $new_code 变更后的有效Code码
     * @param null|string $card_id  卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function updateCode($code, $new_code, $card_id = null)
    {
        $data = ["code" => $code, "new_code" => $new_code];
        is_null($card_id) || $data["card_id"] = $card_id;

        return $this->post("card/code/update", $data);
    }

    /**
     * 删除卡券接口
     *
     * @param string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function deleteCard($card_id)
    {
        return $this->post("card/delete", ["card_id" => $card_id]);
    }

    /**
     * 设置卡券失效接口
     *
     * @param string      $code
     * @param string      $card_id
     * @param null|string $reason
     *
     * @return array
     * @throws Exception
     */
    public function unAvailable($code, $card_id, $reason = null)
    {
        $data = ["code" => $code, "card_id" => $card_id];
        is_null($reason) || $data["reason"] = $reason;
        return $this->post("card/code/unavailable", $data);
    }

    /**
     * 拉取卡券概况数据接口
     *
     * @param string $begin_date  查询数据的起始时间
     * @param string $end_date    查询数据的截至时间
     * @param string $cond_source 卡券来源(0为公众平台创建的卡券数据 1是API创建的卡券数据)
     *
     * @return array
     * @throws Exception
     */
    public function getCardBizuininfo($begin_date, $end_date, $cond_source)
    {
        $data = ["begin_date" => $begin_date, "end_date" => $end_date, "cond_source" => $cond_source];
        return $this->post("datacube/getcardbizuininfo", $data);
    }

    /**
     * 获取免费券数据接口
     *
     * @param string  $begin_date  查询数据的起始时间
     * @param string  $end_date    查询数据的截至时间
     * @param integer $cond_source 卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     * @param null    $card_id     卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function getCardCardinfo($begin_date, $end_date, $cond_source, $card_id = null)
    {
        $data = ["begin_date" => $begin_date, "end_date" => $end_date, "cond_source" => $cond_source];
        is_null($card_id) || $data["card_id"] = $card_id;

        return $this->post("datacube/getcardcardinfo", $data);
    }


    /**
     * 激活会员卡
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function activateMemberCard(array $data)
    {
        return $this->post("card/membercard/activate", $data);
    }

    /**
     * 设置开卡字段接口
     * 用户激活时需要填写的选项
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setActivateMemberCardUser(array $data)
    {
        return $this->post("card/membercard/activateuserform/set", $data);
    }

    /**
     * 获取用户提交资料
     * 根据activate_ticket获取到用户填写的信息
     *
     * @param string $activate_ticket
     *
     * @return array
     * @throws Exception
     */
    public function getActivateMemberCardTempinfo($activate_ticket)
    {
        return $this->post("card/membercard/activatetempinfo/get", ["activate_ticket" => $activate_ticket]);
    }

    /**
     * 更新会员信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateMemberCardUser(array $data)
    {
        return $this->post("card/membercard/updateuser", $data);
    }

    /**
     * 拉取会员卡概况数据接口
     *
     * @param string $begin_date  查询数据的起始时间
     * @param string $end_date    查询数据的截至时间
     * @param string $cond_source 卡券来源(0为公众平台创建的卡券数据 1是API创建的卡券数据)
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCardinfo($begin_date, $end_date, $cond_source)
    {
        $data = ["begin_date" => $begin_date, "end_date" => $end_date, "cond_source" => $cond_source];

        return $this->post("datacube/getcardmembercardinfo", $data);
    }

    /**
     * 拉取单张会员卡数据接口
     *
     * @param string $begin_date 查询数据的起始时间
     * @param string $end_date   查询数据的截至时间
     * @param string $card_id    卡券id
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCardDetail($begin_date, $end_date, $card_id)
    {
        $data = ["begin_date" => $begin_date, "end_date" => $end_date, "card_id" => $card_id];
        return $this->post("datacube/getcardmembercarddetail", $data);
    }

    /**
     * 拉取会员信息（积分查询）接口
     *
     * @param string $card_id 查询会员卡的cardid
     * @param string $code    所查询用户领取到的code值
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCard($card_id, $code)
    {
        $data = ["card_id" => $card_id, "code" => $code];
        return $this->post("card/membercard/userinfo/get", $data);
    }

    /**
     * 设置支付后投放卡券接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function payGiftCard(array $data)
    {
        return $this->post("card/paygiftcard/add", $data);
    }

    /**
     * 删除支付后投放卡券规则
     *
     * @param integer $rule_id 支付即会员的规则名称
     *
     * @return array
     * @throws Exception
     */
    public function delPayGiftCard($rule_id)
    {
        return $this->post("card/paygiftcard/delete", ["rule_id" => $rule_id]);
    }

    /**
     * 查询支付后投放卡券规则详情
     *
     * @param integer $rule_id 要查询规则id
     *
     * @return array
     * @throws Exception
     */
    public function getPayGiftCard($rule_id)
    {
        return $this->post("card/paygiftcard/getbyid", ["rule_id" => $rule_id]);
    }

    /**
     * 批量查询支付后投放卡券规则
     *
     * @param integer $offset    起始偏移量
     * @param integer $count     查询的数量
     * @param bool    $effective 是否仅查询生效的规则
     *
     * @return array
     * @throws Exception
     */
    public function batchGetPayGiftCard($offset = 0, $count = 10, $effective = true)
    {
        $data = ["type" => "RULE_TYPE_PAY_MEMBER_CARD", "offset" => $offset, "count" => $count, "effective" => $effective];
        return $this->post("card/paygiftcard/batchget", $data);
    }

    /**
     * 创建支付后领取立减金活动
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addActivity(array $data)
    {
        return $this->post("card/mkt/activity/create", $data);
    }

    /**
     * 开通券点账户接口
     *
     * @return array
     * @throws Exception
     */
    public function payActivate()
    {
        return $this->get("card/pay/activate");
    }

    /**
     * 对优惠券批价
     *
     * @param string  $card_id  需要来配置库存的card_id
     * @param integer $quantity 本次需要兑换的库存数目
     *
     * @return array
     * @throws Exception
     */
    public function getPayprice($card_id, $quantity)
    {
        return $this->post("card/pay/getpayprice", ["card_id" => $card_id, "quantity" => $quantity]);
    }

    /**
     * 查询券点余额接口
     *
     * @return array
     * @throws Exception
     */
    public function getCoinsInfo()
    {
        return $this->get("card/pay/getcoinsinfo");
    }

    /**
     * 确认兑换库存接口
     *
     * @param string  $card_id  需要来兑换库存的card_id
     * @param integer $quantity 本次需要兑换的库存数目
     * @param string  $order_id 仅可以使用上面得到的订单号，保证批价有效性
     *
     * @return array
     * @throws Exception
     */
    public function payConfirm($card_id, $quantity, $order_id)
    {
        $data = ["card_id" => $card_id, "quantity" => $quantity, "order_id" => $order_id];
        return $this->post("card/pay/confirm", $data);
    }

    /**
     * 充值券点接口
     *
     * @param integer $coin_count
     *
     * @return array
     * @throws Exception
     */
    public function payRecharge($coin_count)
    {
        return $this->post("card/pay/recharge", ["coin_count" => $coin_count]);
    }

    /**
     * 查询订单详情接口
     *
     * @param string $order_id
     *
     * @return array
     * @throws Exception
     */
    public function payGetOrder($order_id)
    {
        return $this->post("card/pay/getorder", ["order_id" => $order_id]);
    }

    /**
     * 查询券点流水详情接口
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function payGetList(array $data)
    {
        return $this->post("card/pay/getorderlist", $data);
    }

}