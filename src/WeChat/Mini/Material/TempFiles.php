<?php

namespace zxf\WeChat\Mini\Material;

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
        $res = $this->upload(10, $filePath, 'image');
        if (!empty($res['errcode'])) {
            throw new Exception($this->getMessage($res['errcode']), $res['errcode']);
        }
        return $res; // 返回 type、media_id、created_at
    }

}
