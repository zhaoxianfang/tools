<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 微信草稿箱管理
 */
class Draft extends WeChatBase
{
    public $useToken = true;

    /**
     * 新建草稿
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Add_draft.html
     *
     * @param array $articles
     *
     * @return array
     * @throws Exception
     */
    public function add(array $articles)
    {
        return $this->post('cgi-bin/draft/add', ['articles' => $articles]);
    }

    /**
     * 获取草稿
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Get_draft.html
     *
     * @param string $media_id 要获取的草稿的media_id
     *
     * @return array
     * @throws Exception
     */
    public function getDraft(string $media_id)
    {
        return $this->post('cgi-bin/draft/get', ['media_id' => $media_id]);
    }


    /**
     * 删除草稿
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Delete_draft.html
     *
     * @param string $media_id 要删除的草稿的media_id
     *
     * @return array
     * @throws Exception
     */
    public function delete($media_id)
    {
        return $this->post('cgi-bin/draft/delete', ['media_id' => $media_id]);
    }

    /**
     * 修改草稿
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Update_draft.html
     *
     * @param string $media_id 要修改的图文消息的id
     * @param int    $index    要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param array  $articles
     *
     * @return array
     * @throws Exception
     */
    public function update(string $media_id, int $index, array $articles)
    {
        $data = ['media_id' => $media_id, 'index' => $index, 'articles' => $articles];
        return $this->post('cgi-bin/draft/update', $data);
    }


    /**
     * 获取草稿总数
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Count_drafts.html
     *
     * @return array
     * @throws Exception
     */
    public function getCount()
    {
        return $this->get('cgi-bin/draft/count');
    }

    /**
     * 获取草稿列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Draft_Box/Get_draft_list.html
     *
     * @param int      $offset     从全部素材的该偏移位置开始返回，0表示从第一个素材返回
     * @param int      $count      返回素材的数量，取值在1到20之间
     * @param int|null $no_content 1 表示不返回 content 字段，0 表示正常返回，默认为 0
     *
     * @return array
     * @throws Exception
     */
    public function batchGet(int $offset = 0, int $count = 20, ?int $no_content = 0)
    {
        return $this->post('cgi-bin/draft/batchget', ['no_content' => $no_content, 'offset' => $offset, 'count' => $count]);
    }
}