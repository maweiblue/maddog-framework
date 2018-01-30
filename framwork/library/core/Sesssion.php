<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/29
 * Time: 10:54
 */

namespace core;

class Sesssion
{
    protected static $prefix = '';
    protected static $init   = null;


    public static function prefix($prefix = '')
    {
        if (empty($prefix) && null !== $prefix) {
            return self::$prefix;
        } else {
            self::$prefix = $prefix;
        }
    }

    public static function init(array $config = [])
    {
        if (empty($config)) {
            $config = Config::get('session');
        }
        // 记录初始化信息
        $isDoStart = false;

        // 启动session
        if (!empty($config['auto_start']) && PHP_SESSION_ACTIVE != session_status()) {
            ini_set('session.auto_start', 0);
            $isDoStart = true;
        }

        if (isset($config['prefix']) && (self::$prefix === '' || self::$prefix === null)) {
            self::$prefix = $config['prefix'];
        }

        if (!empty($config['type'])) {
            if ( !session_set_save_handler(new \core\session\driver\Redis($config))) {
                throw new \RuntimeException('error session handler:not exists!' );
            }
        }
        if ($isDoStart) {
            session_start();
            self::$init = true;
        } else {
            self::$init = false;
        }
    }

    public static function boot()
    {
        if (is_null(self::$init)) {
            self::init();
        } elseif (false === self::$init) {
            if (PHP_SESSION_ACTIVE != session_status()) {
                session_start();
            }
            self::$init = true;
        }
    }

    public static function set($name, $value = '', $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = !is_null($prefix) ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 二维数组赋值
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } elseif ($prefix) {
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }

    public static function get($name = '', $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = !is_null($prefix) ? $prefix : self::$prefix;
        if ('' == $name) {
            // 获取全部的session
            $value = $prefix ? (!empty($_SESSION[$prefix]) ? $_SESSION[$prefix] : []) : $_SESSION;
        } elseif ($prefix) {
            // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                $value = isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                $value = isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
        return $value;
    }


    public static function pull($name, $prefix = null)
    {
        $result = self::get($name, $prefix);
        if ($result) {
            self::delete($name, $prefix);
            return $result;
        } else {
            return;
        }
    }
    public static function flash($name, $value)
    {
        self::set($name, $value);
        if (!self::has('__flash__.__time__')) {
            self::set('__flash__.__time__', $_SERVER['REQUEST_TIME_FLOAT']);
        }
        self::push('__flash__', $name);
    }

    /**
     * 清空当前请求的session数据
     * @return void
     */
    public static function flush()
    {
        if (self::$init) {
            $item = self::get('__flash__');

            if (!empty($item)) {
                $time = $item['__time__'];
                if ($_SERVER['REQUEST_TIME_FLOAT'] > $time) {
                    unset($item['__time__']);
                    self::delete($item);
                    self::set('__flash__', []);
                }
            }
        }
    }

    /**
     * 删除session数据
     * @param string|array  $name session名称
     * @param string|null   $prefix 作用域（前缀）
     * @return void
     */
    public static function delete($name, $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = !is_null($prefix) ? $prefix : self::$prefix;
        if (is_array($name)) {
            foreach ($name as $key) {
                self::delete($key, $prefix);
            }
        } elseif (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    }
    /**
     * 清空session数据
     * @param string|null   $prefix 作用域（前缀）
     * @return void
     */
    public static function clear($prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = !is_null($prefix) ? $prefix : self::$prefix;
        if ($prefix) {
            unset($_SESSION[$prefix]);
        } else {
            $_SESSION = [];
        }
    }
    /**
     * 判断session数据
     * @param string        $name session名称
     * @param string|null   $prefix
     * @return bool
     */
    public static function has($name, $prefix = null)
    {
        empty(self::$init) && self::boot();
        $prefix = !is_null($prefix) ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 支持数组
            list($name1, $name2) = explode('.', $name);
            return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
        } else {
            return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
        }
    }
    /**
     * 添加数据到一个session数组
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public static function push($key, $value)
    {
        $array = self::get($key);
        if (is_null($array)) {
            $array = [];
        }
        $array[] = $value;
        self::set($key, $array);
    }
    /**
     * 启动session
     * @return void
     */
    public static function start()
    {
        session_start();
        self::$init = true;
    }
    /**
     * 销毁session
     * @return void
     */
    public static function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }
        session_unset();
        session_destroy();
        self::$init = null;
    }

    /**
     * 重新生成session_id
     * @param bool $delete 是否删除关联会话文件
     * @return void
     */
    private static function regenerate($delete = false)
    {
        session_regenerate_id($delete);
    }

    /**
     * 暂停session
     * @return void
     */
    public static function pause()
    {
        // 暂停session
        session_write_close();
        self::$init = false;
    }
}