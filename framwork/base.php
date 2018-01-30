<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/26
 * Time: 10:37
 */
//框架版本号
define('VERSION','0.1');
//开始时间
define('START_TIME',microtime(true));
//
define('START_MEN',memory_get_usage());
define('DS', DIRECTORY_SEPARATOR);
defined('FRAMEWORK_PATH') or define('FRAMEWORK_PATH',__DIR__.DS);
define('LIB_PATH',FRAMEWORK_PATH.'library'.DS);
define("CORE_PATH",LIB_PATH.'core'.DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);

define('EXTEND_PATH',ROOT_PATH.'extend'.DS);

require CORE_PATH.'Loader.php';
\core\Loader::register();
\core\Config::set(include FRAMEWORK_PATH . 'convention.php');
\core\App::run();

