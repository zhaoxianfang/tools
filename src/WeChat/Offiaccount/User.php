<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 微信粉丝管理
 */
class User extends WeChatBase
{

    /**
     * 设置用户备注名
     *
     * @param string $openid
     * @param string $remark
     *
     * @return array
     * @throws Exception
     */
    public function updateMark($openid, $remark)
    {
        return $this->post("cgi-bin/user/info/updateremark", ["openid" => $openid, "remark" => $remark]);
    }

    /**
     * 获取用户基本信息（包括UnionID机制）
     *
     * @param string $openid
     * @param string $lang
     *
     * @return array
     * @throws Exception
     */
    public function getUserInfo($openid, $lang = "zh_CN")
    {
        return $this->get("cgi-bin/user/info", [], [
            "openid" => $openid,
            "lang"   => $lang,
        ]);
    }

    /**
     * 批量获取用户基本信息
     *
     * @param array  $openids
     * @param string $lang
     *
     * @return array
     * @throws Exception
     */
    public function getBatchUserInfo(array $openids, $lang = "zh_CN")
    {
        $data = ["user_list" => []];
        foreach ($openids as $openid) {
            $data["user_list"][] = ["openid" => $openid, "lang" => $lang];
        }
        return $this->post("cgi-bin/user/info/batchget", $data);
    }

    /**
     * 获取用户列表
     *
     * @param string $next_openid
     *
     * @return array
     * @throws Exception
     */
    public function getUserList($next_openid = "")
    {
        return $this->get("cgi-bin/user/get", [], [
            "next_openid" => $next_openid,
        ]);
    }

    /**
     * 获取标签下粉丝列表
     *
     * @param integer $tagid       标签ID
     * @param string  $next_openid 第一个拉取的OPENID
     *
     * @return array
     * @throws Exception
     */
    public function getUserListByTag($tagid, $next_openid = "")
    {
        return $this->post("cgi-bin/user/tag/get", ["tagid" => $tagid, "next_openid" => $next_openid]);
    }

    /**
     * 获取公众号的黑名单列表
     *
     * @param string $begin_openid
     *
     * @return array
     * @throws Exception
     */
    public function getBlackList($begin_openid = "")
    {
        return $this->post("cgi-bin/tags/members/getblacklist", ["begin_openid" => $begin_openid]);
    }

    /**
     * 批量拉黑用户
     *
     * @param array $openids
     *
     * @return array
     * @throws Exception
     */
    public function batchBlackList(array $openids)
    {
        return $this->post("cgi-bin/tags/members/batchblacklist", ["openid_list" => $openids]);
    }

    /**
     * 批量取消拉黑用户
     *
     * @param array $openids
     *
     * @return array
     * @throws Exception
     */
    public function batchUnblackList(array $openids)
    {
        return $this->post("cgi-bin/tags/members/batchunblacklist", ["openid_list" => $openids]);
    }

}