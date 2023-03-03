<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 发布能力
 * Class Freepublish
 *
 * @author  taoxin
 * @package WeChat
 */
class Freepublish extends WeChatBase
{
    /**
     * 发布接口
     * 开发者需要先将图文素材以草稿的形式保存（见“草稿箱/新建草稿”，如需从已保存的草稿中选择，见“草稿箱/获取草稿列表”）
     *
     * @param mixed $media_id 选择要发布的草稿的media_id
     *
     * @return array
     * @throws Exception
     */
    public function submit($media_id)
    {
        return $this->post("cgi-bin/freepublish/submit", ['media_id' => $media_id]);
    }

    /**
     * 发布状态轮询接口
     *
     * @param mixed $publish_id
     *
     * @return array
     * @throws Exception
     */
    public function getFreePublish($publish_id)
    {
        return $this->post("cgi-bin/freepublish/get", ['publish_id' => $publish_id]);
    }

    /**
     * 删除发布
     * 发布成功之后，随时可以通过该接口删除。此操作不可逆，请谨慎操作。
     *
     * @param mixed $article_id 成功发布时返回的 article_id
     * @param int   $index      要删除的文章在图文消息中的位置，第一篇编号为1，该字段不填或填0会删除全部文章
     *
     * @return array
     * @throws Exception
     */
    public function delete($article_id, $index = 0)
    {
        return $this->post("cgi-bin/freepublish/delete", ['article_id' => $article_id, 'index' => $index]);
    }

    /**
     * 通过 article_id 获取已发布文章
     *
     * @param mixed $article_id 要获取的草稿的article_id
     *
     * @return array
     * @throws Exception
     */
    public function getArticle($article_id)
    {
        return $this->post("cgi-bin/freepublish/getarticle", ['article_id' => $article_id]);
    }

    /**
     * 获取成功发布列表
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
        return $this->post("cgi-bin/freepublish/batchget", ['no_content' => $no_content, 'offset' => $offset, 'count' => $count]);
    }
}