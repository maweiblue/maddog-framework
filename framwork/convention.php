<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/27
 * Time: 16:21
 */
return [
    'redis_host'=>'127.0.0.1',
    'redis_port'=>'6379',
    'redis_pwd'=>'',
    'app_namespace'=>'app',
    'default_timezone'=>'PRC',
    'default_filter'=>'',
    'session'                => [
        'id'             => '',
        'var_session_id' => '',
        'prefix'         => 'game',
        'type'           => 'redis',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    'default_module'=>'index',

    'deny_module_list'=>[],

    'default_controller'=>'index',

    'default_action'=>'index',

    'url_controller_layer'=>'controller',

    'controller_suffix'=>false,

    'empty_controller'=>'Error',

    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    'action_suffix'=>'',
    'extra_file_list'        => [FRAMEWORK_PATH . 'helper.php'],
    'tmpl_action_success' =>APP_PATH.'common/view/dispatch_jump.php',
    'tmpl_action_error' =>APP_PATH.'common/view/dispatch_jump.php'

];