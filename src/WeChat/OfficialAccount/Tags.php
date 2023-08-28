<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

class Tags extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取粉丝标签列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     */
    public function getTags()
    {
        return $this->get('cgi-bin/tags/get');
    }

    /**
     * 创建粉丝标签
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param string $name
     *
     * @return array
     * @throws Exception
     */
    public function createTags(string $name)
    {
        return $this->post('cgi-bin/tags/create', ['tag' => ['name' => $name]]);
    }

    /**
     * 更新粉丝标签
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param int    $id   标签ID
     * @param string $name 标签名称
     *
     * @return array
     * @throws Exception
     */
    public function updateTags(int $id, string $name)
    {
        return $this->post('cgi-bin/tags/update', ['tag' => ['name' => $name, 'id' => $id]]);
    }

    /**
     * 删除粉丝标签
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param int $tagId
     *
     * @return array
     * @throws Exception
     */
    public function deleteTags(int $tagId)
    {
        return $this->post('cgi-bin/tags/delete', ['tag' => ['id' => $tagId]]);
    }

    /**
     * 获取标签下粉丝列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param int    $tagid       标签ID
     * @param string $next_openid 第一个拉取的OPENID
     *
     * @return array
     * @throws Exception
     */
    public function getUserListByTag(int $tagid, string $next_openid = '')
    {
        return $this->post('cgi-bin/user/tag/get', ['tagid' => $tagid, 'next_openid' => $next_openid]);
    }

    /**
     * 批量为用户打标签
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param array $openids
     * @param int   $tagId
     *
     * @return array
     * @throws Exception
     */
    public function batchTagging(array $openids, int $tagId)
    {
        return $this->post('cgi-bin/tags/members/batchtagging', ['openid_list' => $openids, 'tagid' => $tagId]);
    }

    /**
     * 批量为用户取消标签
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param array $openids
     * @param int   $tagId
     *
     * @return array
     * @throws Exception
     */
    public function batchUntagging(array $openids, int $tagId)
    {
        return $this->post('cgi-bin/tags/members/batchuntagging', ['openid_list' => $openids, 'tagid' => $tagId]);
    }

    /**
     * 获取用户身上的标签列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/User_Management/User_Tag_Management.html
     *
     * @param string $openid
     *
     * @return array
     * @throws Exception
     */
    public function getUserTagId($openid)
    {
        return $this->post('cgi-bin/tags/getidlist', ['openid' => $openid]);
    }
}