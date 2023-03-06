<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 用户标签管理
 */
class Tags extends WeChatBase
{
    /**
     * 获取粉丝标签列表
     *
     * @throws Exception
     */
    public function getTags()
    {
        return $this->get("cgi-bin/tags/get");
    }

    /**
     * 创建粉丝标签
     *
     * @param string $name
     *
     * @return array
     * @throws Exception
     */
    public function createTags($name)
    {
        return $this->post("cgi-bin/tags/create", ["tag" => ["name" => $name]]);
    }

    /**
     * 更新粉丝标签
     *
     * @param integer $id   标签ID
     * @param string  $name 标签名称
     *
     * @return array
     * @throws Exception
     */
    public function updateTags($id, $name)
    {
        return $this->post("cgi-bin/tags/update", ["tag" => ["name" => $name, "id" => $id]]);
    }

    /**
     * 删除粉丝标签
     *
     * @param int $tagId
     *
     * @return array
     * @throws Exception
     */
    public function deleteTags($tagId)
    {
        return $this->post("cgi-bin/tags/delete", ["tag" => ["id" => $tagId]]);
    }

    /**
     * 批量为用户打标签
     *
     * @param array   $openids
     * @param integer $tagId
     *
     * @return array
     * @throws Exception
     */
    public function batchTagging(array $openids, $tagId)
    {
        return $this->post("cgi-bin/tags/members/batchtagging", ["openid_list" => $openids, "tagid" => $tagId]);
    }

    /**
     * 批量为用户取消标签
     *
     * @param array   $openids
     * @param integer $tagId
     *
     * @return array
     * @throws Exception
     */
    public function batchUntagging(array $openids, $tagId)
    {
        return $this->post("cgi-bin/tags/members/batchuntagging", ["openid_list" => $openids, "tagid" => $tagId]);
    }

    /**
     * 获取用户身上的标签列表
     *
     * @param string $openid
     *
     * @return array
     * @throws Exception
     */
    public function getUserTagId($openid)
    {
        return $this->post("cgi-bin/tags/getidlist", ["openid" => $openid]);
    }
}