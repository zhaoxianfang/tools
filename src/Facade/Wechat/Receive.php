<?php

namespace zxf\Facade\Wechat;

use zxf\Facade\FacadeBase;
use zxf\Facade\FacadeInterface;

/**
 * 微信公众号服务端消息接收与发送消息
 *
 * @method static mixed getMsgType()                                            // 获取当前推送接口类型 (text|image|loction|event|...)
 * @method static mixed getOpenid()                                             获取当前推送来源用户的openid
 * @method static mixed getReceive()                                            获取当前推送的所有数据
 *
 * @method static mixed text($content)                                          回复文本消息 ->text($content)->reply();
 * @method static mixed news($news)                                             回复图文消息（高级图文或普通图文，数组） ->news($news)->reply();
 * @method static mixed image($media_id)                                        回复图片消息（需先上传到微信服务器生成 media_id） ->image($media_id)->reply();
 * @method static mixed voice($media_id)                                        回复语音消息（需先上传到微信服务器生成 media_id） 回复语音消息（需先上传到微信服务器生成 media_id）->voice($media_id)->reply();
 * @method static mixed video($media_id,$title,$desc)                           回复视频消息（需先上传到微信服务器生成 media_id） ->video($media_id,$title,$desc)->reply();
 * @method static mixed music($title,$desc,$musicUrl,$hgMusicUrl,$thumbe)       回复音乐消息 ->music($title,$desc,$musicUrl,$hgMusicUrl,$thumbe)->reply();
 * @method static mixed transferCustomerService($account)                       将消息转发给多客服务 transferCustomerService($account)->reply();
 */
class Receive extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\WeChat\Server\Receive::class;
    }
}