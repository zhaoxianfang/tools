<?php

namespace zxf\WeChat\Offiaccount;

// 微信智能接口
use zxf\WeChat\WeChatBase;

class IntelligentInterface extends WeChatBase
{
    // 提交语音
    public function addvoicetorecofortext($file = '', $voice_id = '')
    {
        return $this->customUpload("cgi-bin/media/voice/addvoicetorecofortext", $file,
            [
                "format"   => 'mp3',//	（只支持mp3，16k，单声道，最大1M）
                "voice_id" => $voice_id,
                "lang"     => "zh_CN",
            ]);
    }

    // 获取语音识别结果
    public function queryrecoresultfortext($voice_id = '')
    {
        return $this->post("cgi-bin/media/voice/queryrecoresultfortext", [],
            [
                "voice_id" => $voice_id,
                "lang"     => "zh_CN",
            ]);
    }

    /**
     * 微信翻译
     *
     * @param string $lfrom 源语言，zh_CN 或 en_US
     * @param string $lto   目标语言，zh_CN 或 en_US
     *
     * @return mixed
     * @throws \Exception
     */
    public function translatecontent($content = '', $lfrom = '', $lto = '')
    {
        return $this->post("cgi-bin/media/voice/translatecontent", ['content' => $content],
            [
                "lfrom" => $lfrom,
                "lto"   => $lto,
            ]);
    }
}