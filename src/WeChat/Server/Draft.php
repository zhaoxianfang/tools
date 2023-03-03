<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信草稿箱管理
 * Class Draft
 *
 * @author  taoxin
 * @package WeChat
 */
class Draft extends WeChatBase
{
    /**
     * 新建草稿
     *
     * @param $articles
     *
     * @return array
     * @throws Exception
     */
    public function add($articles)
    {
        return $this->post("cgi-bin/draft/add", ['articles' => $articles]);
    }

    /**
     * 获取草稿
     *
     * @param string $media_id
     * @param string $outType 返回处理函数
     *
     * @return array
     * @throws Exception
     */
    public function getDraft($media_id, $outType = null)
    {
        return $this->post("cgi-bin/draft/get", ['media_id' => $media_id]);
    }


    /**
     * 删除草稿
     *
     * @param string $media_id
     *
     * @return array
     * @throws Exception
     */
    public function delete($media_id)
    {
        return $this->post("cgi-bin/draft/delete", ['media_id' => $media_id]);
    }

    /**
     * 新增图文素材
     *
     * @param array $data 文件名称
     *
     * @return array
     * @throws Exception
     */
    public function addNews($data)
    {
        return $this->post("cgi-bin/material/add_news", $data);
    }

    /**
     * 修改草稿
     *
     * @param string $media_id 要修改的图文消息的id
     * @param int    $index    要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param        $articles
     *
     * @return array
     * @throws Exception
     */
    public function update($media_id, $index, $articles)
    {
        $data = ['media_id' => $media_id, 'index' => $index, 'articles' => $articles];

        return $this->post("cgi-bin/draft/update", $data);
    }

    /**
     * 获取草稿总数
     *
     * @return array
     * @throws Exception
     */
    public function getCount()
    {
        return $this->get("cgi-bin/draft/count");
    }

    /**
     * 获取草稿列表
     *
     * @param int $offset     从全部素材的该偏移位置开始返回，0表示从第一个素材返回
     * @param int $count      返回素材的数量，取值在1到20之间
     * @param int $no_content 1 表示不返回 content 字段，0 表示正常返回，默认为 0
     *
     * @return array
     * @throws Exception
     */
    public function batchGet($offset = 0, $count = 20, $no_content = 0)
    {
        return $this->post("cgi-bin/draft/batchget", ['no_content' => $no_content, 'offset' => $offset, 'count' => $count]);
    }

}
