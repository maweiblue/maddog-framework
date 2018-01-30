<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/26
 * Time: 11:06
 */
namespace core;
/**
 * 当前只实现PSR4的自动加载和mapping的方式，自定义类建议使用mapping
 * Class Loader
 * @package core
 *
 */
class Loader{
    // PSR-4
    private static $prefixLengthsPsr4 = [];
    private static $prefixDirsPsr4    = [];
    private static $fallbackDirsPsr4  = [];

    //mapping
    private static $map=[];

    public static function  autoload($class){
        if($file=self::findFile($class)){
            include $file;
            return true;
        }
    }

    private static function findFile($class){
        //class mappping
        if(!empty(self::$map[$class])){
            return self::$map[$class];
        }
        // 查找 PSR-4
        $logicalPathPsr4 = strtr($class, '\\', DS) . '.php';
        $first = $class[0];
        if (isset(self::$prefixLengthsPsr4[$first])) {
            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
                        if (is_file($file = $dir . DS . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }
        // 查找 PSR-4 fallback dirs
        foreach (self::$fallbackDirsPsr4 as $dir) {
            if (is_file($file = $dir . DS . $logicalPathPsr4)) {
                return $file;
            }
        }
        return self::$map[$class]=false;
    }

    /**
     * 添加mapping class
     * @param $class
     * @param string $map
     */
    public static function addClassMap($class, $map = '')
    {
        if (is_array($class)) {
            self::$map = array_merge(self::$map, $class);
        } else {
            self::$map[$class] = $map;
        }
    }

    /**
     * 对外接口
     * @param $namespace
     * @param string $path
     */
    public static function addNamespace($namespace, $path = '')
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $paths) {
                self::addPsr4($prefix . '\\', rtrim($paths, DS), true);
            }
        } else {
            self::addPsr4($namespace . '\\', rtrim($path, DS), true);
        }
    }

    /**
     * 为引导页面添加目录地址
     * @param $prefix
     * @param $paths
     * @param bool $prepend
     */
    private static  function addPsr4($prefix,$paths,$prepend = false){
        if (!$prefix) {
            // Register directories for the root namespace.
            if ($prepend) {
                self::$fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    self::$fallbackDirsPsr4
                );
            } else {
                self::$fallbackDirsPsr4 = array_merge(
                    self::$fallbackDirsPsr4,
                    (array) $paths
                );
            }
        } elseif (!isset(self::$prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix]    = (array) $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                self::$prefixDirsPsr4[$prefix]
            );
        } else {
            // Append directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                self::$prefixDirsPsr4[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * 对外注册loader
     */
    public  static function register(){
        spl_autoload_register('core\\Loader::autoload',true,true);
        self::addNamespace([
            'core'=>LIB_PATH.'core'.DS
        ]);
        if(is_file(APP_PATH.'classmap.php')){
            self::addClassMap(include ROOT_PATH.'classmap.php');
        }
    }

    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

    public static function controller($name, $layer = 'controller', $appendSuffix = false, $empty = '')
    {
        if (false !== strpos($name, '\\')) {
            $class  = $name;
            $module = Request::instance()->module();
        } else {
            if (strpos($name, '/')) {
                list($module, $name) = explode('/', $name);
            } else {
                $module = Request::instance()->module();
            }
            $class = self::parseClass($module, $layer, $name);
        }
//        echo $class;
        if (class_exists($class)) {
            return App::invokeClass($class);
        }
    }

    public static function parseClass($module, $layer, $name, $appendSuffix = false)
    {
        $name  = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = strtolower(array_pop($array));
        $path  = $array ? implode('\\', $array) . '\\' : '';
        return App::$namespace . '\\' . ($module ? $module . '\\' : '') . $layer . '\\' . $path . $class;
    }


}