<?php

namespace zxf\Tools;

/**
 * Session 操作类
 * 专注于文件存储，支持过期时间控制与复杂数据处理。
 */
class Session
{
    const SESSION_STARTED     = true;
    const SESSION_NOT_STARTED = false;

    private bool   $session_state = self::SESSION_NOT_STARTED;
    private static $instance;

    /**
     * 获取 Session 实例（单例模式）
     *
     * @param array $options 配置选项，例如 ['name' => 'CUSTOM_SESSION_NAME']
     *
     * @return static
     */
    public static function instance(array $options = []): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        self::$instance->startSession($options);

        return self::$instance;
    }

    /**
     * 启动会话
     *
     * @param array $options 配置选项，例如 ['name' => 'CUSTOM_SESSION_NAME']
     *
     * @return bool
     */
    public function startSession(array $options = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!empty($options)) {
                foreach ($options as $key => $value) {
                    ini_set('session.' . $key, $value);
                }
            }
            session_start();
        }

        $this->session_state = session_status() === PHP_SESSION_ACTIVE;

        return $this->session_state;
    }

    /**
     * 设置 Session 值
     *
     * @param string   $name   键名
     * @param mixed    $value  键值
     * @param int|null $expiry 过期时间（秒），默认不设置过期时间
     */
    public function set(string $name, mixed $value, ?int $expiry = null): void
    {
        $_SESSION[$name] = [
            'value'  => $value,
            'expiry' => $expiry ? time() + $expiry : null,
        ];
    }

    /**
     * 获取 Session 值
     *
     * @param string $name 键名
     *
     * @return mixed|null 返回值或 null 如果不存在或已过期
     */
    public function get(string $name)
    {
        if (isset($_SESSION[$name])) {
            $data = $_SESSION[$name];
            // 检查是否存在且未过期
            if (!$this->exists($name)) {
                return null;
            }
            return isset($data['value']) && isset($data['expiry']) ? $data['value'] : $data;
        }
        return null;
    }

    /**
     * 获取所有未过期的 Session 数据
     *
     * @return array
     */
    public function all(): array
    {
        $result = [];
        foreach ($_SESSION as $name => $data) {
            $item = $this->get($name);
            if (!is_null($item)) {
                $result[$name] = $item;
            }
        }
        return $result;
    }

    /**
     * 删除 Session 键
     *
     * @param string $name 键名
     */
    public function delete(string $name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * 判断键是否存在且未过期
     *
     * @param string $name 键名
     *
     * @return bool
     */
    public function exists(string $name): bool
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }

        $data = $_SESSION[$name];
        // 检查是否过期 并删除过期数据
        if (isset($data['expiry']) && $data['expiry'] < time()) {
            $this->delete($name);
            return false;
        }
        return true;
    }

    /**
     * 清空所有 Session 数据
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * 重新生成session id 并返回新的session id
     *
     * @params bool $deleteOldSession 是否删除旧的会话文件
     *              默认为false，表示保留旧的会话文件
     *              为true表示删除旧的会话文件
     *
     * @return false|string
     */
    public function regenerate(bool $deleteOldSession = false): bool|string
    {
        // 重新生成会话ID，并保留当前会话中的数据
        session_regenerate_id($deleteOldSession); // 参数为true表示删除旧的会话文件
        return $this->sessionId();
    }

    /**
     * 获取session id
     *
     * @return false|string
     */
    public function sessionId(): bool|string
    {
        return session_id();
    }

    /**
     * 销毁会话
     *
     * @return bool
     */
    public function destroy(): bool
    {
        if ($this->session_state === self::SESSION_STARTED) {
            session_unset(); // 清除所有会话数据
            session_destroy();
            $_SESSION            = [];
            $this->session_state = self::SESSION_NOT_STARTED;

            return true;
        }

        return false;
    }

    /**
     * 魔术方法设置值
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * 魔术方法获取值
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 魔术方法判断键是否存在
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->exists($name);
    }

    /**
     * 魔术方法删除键
     *
     * @param string $name
     */
    public function __unset(string $name): void
    {
        $this->delete($name);
    }
}
