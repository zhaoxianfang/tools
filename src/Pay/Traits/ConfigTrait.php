<?php

namespace zxf\Pay\Traits;

use Exception;
use zxf\tools\Cache;
use zxf\tools\DataArray;
use function config;

/**
 * 微信支付配置
 */
trait ConfigTrait
{
    // 当前激活/使用的配置 名称
    protected string $activeConfigName = 'default';

    /**
     * 初始化配置参数
     *
     * @param string $connectionName 多配置的键名,默认为 default
     *
     * @return $this
     * @throws Exception
     */
    public function initConfig(string $connectionName = 'default'): static
    {
        $this->activeConfigName = $connectionName;

        if (!function_exists('config')) {
            throw new Exception('未定义函数 config()');
        }
        $config = config('tools_pay.wechat.' . $connectionName);
        if (empty($config) || !is_array($config) || empty($config['cache_path'])) {
            throw new Exception('未正确配置 tools_pay.wechat.' . $connectionName);
        }
        empty($this->cache) || $this->cache = Cache::instance();
        $this->cache->setCacheDir($config['cache_path']);

        $this->config = new DataArray(array_merge($this->config, $config));
        if (empty($this->cache->get('lately_wechat_pay_config_' . $connectionName, []))) {
            $this->cache->set('lately_wechat_pay_config_' . $connectionName, $this->config->toArray());
        }
        return $this;
    }

    /**
     * 读取当前使用的配置
     *
     * @param string $connectionName
     *
     * @return bool|array|string|null
     * @throws Exception
     */
    public function toArray(string $connectionName = 'default'): bool|array|string|null
    {
        if (empty($config = $this->cache->get('lately_wechat_pay_config_' . $connectionName, []))) {
            $config = $this->initConfig($connectionName);
        }
        return $config;
    }

    /**
     * 销毁 某个连接配置缓存
     *
     * @param string $connectionName
     *
     * @return $this
     * @throws Exception
     */
    public function destroyConfig(string $connectionName = 'default'): static
    {
        $this->cache->delete('lately_wechat_pay_config_' . $connectionName);
        return $this;
    }
}