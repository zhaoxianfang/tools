<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 云开发
 */
class CloudBase extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 发送短信v2
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/newSendCloudBaseSms.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function newSendCloudBaseSms(array $data)
    {
        return $this->post('tcb/sendsmsv2', $data);
    }

    /**
     * 发送短信
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/sendCloudBaseSms.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function sendCloudBaseSms(array $data)
    {
        return $this->post('tcb/sendsms', $data);
    }

    /**
     * 创建发短信任务
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/createSendSmsTask.html
     *
     * @param string    $env         环境 ID
     * @param string    $file_url    短信 CSV 文件地址CodeUri
     * @param string    $template_id 短信模版 ID 默认值：844110（销类短信模版 ID)
     * @param bool|null $use_short_name
     *
     * @return array
     * @throws Exception
     */
    public function createSendSmsTask(string $env, string $file_url, string $template_id, ?bool $use_short_name)
    {
        $data = [
            'env'            => $env,
            'file_url'       => $file_url,
            'template_id'    => $template_id,
            'use_short_name' => $use_short_name,
        ];
        return $this->post('tcb/createsendsmstask', $data);
    }

    /**
     * 延时调用云函数
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/addDelayedFunctionTask.html
     *
     * @param string $env           环境 ID
     * @param string $function_name 函数名称
     * @param string $data          发送的数据包，格式必须为JSONString
     * @param int    $delay_time    延迟时间，单位：秒，合法范围：6s-30天
     *
     * @return array
     * @throws Exception
     */
    public function addDelayedFunctionTask(string $env, string $function_name, string $data, int $delay_time)
    {
        $data = [
            'env'           => $env,
            'function_name' => $function_name,
            'data'          => $data,
            'delay_time'    => $delay_time,
        ];
        return $this->post('tcb/adddelayedfunctiontask', $data);
    }

    /**
     * 云开发上报接口
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/cloudbaseReportAPI.html
     *
     * @param array $data 发送的数据包，格式必须为JSONString
     *
     * @return array
     * @throws Exception
     */
    public function cloudbaseReportAPI(array $data)
    {
        return $this->post('tcb/adddelayedfunctiontask', $data);
    }

    /**
     * 查询短信记录
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/describeSmsRecords.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function describeSmsRecords(array $data)
    {
        return $this->post('tcb/describesmsrecords', $data);
    }

    /**
     * 描述扩展上传文件信息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/describeExtensionUploadInfo.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function describeExtensionUploadInfo(array $data)
    {
        return $this->post('tcb/describeextensionuploadinfo', $data);
    }

    /**
     * 获取云开发数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/getCloudBaseStatistics.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getCloudBaseStatistics(array $data)
    {
        return $this->post('tcb/getstatistics', $data);
    }

    /**
     * 获取cloudID 对应的数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/getOpenData.html
     *
     * @param array $cloudid_list CloudID 列表
     *
     * @return array
     * @throws Exception
     */
    public function getOpenData(array $cloudid_list)
    {
        return $this->post('wxa/getopendata', ['cloudid_list' => $cloudid_list]);
    }

    /**
     * 获取实时语音签名
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/getCloudBaseVoIPSign.html
     *
     * @param string $group_id  游戏房间的标识
     * @param int    $timestamp 生成这个随机字符串的 UNIX 时间戳（精确到秒）
     * @param string $nonce     随机字符串，长度应小于 128
     *
     * @return array
     * @throws Exception
     */
    public function getCloudBaseVoIPSign(string $group_id, int $timestamp, string $nonce)
    {
        return $this->post('wxa/getopendata', ['group_id' => $group_id, 'timestamp' => $timestamp, 'nonce' => $nonce]);
    }

    /**
     * 触发云函数
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/cloudbase/invokeCloudFunction.html
     *
     * @param string $env      云开发环境ID
     * @param string $name     云函数名称
     * @param string $req_data 云函数的传入参数，具体结构由开发者定义
     *
     * @return array
     * @throws Exception
     */
    public function invokeCloudFunction(string $env, string $name, string $req_data)
    {
        return $this->post('tcb/invokecloudfunction', ['env' => $env, 'name' => $name, 'req_data' => $req_data]);
    }

}
