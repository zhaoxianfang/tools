<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信卡券
 */
class Card extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 创建微信卡券
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Create_a_Coupon_Voucher_or_Card.html#8
     *
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function create(array $data = [])
    {
        return $this->post('card/create', $data);
    }

    /**
     * 设置买单接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Create_a_Coupon_Voucher_or_Card.html#12
     *
     * @param string $card_id 卡券ID
     * @param bool   $is_open 是否开启买单功能，填true/false
     *
     * @return mixed
     * @throws Exception
     */
    public function setPaycell(string $card_id, bool $is_open = true)
    {
        return $this->post('card/paycell/set', ['card_id' => $card_id, 'is_open' => $is_open]);
    }

    /**
     * 设置自助核销接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Create_a_Coupon_Voucher_or_Card.html#15
     *
     * @param string $card_id            卡券ID
     * @param bool   $is_open            是否开启自助核销功能，填true/false，默认为false
     * @param bool   $need_verify_cod    用户核销时是否需要输入验证码， 填true/false， 默认为false
     * @param bool   $need_remark_amount 用户核销时是否需要备注核销金额， 填true/false， 默认为false
     *
     * @return array
     * @throws Exception
     */
    public function setConsumeCell(string $card_id, bool $is_open = false, bool $need_verify_cod = false, bool $need_remark_amount = false)
    {
        return $this->post('card/selfconsumecell/set', ['card_id' => $card_id, 'is_open' => $is_open, 'need_verify_cod' => $need_verify_cod, 'need_remark_amount' => $need_remark_amount]);
    }

    /**
     * 创建二维码接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Create_a_membership_card.html#8
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#0
     */
    public function createQrc(array $data)
    {
        return $this->post('card/qrcode/create', $data);
    }

    /**
     * 创建货架接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#3
     *
     * @param string $banner    页面的banner图片链接，须调用，建议尺寸为640*300。    是
     * @param string $title     页面的title。    是
     *
     * @param bool   $can_share 页面是否可以分享,填入true/false    是
     * @param string $scene     投放页面的场景值； SCENE_NEAR_BY 附近 SCENE_MENU 自定义菜单 SCENE_QRCODE 二维码 SCENE_ARTICLE 公众号文章
     *                          SCENE_H5 h5页面 SCENE_IVR 自动回复 SCENE_CARD_CUSTOM_CELL 卡券自定义cell    是
     * @param array  $card_list 卡券列表，每个item有两个字段    是
     * @param string $thumb_url 缩略图url
     *
     * @return mixed
     * @throws Exception
     */
    public function createLandingPage(string $banner, string $title, bool $can_share, string $scene, array $card_list, string $thumb_url)
    {
        $data = [
            'banner'    => $banner,
            'title'     => $title,
            'can_share' => $can_share,
            'scene'     => $scene,
            'card_list' => $card_list,
            'thumb_url' => $thumb_url,
        ];
        return $this->post('card/landingpage/create', $data);
    }

    /**
     * 导入自定义code(仅对自定义code商户)
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#5
     *
     * @param string $card_id 需要进行导入code的卡券ID。
     * @param array  $code    需导入微信卡券后台的自定义code，上限为100个。
     *
     * @return array
     * @throws Exception
     */
    public function deposit(string $card_id, array $code)
    {
        return $this->post('card/code/deposit', ['card_id' => $card_id, 'code' => $code]);
    }

    /**
     * 查询导入code数目接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#_4-1-%E5%AF%BC%E5%85%A5%E8%87%AA%E5%AE%9A%E4%B9%89code-%E4%BB%85%E5%AF%B9%E8%87%AA%E5%AE%9A%E4%B9%89code%E5%95%86%E6%88%B7
     *
     * @param string $card_id 进行导入code的卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function getDepositCount($card_id)
    {
        return $this->post('card/code/getdepositcount', ['card_id' => $card_id]);
    }

    /**
     * 核查code接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#_4-1-%E5%AF%BC%E5%85%A5%E8%87%AA%E5%AE%9A%E4%B9%89code-%E4%BB%85%E5%AF%B9%E8%87%AA%E5%AE%9A%E4%B9%89code%E5%95%86%E6%88%B7
     *
     * @param string $card_id 进行导入code的卡券ID
     * @param array  $code    已经微信卡券后台的自定义code，上限为100个
     *
     * @return array
     * @throws Exception
     */
    public function checkCode(string $card_id, array $code)
    {
        return $this->post('card/code/checkcode', ['card_id' => $card_id, 'code' => $code]);
    }

    /**
     * 图文消息群发卡券
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#6
     *
     * @param string|null $card_id 卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function getNewsHtml(?string $card_id = '')
    {
        return $this->post('card/mpnews/gethtml', $card_id ? ['card_id' => $card_id] : []);
    }

    /**
     * 设置测试白名单
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Distributing_Coupons_Vouchers_and_Cards.html#12
     *
     * @param array|null $openids   测试的openid列表
     * @param array|null $usernames 测试的微信号列表。
     *
     * @return array
     * @throws Exception
     */
    public function setTestWhiteList(?array $openids = [], ?array $usernames = [])
    {
        return $this->post('card/testwhitelist/set', ['openid' => $openids, 'username' => $usernames]);
    }

    /**
     * 线下核销查询Code
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Redeeming_a_coupon_voucher_or_card.html#1
     *
     * @param string      $code          单张卡券的唯一标准
     * @param string|null $card_id       卡券ID代表一类卡券。自定义code卡券必填
     * @param bool|null   $check_consume 是否校验code核销状态，填入true和false时的code异常状态返回数据不同
     *
     * @return array
     * @throws Exception
     */
    public function getCode(string $code, ?string $card_id = null, ?bool $check_consume = null)
    {
        $data = ['code' => $code];
        $card_id && $data['card_id'] = $card_id;
        !is_null($check_consume) && $data['check_consume'] = $check_consume;
        return $this->post('card/code/get', $data);
    }

    /**
     * 线下核销核销Code
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Redeeming_a_coupon_voucher_or_card.html#2
     *
     * @param string      $code    需核销的Code码
     * @param string|null $card_id 券ID。创建卡券时use_custom_code填写true时必填。非自定义Code不必填写
     *
     * @return array
     * @throws Exception
     */
    public function consume(string $code, ?string $card_id = null)
    {
        $data = ['code' => $code];
        !is_null($card_id) && $data['card_id'] = $card_id;
        return $this->post('card/code/consume', $data);
    }

    /**
     * Code解码接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Redeeming_a_coupon_voucher_or_card.html#4
     *
     * @param string $encrypt_code 经过加密的Code码
     *
     * @return array
     * @throws Exception
     */
    public function decrypt(string $encrypt_code)
    {
        return $this->post('card/code/decrypt', ['encrypt_code' => $encrypt_code]);
    }

    /**
     * 获取用户已领取卡券接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#1
     *
     * @param string      $openid
     * @param null|string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function getCardList(string $openid, ?string $card_id = null)
    {
        $data = ['openid' => $openid];
        !is_null($card_id) && $data['card_id'] = $card_id;
        return $this->post('card/user/getcardlist', $data);
    }

    /**
     * 查看卡券详情
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#2
     *
     * @param string $card_id 卡券id
     *
     * @return array
     * @throws Exception
     */
    public function getCard(string $card_id)
    {
        return $this->post('card/get', ['card_id' => $card_id]);
    }

    /**
     * 批量查询卡券列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#3
     *
     * @param int        $offset      查询卡列表的起始偏移量，从0开始，即offset: 5是指从从列表里的第六个开始读取
     * @param int        $count       需要查询的卡片的数量（数量最大50）
     * @param array|null $status_list 支持开发者拉出指定状态的卡券列表
     *
     * @return array
     * @throws Exception
     */
    public function batchGet(int $offset = 0, int $count = 50, ?array $status_list = [])
    {
        $data = ['offset' => $offset, 'count' => $count];
        empty($status_list) || $data['status_list'] = $status_list;

        return $this->post('card/batchget', $data);
    }

    /**
     * 更改卡券信息接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#4
     *
     * @param string $card_id
     * @param array  $member_card
     *
     * @return array
     * @throws Exception
     */
    public function updateCard(string $card_id, array $member_card)
    {
        return $this->post('card/update', ['card_id' => $card_id, 'member_card' => $member_card]);
    }

    /**
     * 修改库存接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#5
     *
     * @param string       $card_id              卡券ID
     * @param null|integer $increase_stock_value 增加多少库存，支持不填或填0
     * @param null|integer $reduce_stock_value   减少多少库存，可以不填或填0
     *
     * @return array
     * @throws Exception
     */
    public function modifyStock($card_id, ?int $increase_stock_value = null, ?int $reduce_stock_value = null)
    {
        $data = ['card_id' => $card_id];
        is_null($increase_stock_value) || $data['increase_stock_value'] = $increase_stock_value;
        is_null($reduce_stock_value) || $data['reduce_stock_value'] = $reduce_stock_value;

        return $this->post('card/modifystock', $data);
    }

    /**
     * 更改Code接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#6
     *
     * @param string      $code     需变更的Code码
     * @param string      $new_code 变更后的有效Code码
     * @param null|string $card_id  卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function updateCode(string $code, string $new_code, ?string $card_id = null)
    {
        $data = ['code' => $code, 'new_code' => $new_code];
        is_null($card_id) || $data['card_id'] = $card_id;

        return $this->post('card/code/update', $data);
    }

    /**
     * 删除卡券接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#7
     *
     * @param string $card_id
     *
     * @return array
     * @throws Exception
     */
    public function deleteCard(string $card_id)
    {
        return $this->post('card/delete', ['card_id' => $card_id]);
    }

    /**
     * 设置卡券失效接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#8
     *
     * @param string      $code
     * @param string      $card_id
     * @param null|string $reason
     *
     * @return array
     * @throws Exception
     */
    public function unAvailable(string $code, string $card_id, ?string $reason = null)
    {
        $data = ['code' => $code, 'card_id' => $card_id];
        is_null($reason) || $data['reason'] = $reason;
        return $this->post('card/code/unavailable', $data);
    }

    /**
     * 拉取卡券概况数据接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#9
     *
     * @param string $begin_date  查询数据的起始时间
     * @param string $end_date    查询数据的截至时间
     * @param int    $cond_source 卡券来源(0为公众平台创建的卡券数据 1是API创建的卡券数据)
     *
     * @return array
     * @throws Exception
     */
    public function getCardBizuininfo(string $begin_date, string $end_date, int $cond_source)
    {
        $data = ['begin_date' => $begin_date, 'end_date' => $end_date, 'cond_source' => $cond_source];
        return $this->post('datacube/getcardbizuininfo', $data);
    }

    /**
     * 获取免费券数据接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#11
     *
     * @param string      $begin_date  查询数据的起始时间
     * @param string      $end_date    查询数据的截至时间
     * @param integer     $cond_source 卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     * @param string|null $card_id     卡券ID
     *
     * @return array
     * @throws Exception
     */
    public function getCardCardinfo(string $begin_date, string $end_date, int $cond_source, ?string $card_id = null)
    {
        $data = ['begin_date' => $begin_date, 'end_date' => $end_date, 'cond_source' => $cond_source];
        is_null($card_id) || $data['card_id'] = $card_id;
        return $this->post('datacube/getcardcardinfo', $data);
    }


    /**
     * 激活会员卡
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Create_a_membership_card.html#15
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function activateMemberCard(array $data)
    {
        $data = [
            'membership_number'        => $data['membership_number'], //	是	string(20)	会员卡编号，由开发者填入，作为序列号显示在用户的卡包里。可与Code码保持等值。
            'code'                     => $data['code'], //	是	string(20)	领取会员卡用户获得的code
            'card_id'                  => $data['card_id'] ?? '',//	否	string（32）	卡券ID,自定义code卡券必填
            'background_pic_url'       => $data['background_pic_url'] ?? '',//	否	string（128）	商家自定义会员卡背景图，须 先调用 上传图片接口 将背景图上传至CDN，否则报错， 卡面设计请遵循 微信会员卡自定义背景设计规范
            'activate_begin_time'      => $data['activate_begin_time'] ?? '',//	否	unsigned int	激活后的有效起始时间。若不填写默认以创建时的 data_info 为准。Unix时间戳格式。
            'activate_end_time'        => $data['activate_end_time'] ?? '',//	否	unsigned int	激活后的有效截至时间。若不填写默认以创建时的 data_info 为准。Unix时间戳格式。
            'init_bonus'               => $data['init_bonus'] ?? '',//	否	int	初始积分，不填为0。
            'init_bonus_record'        => $data['init_bonus_record'] ?? '', //	否	string(32)	积分同步说明。
            'init_balance'             => $data['init_balance'] ?? '',//	否	int	初始余额，不填为0。
            'init_custom_field_value1' => $data['init_custom_field_value1'] ?? '',//	否	string（12）	创建时字段custom_field1定义类型的初始值，限制为4个汉字，12字节。
            'init_custom_field_value2' => $data['init_custom_field_value2'] ?? '',//	否	string（12）	创建时字段custom_field2定义类型的初始值，限制为4个汉字，12字节。
            'init_custom_field_value3' => $data['init_custom_field_value3'] ?? '',//	否	string（12）	创建时字段custom_field3定义类型的初始值，限制为4个汉字，12字节。
        ];
        return $this->post('card/membercard/activate', $data);
    }

    /**
     * 设置开卡字段接口
     * 用户激活时需要填写的选项
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Create_a_membership_card.html#16
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setActivateMemberCardUser(array $data)
    {
        $data = [
            'card_id'              => $data['card_id'],//	是	string(32)	卡券ID。
            'required_form'        => $data['required_form'] ?? '',//	否	JSON结构	会员卡激活时的必填选项。
            'optional_form'        => $data['optional_form'] ?? '',//	否	JSON结构	会员卡激活时的选填项。
            'can_modify'           => $data['can_modify'] ?? '',//	否	bool	当前结构（required_form或者optional_form ）内 的字段是否允许用户激活后再次修改，商户设置为true 时，需要接收相应事件通知处理修改事件
            'common_field_id_list' => $data['common_field_id_list'] ?? '',//	否	arry	微信格式化的选项类型。见以下列表。
            'custom_field_list'    => $data['custom_field_list'] ?? '',//否	arry	自定义选项名称，开发者可 以分别在必填和选填中至多定义五个自定义选项
            'rich_field_list'      => $data['rich_field_list'] ?? '',//	否	arry	自定义富文本类型，包含以下三个字段，开发者可 以分别在必填和选填中至多定义五个自定义选项
            'type'                 => $data['type'] ?? '',//	否	string( 21 )	富文本类型 FORM_FIELD_RADIO 自定义单选 FORM_FIELD_SELECT 自定义选择项 FORM_FIELD_CHECK_BOX 自定义多选
            'values'               => $data['values'] ?? '',//	否	arry	选择项
            'service_statement'    => $data['service_statement'] ?? '',//否	JSON结构	服务声明，用于放置商户会员卡守 则
            'name'                 => $data['name'] ?? '',//	否	string( 21 )	会员声明字段名称 / 链接名称 /字段名
            'url'                  => $data['url'] ?? '',//	否	string(128)	自定义url 请填写http:// 或者https://开头的链接
            'bind_old_card'        => $data['bind_old_card'] ?? '',//否	JSON结构	绑定老会员链接
        ];
        return $this->post('card/membercard/activateuserform/set', $data);
    }

    /**
     * 获取用户提交资料
     * 根据activate_ticket获取到用户填写的信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Create_a_membership_card.html#16
     *
     *
     * @param string $activate_ticket
     *
     * @return array
     * @throws Exception
     */
    public function getActivateMemberCardTempinfo(string $activate_ticket)
    {
        return $this->post('card/membercard/activatetempinfo/get', ['activate_ticket' => $activate_ticket]);
    }

    /**
     * 更新会员信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Create_a_membership_card.html#18
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateMemberCardUser(array $data)
    {
        $data = [
            'code'                    => $data['code'],//	是	string(20)	1231123	卡券Code码。
            'card_id'                 => $data['card_id'],//	是	string（32）	p1Pj9jr90_SQ RaVqYI239Ka1erkI	卡券ID。
            'background_pic_url'      => $data['background_pic_url'] ?? '',//	否	string（128）	https://mmbiz.qlogo.cn/	支持商家激活时针对单个会员卡分配自定义的会员卡背景。
            'bonus'                   => $data['bonus'] ?? '',//	否	int	100	需要设置的积分全量值，传入的数值会直接显示
            'add_bonus'               => $data['add_bonus'] ?? '',//	否	int	100	本次积分变动值，传负数代表减少
            'record_bonus'            => $data['record_bonus'] ?? '',//	否	string(42)	消费30元，获得3积分	商家自定义积分消耗记录，不超过14个汉字
            'balance'                 => $data['balance'] ?? '',//	否	int	100	需要设置的余额全量值，传入的数值会直接显示在卡面
            'add_balance'             => $data['add_balance'] ?? '',//	否	int	100	本次余额变动值，传负数代表减少
            'record_balance'          => $data['record_balance'] ?? '',//	否	string(42)	购买焦糖玛 琪朵一杯，扣除金额30元。	商家自定义金额消耗记录，不超过14个汉字。
            'custom_field_value1'     => $data['custom_field_value1'] ?? '',//	否	string（12）	白金	创建时字段custom_field1定义类型的最新数值，限制为4个汉字，12字节。
            'custom_field_value2'     => $data['custom_field_value1'] ?? '',//	否	string（12）	8折	创建时字段custom_field2定义类型的最新数值，限制为4个汉字，12字节。
            'custom_field_value3'     => $data['custom_field_value3'] ?? '',//	否	string（12）	500	创建时字段custom_field3定义类型的最新数值，限制为4个汉字，12字节。
            'notify_optional'         => $data['notify_optional'] ?? '',//	否	JSON	--	控制原生消息结构体，包含各字段的消息控制字段
            'is_notify_bonus'         => $data['is_notify_bonus'] ?? '',//	否	bool	true	积分变动时是否触发系统模板消息，默认为true
            'is_notify_balance'       => $data['is_notify_balance'] ?? '',//否	bool	true	余额变动时是否触发系统模板消息，默认为true
            'is_notify_custom_field1' => $data['is_notify_custom_field1'] ?? '',//	否	bool	false	自定义group1变动时是否触发系统模板消息，默认为false。（2、3同理）
        ];
        return $this->post('card/membercard/updateuser', $data);
    }

    /**
     * 拉取会员卡概况数据接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#12
     *
     * @param string $begin_date  查询数据的起始时间
     * @param string $end_date    查询数据的截至时间
     * @param int    $cond_source 卡券来源(0为公众平台创建的卡券数据 1是API创建的卡券数据)
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCardinfo(string $begin_date, string $end_date, int $cond_source)
    {
        $data = ['begin_date' => $begin_date, 'end_date' => $end_date, 'cond_source' => $cond_source];
        return $this->post('datacube/getcardmembercardinfo', $data);
    }

    /**
     * 拉取单张会员卡数据接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Managing_Coupons_Vouchers_and_Cards.html#13
     *
     * @param string $begin_date 查询数据的起始时间
     * @param string $end_date   查询数据的截至时间
     * @param string $card_id    卡券id
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCardDetail(string $begin_date, string $end_date, string $card_id)
    {
        $data = ['begin_date' => $begin_date, 'end_date' => $end_date, 'card_id' => $card_id];
        return $this->post('datacube/getcardmembercarddetail', $data);
    }

    /**
     * 拉取会员信息（积分查询）接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Manage_Member_Card.html#1
     *
     * @param string $card_id 查询会员卡的cardid
     * @param string $code    所查询用户领取到的code值
     *
     * @return array
     * @throws Exception
     */
    public function getCardMemberCard(string $card_id, string $code)
    {
        $data = ['card_id' => $card_id, 'code' => $code];
        return $this->post('card/membercard/userinfo/get', $data);
    }

    /**
     * 设置支付后投放卡券接口
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Manage_Member_Card.html#4
     *
     * @param array $rule_info 支付后营销规则结构体
     *
     * @return array
     * @throws Exception
     */
    public function payGiftCard(array $rule_info)
    {
        return $this->post('card/paygiftcard/add', ['rule_info' => $rule_info]);
    }

    /**
     * 删除支付后投放卡券规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Manage_Member_Card.html#4
     *
     * @param int $rule_id 支付即会员的规则名称
     *
     * @return array
     * @throws Exception
     */
    public function delPayGiftCard(int $rule_id)
    {
        return $this->post('card/paygiftcard/delete', ['rule_id' => $rule_id]);
    }

    /**
     * 查询支付后投放卡券规则详情
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Manage_Member_Card.html#4
     *
     * @param int $rule_id 要查询规则id
     *
     * @return array
     * @throws Exception
     */
    public function getPayGiftCard(int $rule_id)
    {
        return $this->post('card/paygiftcard/getbyid', ['rule_id' => $rule_id]);
    }

    /**
     * 批量查询支付后投放卡券规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Cards_and_Offer/Membership_Cards/Manage_Member_Card.html#4
     *
     * @param integer $offset    起始偏移量
     * @param integer $count     查询的数量
     * @param bool    $effective 是否仅查询生效的规则
     *
     * @return array
     * @throws Exception
     */
    public function batchGetPayGiftCard(int $offset = 0, int $count = 10, bool $effective = true)
    {
        $data = ['type' => 'RULE_TYPE_PAY_MEMBER_CARD', 'offset' => $offset, 'count' => $count, 'effective' => $effective];

        return $this->post('card/paygiftcard/batchget', $data);
    }

    /**
     * 创建支付后领取立减金活动
     *
     * @param array $data
     *
     * TODO
     *
     * @return mixed
     * @throws Exception
     */
    public function addActivity(array $data)
    {
        $url = "https://api.weixin.qq.com/card/mkt/activity/create?access_token=ACCESS_TOKEN";

        return $this->post($url, $data);
    }

    /**
     * 开通券点账户接口
     *
     * TODO
     *
     */
    public function payActivate()
    {
        $url = "https://api.weixin.qq.com/card/pay/activate?access_token=ACCESS_TOKEN";

        return $this->get($url);
    }

    /**
     * 对优惠券批价
     *
     * @param string  $card_id  需要来配置库存的card_id
     * @param integer $quantity 本次需要兑换的库存数目
     *
     * @return array
     *
     * TODO
     *
     * @throws Exception
     */
    public function getPayprice($card_id, $quantity)
    {
        $url = "POST https://api.weixin.qq.com/card/pay/getpayprice?access_token=ACCESS_TOKEN";

        return $this->post($url, ['card_id' => $card_id, 'quantity' => $quantity]);
    }

    /**
     * 查询券点余额接口
     *TODO
     *
     * @return array
     *
     */
    public function getCoinsInfo()
    {
        $url = "https://api.weixin.qq.com/card/pay/getcoinsinfo?access_token=ACCESS_TOKEN";

        return $this->get($url);
    }

    /**
     * 确认兑换库存接口
     * TODO
     *
     * @param string  $card_id  需要来兑换库存的card_id
     * @param integer $quantity 本次需要兑换的库存数目
     * @param string  $order_id 仅可以使用上面得到的订单号，保证批价有效性
     *
     * @return array
     *
     */
    public function payConfirm($card_id, $quantity, $order_id)
    {
        $data = ['card_id' => $card_id, 'quantity' => $quantity, 'order_id' => $order_id];
        $url  = "https://api.weixin.qq.com/card/pay/confirm?access_token=ACCESS_TOKEN";

        return $this->post($url, $data);
    }

    /**
     * 充值券点接口
     * TODO
     *
     * @param integer $coin_count
     *
     * @return array
     *
     */
    public function payRecharge($coin_count)
    {
        $url = "https://api.weixin.qq.com/card/pay/recharge?access_token=ACCESS_TOKEN";

        return $this->post($url, ['coin_count' => $coin_count]);
    }

    /**
     * 查询订单详情接口
     * TODO
     *
     * @param string $order_id
     *
     * @return array
     *
     */
    public function payGetOrder($order_id)
    {
        $url = "https://api.weixin.qq.com/card/pay/getorder?access_token=ACCESS_TOKEN";

        return $this->post($url, ['order_id' => $order_id]);
    }

    /**
     * 查询券点流水详情接口
     * TODO
     *
     * @param array $data
     *
     * @return array
     *
     */
    public function payGetList(array $data)
    {
        $url = "https://api.weixin.qq.com/card/pay/getorderlist?access_token=ACCESS_TOKEN";

        return $this->post($url, $data);
    }

}