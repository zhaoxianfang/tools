<?php

namespace zxf\tools;

/**
 * 实现建单的session 类
 */
class Session
{
    const SESSION_STARTED     = true;
    const SESSION_NOT_STARTED = false;

    private        $session_state = self::SESSION_NOT_STARTED;
    private static $instance;

    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        self::$instance->start_session();
        return self::$instance;
    }

    public function start_session()
    {
        if ($this->session_state == self::SESSION_NOT_STARTED) {
            $this->session_state = session_start();
        }
        return $this->session_state;
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
    }

    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    public function __unsset($name)
    {
        unset($_SESSION[$name]);
    }

    public function destroy()
    {
        if ($this->session_state == self::SESSION_STARTED) {
            $this->session_state = !session_destroy();
            unset($_SESSION);
            return !$this->session_state;
        }

        return false;
    }
}
