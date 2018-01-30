<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/26
 * Time: 15:17
 */

namespace core;

class App
{
    protected static $init=false;

    public  static $modulePath;

    public  static $debug=true;

    public  static $namespace='app';

    public  static $suffix=false;

    /**
     * @var bool 应用路由检测
     */
    protected static $routeCheck;

    /**
     * @var bool 严格路由检测
     */
    protected static $routeMust;
    protected static $dispatch;
    protected static $file = [];

    public  static function run(Request $request=null){
        is_null($request) && $request = Request::instance();
        try{
            $config=self::initCommon();
            $path=$request->path();
            $dispatch = Route::parseUrl($path);
            $request->dispatch($dispatch);
            self::module($dispatch['module'],$config);
        }catch (\Exception $exception) {
            $data = $exception->getMessage();
            die($data);
        }
    }


    public static function module($result, $config, $convert = null)
    {
            if (is_string($result)) {
                $result = explode('/', $result);
            }
            $request = Request::instance();
            // 只支持多模块使用
            $module    = strip_tags(strtolower($result[0] ?: $config['default_module']));
            $available = false;
            if (!in_array($module, $config['deny_module_list']) && is_dir(APP_PATH . $module)) {
                $available = true;
            }
            // 模块初始化
            if ($module && $available) {
                // 初始化模块
                $request->module($module);
                $config = self::init($module);
                // 模块请求缓存检查
//                $request->cache($config['request_cache'], $config['request_cache_expire'], $config['request_cache_except']);
            } else {
                throw new \Exception('module not exists:' . $module,404);
         }
        // 当前模块路径
        App::$modulePath = APP_PATH . ($module ? $module . DS : '');
        // 是否自动转换控制器和操作名
        // 获取控制器名
        $controller = strip_tags($result[1] ?: $config['default_controller']);
        // 获取操作名
        $actionName = strip_tags($result[2] ?: $config['default_action']);
        // 设置当前请求的控制器、操作
        $request->controller(strtolower($controller))->action($actionName);
        // 监听module_init
        $instance = Loader::controller($controller, $config['url_controller_layer'], $config['controller_suffix'], $config['empty_controller']);

        if (is_null($instance)) {
            throw new \Exception('controller not exists:' . strtolower($controller),404);
        }
        // 获取当前操作名
        $action = $actionName . $config['action_suffix'];
        $vars = [];
        if (is_callable([$instance, $action])) {
            // 执行操作方法
            $call = [$instance, $action];
        }else {
            // 操作不存在
            throw new \Exception( 'method not exists:' . get_class($instance) . '->' . $action . '()',404);
        }
        return self::invokeMethod($call, $vars);
    }


    public static function invokeMethod($method, $vars = [])
    {
        if (is_array($method)) {
            $class   = is_object($method[0]) ? $method[0] : self::invokeClass($method[0]);
            if(method_exists($class,$method[1])){
                $reflect = new \ReflectionMethod($class, $method[1]);
            }else{
                return $class->$method[1]($vars);
            }
        } else {
            // 静态方法
            $reflect = new \ReflectionMethod($method);
        }
        return $reflect->invokeArgs(isset($class) ? $class : null,$vars);
    }

    public static function initCommon(){
        if(empty(self::$init)){
              $config=self::init();
              self::$namespace=$config['app_namespace'];
              Loader::addNamespace($config['app_namespace'],APP_PATH);
            // 加载额外文件
            if (!empty($config['extra_file_list'])) {
                foreach ($config['extra_file_list'] as $file) {
                    $file = strpos($file, '.') ? $file : APP_PATH . $file . '.php';
                    if (is_file($file) && !isset(self::$file[$file])) {
                        include $file;
                        self::$file[$file] = true;
                    }
                }
            }
            date_default_timezone_set($config['default_timezone']);
            self::$init=true;
        }
        return Config::get();
    }

    private static function init($module=''){
        $module=$module?$module.DS:'';
        if(is_file(APP_PATH.$module.'init.php')) {
            include APP_PATH . $module . 'init.php';
        }else{
            $path=APP_PATH.$module;
            $config=Config::load(APP_PATH.$module.'config.php');
            $filename=APP_PATH.$module.'database.php';
            Config::load($filename,'database');
            if(is_file($path.'commom.php')){
                include APP_PATH.$module.'commom.php';
            }
        }
        return Config::get();
    }


    public static function invokeClass($class, $vars = [])
    {
        $reflect     = new \ReflectionClass($class);
        $args = [];
        return $reflect->newInstanceArgs($args);
    }

    
}