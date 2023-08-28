<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信粉丝管理
 */
class User extends WeChatBase
{
    public $useToken = true;

    /**
     * 设置用户备注名
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Configuring_user_notes.html
     *
     * @param string $openid
     * @param string $remark
     *
     * @return array
     * @throws Exception
     */
    public function updateMark(string $openid, string $remark)
    {
        return $this->post('cgi-bin/user/info/updateremark', ['openid' => $openid, 'remark' => $remark]);
    }

    /**
     * 获取用户基本信息（包括UnionID机制）
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
     *
     * @param string $openid
     * @param string $lang
     *
     * @return array
     * @throws Exception
     */
    public function getUserInfo(string $openid, string $lang = 'zh_CN')
    {
        return $this->get('cgi-bin/user/info', [], ['openid' => $openid, 'lang' => $lang]);
    }

    /**
     * 批量获取用户基本信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
     *
     * @param array  $openids
     * @param string $lang
     *
     * @return array
     * @throws Exception
     */
    public function getBatchUserInfo(array $openids, string $lang = 'zh_CN')
    {
        $data = ['user_list' => []];
        foreach ($openids as $openid) {
            $data['user_list'][] = ['openid' => $openid, 'lang' => $lang];
        }

        return $this->post('cgi-bin/user/info/batchget', $data);
    }

    /**
     * 获取用户列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Getting_a_User_List.html
     *
     * @param string $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     *
     * @return array
     * @throws Exception
     */
    public function getUserList($next_openid = '')
    {
        return $this->get('cgi-bin/user/get', [], ['next_openid' => $next_openid]);
    }

    /**
     * 获取标签下粉丝列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param integer $tagid       标签ID
     * @param string  $next_openid 第一个拉取的OPENID
     *
     * @return array
     * @throws Exception
     */
    public function getUserListByTag(int $tagid, string $next_openid = '')
    {
        return $this->post('cgi-bin/user/tag/get', ['tagid' => $tagid, 'next_openid' => $next_openid]);
    }

    /**
     * 获取公众号的黑名单列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Manage_blacklist.html
     *
     * @param string|null $begin_openid 当 begin_openid 为空时，默认从开头拉取。
     *
     * @return array
     * @throws Exception
     */
    public function getBlackList(?string $begin_openid = '')
    {
        return $this->post('cgi-bin/tags/members/getblacklist', ['begin_openid' => $begin_openid]);
    }

    /**
     * 批量拉黑用户
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Manage_blacklist.html
     *
     * @param array $openids
     *
     * @return array
     * @throws Exception
     */
    public function batchBlackList(array $openids)
    {
        return $this->post('cgi-bin/tags/members/batchblacklist', ['openid_list' => $openids]);
    }

    /**
     * 批量取消拉黑用户
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/Manage_blacklist.html
     *
     * @param array $openids
     *
     * @return array
     * @throws Exception
     */
    public function batchUnblackList(array $openids)
    {
        return $this->post('cgi-bin/tags/members/batchunblacklist', ['openid_list' => $openids]);
    }
}