<?php

namespace zxf\tools;
/**
 * 命令行参数解析工具类
 *
 * #!/usr/bin/env php
 * <?php
 *
 * $cmd = new zxf\tools\Command::instance();
 *
 * 获取所有参数值
 * $cmd->all();
 *
 * 解析选项 port
 * $cmd->option('port', function ($val) {
 *     // $val port选项传入的值
 *     echo 'Option port handler=》.$val;
 * });
 *
 * 解析参数 test
 * $cmd->args('test', function ($bool){
 * $bool 是否解析到 test true|false
 *     if($bool){
 *         // 传入了 test
 *     }else{
 *         //未传入 test
 *     }
 * });
 *
 * // 获取所有Opts的值
 * $cmd->getOptVal();
 * // 获取 port 的值 ，没有则返回null
 * $cmd->getOptVal('post');
 *
 * // 获取所有Args的值
 * $cmd->getArgVal();
 * // 获取 是否传入 test 的 ，返回true|false
 * $cmd->getArgVal('test');
 *
 * 调用 demo:  php zxf\tools\Command.php --port 3307 -c 100 -hlocal -g test
 * 传入参数说明：
 *    --opts参数名称 加 空格 加 opts参数值 例如：--port 3307 表示 port 的值为 3307      ; 返回到 opts 中
 *    -opts参数名称 加 空格 加 opts参数值 例如：-c 100 表示 c 的值为 100                ; 返回到 opts 中
 *    -opts参数简称「单字母」 不加空格 接opts参数值 例如：-hlocal 表示  的值为 local      ; 返回到 opts 中
 *    -opts参数简称「单字母」 例如：-g 表示 传入了参数 g                                ; 返回到 opts 中
 *    参数名称 例如：test 表示 传入了参数 test                                         ; 返回到 args 中
 */
class Command
{
    // store options
    private static $optsArr = [];
    // store args
    private static $argsArr = [];
    // 是否解析过
    private static $isParse = false;

    /**
     * @var object 对象实例
     */
    protected static $instance;

    public function __construct()
    {
        if (!self::$isParse) {
            self::parseArgs();
        }
    }

    /**
     * 初始化
     *
     * @param $options
     * @return object|static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 检查环境
        if (PHP_SAPI != 'cli') {
            exit('Please run under command line.');
        }
        return self::$instance;
    }

    /**
     * 获取选项值
     * @param string|NULL $opt
     * @return array|string|NULL
     */
    public function getOptVal($opt = null)
    {
        if (is_null($opt)) {
            return self::$optsArr;
        } else if (isset(self::$optsArr[$opt])) {
            return self::$optsArr[$opt];
        }
        return null;
    }

    /**
     * 获取命令行参数值
     * @param string|NULL $index
     * @return array|string|NULL
     */
    public function getArgVal($arg = null)
    {
        if (is_null($arg)) {
            return self::$argsArr;
        } elseif (!empty(self::$argsArr) && in_array($arg, self::$argsArr)) {
            return true;
        }
        return false;
    }

    /**
     * 注册选项对应的回调处理函数, $callback 应该有一个参数, 用于接收选项值
     *
     * @param string $opt 解析的opts参数名称
     * @param callable $callback 回调函数
     * @return void 解析到值返回解析值，否则返回 null
     * @throws InvalidArgumentException
     */
    public function option($opt, callable $callback)
    {
        // check
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf('Not a valid callback <%s>.', $callback));
        }
        if (isset(self::$optsArr[$opt])) {
            call_user_func($callback, self::$optsArr[$opt]);
        } else {
            call_user_func($callback, null);
        }
    }

    /**
     * 注册参数对应的回调处理函数, $callback 应该有一个参数, 用于接收参数值
     * @param string $arg 解析的arg参数名称
     * @param callable $callback 回调函数
     * @return void 解析到参数返回true，否则返回 false
     * @throws InvalidArgumentException
     */
    public function args($arg, callable $callback)
    {
        // check
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf('Not a valid callback <%s>.', $callback));
        }
        if (!empty(self::$argsArr) && in_array($arg, self::$argsArr)) {
            call_user_func($callback, true);
        } else {
            call_user_func($callback, false);
        }
    }

    /**
     * 获取所有 opts 和 args 的值
     * @return array
     */
    public function all()
    {
        return ['opts' => self::$optsArr, 'args' => self::$argsArr];
    }

    /**
     * 是否是 -s 形式的短选项
     * @param string $opt
     * @return string|boolean 返回短选项名
     */
    private static function isShortOptions($opt)
    {
        if (preg_match('/^\-([a-zA-Z0-9])$/', $opt, $matchs)) {
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是 -svalue 形式的短选项
     * @param string $opt
     * @return array|boolean 返回短选项名以及选项值
     */
    private static function isShortOptionsWithValue($opt)
    {
        if (preg_match('/^\-([a-zA-Z0-9])(\S+)$/', $opt, $matchs)) {
            return [$matchs[1], $matchs[2]];
        }
        return false;
    }

    /**
     * 是否是 --longopts 形式的长选项
     * @param string $opt
     * @return string|boolean 返回长选项名
     */
    private static function isLongOptions($opt)
    {
        if (preg_match('/^\-\-([a-zA-Z0-9\-_]{2,})$/', $opt, $matchs)) {
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是 --longopts=value 形式的长选项
     * @param string $opt
     * @return array|boolean 返回长选项名及选项值
     */
    private static function isLongOptionsWithValue($opt)
    {
        if (preg_match('/^\-\-([a-zA-Z0-9\-_]{2,})(?:\=(.*?))$/', $opt, $matchs)) {
            return [$matchs[1], $matchs[2]];
        }
        return false;
    }

    /**
     * 是否是命令行参数
     * @param string $value
     * @return boolean
     */
    private static function isArg($value)
    {
        return !preg_match('/^\-/', $value);
    }

    /**
     * 解析命令行参数
     * @return array ['opts'=>[], 'args'=>[]]
     */
    private final static function parseArgs()
    {
        global $argv;
        if (!self::$isParse) {
            // index start from one
            $index  = 1;
            $length = count($argv);
            while ($index < $length) {
                // current value
                $curVal = $argv[$index];
                // check, short or long options
                if (($key = self::isShortOptions($curVal)) || ($key = self::isLongOptions($curVal))) {
                    // go ahead
                    $index++;
                    if (isset($argv[$index]) && self::isArg($argv[$index])) {
                        self::$optsArr[$key] = $argv[$index];
                    } else {
                        self::$optsArr[$key] = true;
                        // back away
                        $index--;
                    }
                } // check, short or long options with value
                else if (($key = self::isShortOptionsWithValue($curVal))
                    || ($key = self::isLongOptionsWithValue($curVal))) {
                    self::$optsArr[$key[0]] = $key[1];
                } // args
                else if (self::isArg($curVal)) {
                    self::$argsArr[] = $curVal;
                }
                // incr index
                $index++;
            }
            self::$isParse = true; // change status
        }
        return ['opts' => self::$optsArr, 'args' => self::$argsArr];
    }
}



