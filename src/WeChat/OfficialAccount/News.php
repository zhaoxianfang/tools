<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 图文消息留言管理
 *
 * @link https://developers.weixin.qq.com/doc/offiaccount/Comments_management/Image_Comments_Management_Interface.html
 */
class News extends WeChatBase
{
    public $useToken = true;

    /**
     * 新增永久素材（原接口有所改动）
     *
     * @param array $data 文件名称
     *
     * @return array
     * @throws Exception
     */
    public function addNews(array $data)
    {
        return $this->post('cgi-bin/material/add_news', $data);
    }

    /**
     * 更新图文素材
     *
     * @param string $media_id 要修改的图文消息的id
     * @param int    $index    要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param array  $news     文章内容
     *
     * @return array
     * @throws Exception
     */
    public function updateNews(string $media_id, int $index, array $news)
    {
        $data = ['media_id' => $media_id, 'index' => $index, 'articles' => $news];
        return $this->post('cgi-bin/material/update_news', $data);
    }

    /**
     * 打开已群发文章评论（新增接口）
     *
     * @param int $msg_data_id 群发返回的msg_data_id
     * @param int $index       多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function openComment(int $msg_data_id, int $index)
    {
        return $this->post('cgi-bin/comment/open', ['msg_data_id' => $msg_data_id, 'index' => $index]);
    }

    /**
     * 关闭已群发文章评论（新增接口）
     *
     * @param int $msg_data_id 群发返回的msg_data_id
     * @param int $index       多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function closeComment(int $msg_data_id, int $index)
    {
        return $this->post('cgi-bin/comment/close', ['msg_data_id' => $msg_data_id, 'index' => $index]);
    }

    /**
     * 查看指定文章的评论数据（新增接口）
     *
     * @param int      $msg_data_id 群发返回的msg_data_id
     * @param int      $begin       起始位置
     * @param int      $count       获取数目（>=50会被拒绝）
     * @param int      $type        type=0 普通评论&精选评论 type=1 普通评论 type=2 精选评论
     * @param int|null $index       多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function commentList(int $msg_data_id, int $begin, int $count, int $type, ?int $index)
    {
        return $this->post('cgi-bin/comment/list', ['msg_data_id' => $msg_data_id, 'index' => $index, 'begin' => $begin, 'count' => $count, 'type' => $type]);
    }

    /**
     * 将评论标记精选（新增接口）
     *
     * @param int      $msg_data_id     群发返回的msg_data_id
     * @param int      $user_comment_id 用户评论id
     * @param int|null $index           多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function markelectComment(int $msg_data_id, int $user_comment_id, ?int $index)
    {
        return $this->post('cgi-bin/comment/markelect', ['msg_data_id' => $msg_data_id, 'index' => $index, 'user_comment_id' => $user_comment_id]);
    }

    /**
     * 将评论取消精选
     *
     * @param int      $msg_data_id     群发返回的msg_data_id
     * @param int      $user_comment_id 用户评论id
     * @param int|null $index           多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function unmarkelectComment(int $msg_data_id, int $user_comment_id, ?int $index)
    {
        return $this->post('cgi-bin/comment/unmarkelect', ['msg_data_id' => $msg_data_id, 'index' => $index, 'user_comment_id' => $user_comment_id]);
    }

    /**
     * 删除评论
     *
     * @param int      $msg_data_id     群发返回的msg_data_id
     * @param int      $user_comment_id 用户评论id
     * @param int|null $index           多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function deleteComment(int $msg_data_id, int $user_comment_id, ?int $index)
    {
        return $this->post('cgi-bin/comment/delete', ['msg_data_id' => $msg_data_id, 'index' => $index, 'user_comment_id' => $user_comment_id]);
    }

    /**
     * 回复评论
     *
     * @param int      $msg_data_id     群发返回的msg_data_id
     * @param int      $user_comment_id 用户评论id
     * @param string   $content         回复内容
     * @param int|null $index           多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function replyComment(int $msg_data_id, int $user_comment_id, string $content, ?int $index)
    {
        return $this->post('cgi-bin/comment/reply/add', ['msg_data_id' => $msg_data_id, 'index' => $index, 'user_comment_id' => $user_comment_id, 'content' => $content]);
    }

    /**
     * 删除回复
     *
     * @param int      $msg_data_id     群发返回的msg_data_id
     * @param int      $user_comment_id 用户评论id
     * @param int|null $index           多图文时，用来指定第几篇图文，从0开始，不带默认操作该msg_data_id的第一篇图文
     *
     * @return mixed
     * @throws Exception
     */
    public function deleteCommentReply(int $msg_data_id, int $user_comment_id, ?int $index)
    {
        return $this->post('cgi-bin/comment/reply/delete', ['msg_data_id' => $msg_data_id, 'index' => $index, 'user_comment_id' => $user_comment_id]);
    }
}