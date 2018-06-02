<?php

namespace zxf\Wechat\Message\Entity;

use zxf\Wechat\Message\Entity;

class Text extends Entity
{
    /**
     * 回复的消息内容.
     */
    protected $content;

    /**
     * 回复的消息内容.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * 消息内容.
     */
    public function getBody()
    {
        return ['Content' => $this->content];
    }

    /**
     * 消息类型.
     */
    public function getType()
    {
        return 'text';
    }
}
