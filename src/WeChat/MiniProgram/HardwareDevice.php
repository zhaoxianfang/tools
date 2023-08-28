<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 硬件设备
 */
class HardwareDevice extends WeChatBase
{
    public $useToken = true;

    /**
     * 发送设备消息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/sendHardwareDeviceMessage.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function sendHardwareDeviceMessage(array $data)
    {
        return $this->post('cgi-bin/message/custom/send', $data);
    }

    /**
     * 获取设备票据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/getSnTicket.html
     *
     * @param string $sn       sn 设备唯一序列号。由厂商分配，长度不能超过128字节。字符只接受数字，大小写字母，下划线（_）和连字符（-）。
     * @param string $model_id 设备型号 id ，通过注册设备获得。
     *
     * @return array
     * @throws Exception
     */
    public function getSnTicket(string $sn, string $model_id)
    {
        return $this->post('wxa/getsnticket', [
            'device_id' => $sn,
            'model_id'  => $model_id,
        ]);
    }

    /**
     * 创建设备组
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/createIotGroupId.html
     *
     * @param string $model_id   设备型号的唯一标识
     * @param string $group_name 设备组的名称（创建时时决定，无法修改）
     *
     * @return array
     * @throws Exception
     */
    public function createIotGroupId(string $model_id, string $group_name)
    {
        return $this->post('wxa/business/group/createid', [
            'model_id'   => $model_id,
            'group_name' => $group_name,
        ]);
    }

    /**
     * 设备组删除设备
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/removeIotGroupDevice.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function removeIotGroupDevice(array $data)
    {
        return $this->post('wxa/business/group/removedevice', $data);
    }

    /**
     * 设备组添加设备
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/addIotGroupDevice.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addIotGroupDevice(array $data)
    {
        return $this->post('wxa/business/group/adddevice', $data);
    }

    /**
     * 查询设备组信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/hardware-device/getIotGroupInfo.html
     *
     * @param string $group_id 设备组的唯一标识
     *
     * @return array
     * @throws Exception
     */
    public function getIotGroupInfo(string $group_id)
    {
        return $this->post('wxa/business/group/getinfo', ['group_id' => $group_id]);
    }

}