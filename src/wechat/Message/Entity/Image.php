<?php

namespace zxf\Wechat\Message\Entity;

use zxf\Wechat\Message\Entity;

class Image extends Entity
{
    /**
     * 通过上传多媒体文件，得到的id.
     */
    protected $mediaId;

    /**
     * 通过上传多媒体文件，得到的id.
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * 消息内容.
     */
    public function getBody()
    {
        $body = ['MediaId' => $this->mediaId];

        return ['Image' => $body];
    }

    /**
     * 消息类型.
     */
    public function getType()
    {
        return 'image';
    }
}
