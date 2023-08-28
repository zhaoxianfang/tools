<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 客服消息处理
 */
class Custom extends WeChatBase
{
    public $useToken = true;

    /**
     * 添加客服帐号
     *
     * @link https://developers.weixin.qq.com/miniprogram/introduction/custom.html#%E6%B7%BB%E5%8A%A0%E5%AE%A2%E6%9C%8D%E8%B4%A6%E5%8F%B7
     *
     * @param string      $kf_wx       客服微信号
     * @param string|null $business_id 创建商户时得到的商户id
     *
     * @return array
     * @throws Exception
     */
    public function addAccount(string $kf_wx, ?string $business_id)
    {
        $data = ['kf_wx' => $kf_wx, 'business_id' => $business_id];
        return $this->post('customservice/kfaccount/add', $data);
    }

    /**
     * 修改客服帐号
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Service_Center_messages.html#2
     *
     * @param string      $kf_account 客服账号
     * @param string      $nickname   客服昵称
     * @param string|null $password   密码
     *
     * @return array
     * @throws Exception
     */
    public function updateAccount(string $kf_account, string $nickname, ?string $password)
    {
        $data = ['kf_account' => $kf_account, 'nickname' => $nickname, 'password' => $password];
        return $this->post('customservice/kfaccount/update', $data);
    }

    /**
     * 删除客服帐号
     *
     * @link https://developers.weixin.qq.com/miniprogram/introduction/custom.html#%E5%88%A0%E9%99%A4%E5%AE%A2%E6%9C%8D%E8%B4%A6%E5%8F%B7
     *
     * @param string $kf_openid
     *
     * @return array
     * @throws Exception
     */
    public function deleteAccount(string $kf_openid)
    {
        $params = ['kf_openid' => $kf_openid];
        return $this->get('customservice/kfaccount/del', [], $params);
    }

    /**
     * 邀请绑定客服帐号
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Customer_Service/Customer_Service_Management.html#3
     *
     * @param string $kf_account 完整客服帐号，格式为：帐号前缀@公众号微信号
     * @param string $invite_wx  接收绑定邀请的客服微信号
     *
     * @return array
     * @throws Exception
     */
    public function inviteWorker(string $kf_account, string $invite_wx)
    {
        return $this->post('customservice/kfaccount/inviteworker', ['kf_account' => $kf_account, 'invite_wx' => $invite_wx]);
    }

    /**
     * 获取所有客服账号
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Customer_Service/Customer_Service_Management.html#0
     *
     * @return array
     * @throws Exception
     */
    public function getAccountList()
    {
        return $this->get('cgi-bin/customservice/getkflist');
    }

    /**
     * 上传客服头像
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Customer_Service/Customer_Service_Management.html#5
     *
     * @param string $kf_account 完整客服账号，格式为：账号前缀@公众号微信号
     * @param string $imagePath  头像文件位置
     *
     * @return array
     * @throws Exception
     */
    public function uploadHeadimg(string $kf_account, string $imagePath)
    {
        return $this->httpUpload('customservice/kfaccount/uploadheadimg', $imagePath, ['kf_account' => $kf_account]);
    }

    /**
     * 客服接口-发消息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Service_Center_messages.html#%E5%AE%A2%E6%9C%8D%E6%8E%A5%E5%8F%A3-%E5%8F%91%E6%B6%88%E6%81%AF
     *
     * @param string      $touser     接收人openid
     * @param string      $msgtype    消息类型，文本为text，图片为image，语音为voice，视频消息为video，音乐消息为music，图文消息（点击跳转到外链）为news，图文消息（点击跳转到图文消息页面）为mpnews，卡券为wxcard，小程序为miniprogrampage
     * @param array       $content    消息内容，根据$msgtype不同而不同
     * @param string|null $kf_account 需要以某个客服账号来发消息
     *
     * @return array
     * @throws Exception
     */
    public function send(string $touser, string $msgtype, array $content, ?string $kf_account = null)
    {
        if (!in_array($msgtype, ['text', 'image', 'voice', 'video', 'music', 'news', 'mpnews', 'mpnewsarticle', 'msgmenu', 'wxcard', 'miniprogrampage'])) {
            return $this->error('消息类型错误');
        }
        $data = [
            'touser'  => $touser,
            'msgtype' => $msgtype,
            $msgtype  => $content,
        ];
        empty($kf_account) || $data['customservice'] = ['kf_account' => $kf_account];
        return $this->post('cgi-bin/message/custom/send', $data);
    }

    /**
     * 客服输入状态
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Service_Center_messages.html#%E5%AE%A2%E6%9C%8D%E8%BE%93%E5%85%A5%E7%8A%B6%E6%80%81
     *
     * @param string $openid  接收人，普通用户（openid）
     * @param string $command Typing:正在输入,CancelTyping:取消正在输入
     *
     * @return array
     * @throws Exception
     */
    public function typing(string $openid, string $command = 'Typing')
    {
        return $this->post('cgi-bin/message/custom/typing', ['touser' => $openid, 'command' => $command]);
    }

