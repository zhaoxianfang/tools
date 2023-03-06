<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;


/**
 * 客服消息处理 ok
 */
class Custom extends WeChatBase
{
    /**
     * 添加客服帐号
     *
     * @param string $kf_account 客服账号
     * @param string $nickname   客服昵称
     *
     * @return array
     * @throws Exception
     */
    public function addAccount($kf_account, $nickname, $password)
    {
        $data = [
            "kf_account" => $kf_account,
            "nickname"   => $nickname,
            "password"   => $password,
        ];

        return $this->post("customservice/kfaccount/add", $data);
    }

    /**
     * 修改客服帐号
     *
     * @param string $kf_account 客服账号
     * @param string $nickname   客服昵称
     *
     * @return array
     * @throws Exception
     */
    public function updateAccount($kf_account, $nickname, $password)
    {
        $data = [
            "kf_account" => $kf_account,
            "nickname"   => $nickname,
            "password"   => $password,
        ];

        return $this->post("customservice/kfaccount/update", $data);
    }

    /**
     * 删除客服帐号
     *
     * @param string $kf_account 客服账号
     *
     * @return array
     * @throws Exception
     */
    public function deleteAccount($kf_account)
    {
        $data = ["kf_account" => $kf_account];

        return $this->post("customservice/kfaccount/del", $data);
    }

    /**
     * 邀请绑定客服帐号
     *
     * @param string $kf_account 完整客服帐号，格式为：帐号前缀@公众号微信号
     * @param string $invite_wx  接收绑定邀请的客服微信号
     *
     * @return array
     * @throws Exception
     */
    public function inviteWorker($kf_account, $invite_wx)
    {
        return $this->post("customservice/kfaccount/inviteworker", ["kf_account" => $kf_account, "invite_wx" => $invite_wx]);
    }

    /**
     * 获取所有客服账号
     *
     * @return array
     * @throws Exception
     */
    public function getAccountList()
    {
        return $this->get("cgi-bin/customservice/getkflist");
    }

    /**
     * 设置客服帐号的头像
     *
     * @param string $kf_account 客户账号
     * @param string $image      头像文件位置
     *
     * @return array
     * @throws Exception
     */
    public function uploadHeadimg($kf_account, $image)
    {
        return $this->customUpload("customservice/kfaccount/uploadheadimg", $image, ["kf_account" => $kf_account]);
    }

    /**
     * 客服接口-发消息
     *
     * @param string $toUser 消息接收人
     * @param        $content
     * @param        $msgType
     * @param        $isKf
     *
     * @return mixed
     * @throws Exception
     */
    public function send(string $toUser, $content, $msgType = 'text', $isKf = false)
    {
        $data           = [
            'touser'  => $toUser,
            'msgtype' => $msgType,
        ];
        $data[$msgType] = $content;
        if ($isKf) {
            // 如果是客服发送的消息
            $data['customservice'] = ['kf_account' => $content];
        }
        return $this->post("cgi-bin/message/custom/send", $data);
    }

    /**
     * 客服输入状态
     *
     * @param string $openid  普通用户（openid）
     * @param string $command Typing:正在输入,CancelTyping:取消正在输入
     *
     * @return array
     * @throws Exception
     */
    public function typing(string $openid, string $command = "Typing")
    {
        return $this->post("cgi-bin/message/custom/typing", ["touser" => $openid, "command" => $command]);
    }

    /**
     * 根据标签进行群发【订阅号与服务号认证后均可用】
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function massSendAll(array $data)
    {
        return $this->post("cgi-bin/message/mass/sendall", $data);
    }

    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function massSend(array $data)
    {
        return $this->post("cgi-bin/message/mass/send", $data);
    }

    /**
     * 删除群发【订阅号与服务号认证后均可用】
     *
     * @param integer      $msg_id      发送出去的消息ID
     * @param null|integer $article_idx 要删除的文章在图文消息中的位置，第一篇编号为1，该字段不填或填0会删除全部文章
     *
     * @return array
     * @throws Exception
     */
    public function massDelete($msg_id, $article_idx = null)
    {
        $data = ["msg_id" => $msg_id];
        is_null($article_idx) || $data["article_idx"] = $article_idx;

        return $this->post("cgi-bin/message/mass/delete", $data);
    }

    /**
     * 预览接口【订阅号与服务号认证后均可用】
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function massPreview(array $data)
    {
        return $this->post("cgi-bin/message/mass/preview", $data);
    }

    /**
     * 查询群发消息发送状态【订阅号与服务号认证后均可用】
     *
     * @param integer $msg_id 群发消息后返回的消息id
     *
     * @return array
     * @throws Exception
     */
    public function massGet($msg_id)
    {
        return $this->post("cgi-bin/message/mass/get", ["msg_id" => $msg_id]);
    }

    /**
     * 获取群发速度
     *
     * @return array
     * @throws Exception
     */
    public function massGetSeed()
    {
        return $this->post("cgi-bin/message/mass/speed/get", []);
    }

    /**
     * 设置群发速度
     *
     * @param integer $speed 群发速度的级别 群发速度的级别，是一个0到4的整数，数字越大表示群发速度越慢。
     *
     * @return array
     * @throws Exception
     */
    public function massSetSeed($speed = 0)
    {
        return $this->post("cgi-bin/message/mass/speed/set", ["speed" => $speed]);
    }


}