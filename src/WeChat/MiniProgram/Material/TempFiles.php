<?php

namespace zxf\WeChat\MiniProgram\Material;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 小程序素材
 */
class TempFiles extends WeChatBase
{

    /**
     * 上传临时图片
     *
     * @param string $filePath 文件路径
     *
     * @return array|bool|mixed|string
     * @throws Exception
     */
    public function uploadImage(string $filePath)
    {
        $this->generateRequestUrl('cgi-bin/media/upload', ['type' => 'image']);
        $res = $this->upload($filePath, 'image');
        if (!empty($res['errcode'])) {
            throw new Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return $res; // 返回 type、media_id、created_at
    }

}