    /**
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_2%EF%BC%89%E7%BE%A4%E5%8F%91%E6%8E%A5%E5%8F%A3%E6%96%B0%E5%A2%9E-send-ignore-reprint-%E5%8F%82%E6%95%B0
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function massSendAll(array $data)
    {
        return $this->post('cgi-bin/message/mass/sendall', $data);
    }

    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_4%E3%80%81%E6%A0%B9%E6%8D%AEOpenID%E5%88%97%E8%A1%A8%E7%BE%A4%E5%8F%91%E3%80%90%E8%AE%A2%E9%98%85%E5%8F%B7%E4%B8%8D%E5%8F%AF%E7%94%A8%EF%BC%8C%E6%9C%8D%E5%8A%A1%E5%8F%B7%E8%AE%A4%E8%AF%81%E5%90%8E%E5%8F%AF%E7%94%A8%E3%80%91
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function massSend(array $data)
    {
        return $this->post('cgi-bin/message/mass/send', $data);
    }

    /**
     * 删除群发【订阅号与服务号认证后均可用】
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_5%E3%80%81%E5%88%A0%E9%99%A4%E7%BE%A4%E5%8F%91%E3%80%90%E8%AE%A2%E9%98%85%E5%8F%B7%E4%B8%8E%E6%9C%8D%E5%8A%A1%E5%8F%B7%E8%AE%A4%E8%AF%81%E5%90%8E%E5%9D%87%E5%8F%AF%E7%94%A8%E3%80%91
     *
     * @param int|null     $msg_id      发送出去的消息ID
     * @param null|integer $article_idx 要删除的文章在图文消息中的位置，第一篇编号为1，该字段不填或填0会删除全部文章
     * @param string|null  $url         要删除的文章url，当msg_id未指定时该参数才生效
     *
     * @return array
     * @throws Exception
     */
    public function massDelete(?int $msg_id, ?int $article_idx, ?string $url = null)
    {
        $data = ['msg_id' => $msg_id];
        is_null($article_idx) || $data['article_idx'] = $article_idx;
        is_null($url) || $data['url'] = $url;
        return $this->post('cgi-bin/message/mass/delete', $data);
    }

    /**
     * 预览接口【订阅号与服务号认证后均可用】
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_6%E3%80%81%E9%A2%84%E8%A7%88%E6%8E%A5%E5%8F%A3%E3%80%90%E8%AE%A2%E9%98%85%E5%8F%B7%E4%B8%8E%E6%9C%8D%E5%8A%A1%E5%8F%B7%E8%AE%A4%E8%AF%81%E5%90%8E%E5%9D%87%E5%8F%AF%E7%94%A8%E3%80%91
     *
     * @param string $touser
     * @param string $msgtype
     * @param array  $content
     *
     * @return array
     * @throws Exception
     */
    public function massPreview(string $touser, string $msgtype, array $content)
    {
        $data = [
            'touser'  => $touser,
            'msgtype' => $msgtype,
            $msgtype  => $content,
        ];
        return $this->post('cgi-bin/message/mass/preview', $data);
    }

    /**
     * 查询群发消息发送状态【订阅号与服务号认证后均可用】
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_7%E3%80%81%E6%9F%A5%E8%AF%A2%E7%BE%A4%E5%8F%91%E6%B6%88%E6%81%AF%E5%8F%91%E9%80%81%E7%8A%B6%E6%80%81%E3%80%90%E8%AE%A2%E9%98%85%E5%8F%B7%E4%B8%8E%E6%9C%8D%E5%8A%A1%E5%8F%B7%E8%AE%A4%E8%AF%81%E5%90%8E%E5%9D%87%E5%8F%AF%E7%94%A8%E3%80%91
     *
     * @param int $msg_id 群发消息后返回的消息id
     *
     * @return array
     * @throws Exception
     */
    public function massGet(int $msg_id)
    {
        return $this->post('cgi-bin/message/mass/get', ['msg_id' => $msg_id]);
    }

    /**
     * 获取群发速度
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_10%E3%80%81%E8%8E%B7%E5%8F%96%E7%BE%A4%E5%8F%91%E9%80%9F%E5%BA%A6
     *
     * @return array
     * @throws Exception
     */
    public function massGetSeed()
    {
        return $this->post('cgi-bin/message/mass/speed/get');
    }

    /**
     * 设置群发速度
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Batch_Sends_and_Originality_Checks.html#_11%E3%80%81%E8%AE%BE%E7%BD%AE%E7%BE%A4%E5%8F%91%E9%80%9F%E5%BA%A6
     *
     * @param integer $speed 群发速度的级别
     *                       0    80w/分钟
     *                       1    60w/分钟
     *                       2    45w/分钟
     *                       3    30w/分钟
     *                       4    10w/分钟
     *
     * @return array
     * @throws Exception
     */
    public function massSetSeed(int $speed)
    {
        return $this->post('cgi-bin/message/mass/speed/set', ['speed' => $speed]);
    }
}