<?php

namespace zxf\Wechat\Message\Entity;

use zxf\Wechat\Message\Entity;

class Video extends Entity
{
    /**
     * 通过上传多媒体文件，得到的id.
     */
    protected $mediaId;

    /**
     * 视频消息的标题.
     */
    protected $title;

    /**
     * 视频消息的描述.
     */
    protected $description;

    /**
     * 通过上传多媒体文件，得到的id.
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * 视频消息的标题.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * 视频消息的描述.
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * 消息内容.
     */
    public function getBody()
    {
        $body = [
            'MediaId' => $this->mediaId,
            'Title' => $this->title,
            'Description' => $this->description,
        ];

        return ['Video' => $body];
    }

    /**
     * 消息类型.
     */
    public function getType()
    {
        return 'video';
    }
}
