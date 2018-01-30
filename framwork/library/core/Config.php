<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/26
 * Time: 17:17
 */

namespace core;


class Config
{
    private static $config=[];
    private static $range='_sys_';
    public  static  function range($range){
        self::$range=$range;
        if(!isset(self::$config[$range])){
            self::$config[$range]=[];
        }
    }

    public  static function  load($file,$name='',$range=''){
        $range=$range?:self::$range;

        if(!isset(self::$config[$range])){
            self::$config[$range]=[];
        }
        if(is_file($file)){
            $name=strtolower($name);
            return self::set(include $file,$name,$range);
        }else{
            return self::$config[$range];
        }
    }

    public  static function has($name,$range=''){
        $range=$range?:self::$range;
        if(!strpos($name,'.')){
            return isset(self::$config[$range][strtolower($name)]);
        }else{
            $name=explode('.',$name,2);
            return isset(self::$config[$range][strtolower($name[0])][$name[1]]);
        }
    }

    public  static  function get($name=null,$range=''){
        $range=$range?:self::$range;
        if(empty($name)&&isset(self::$config[$range])){
            return self::$config[$range];
        }
        if(!strpos($name,'.')){
            $name=strtolower($name);
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        }else{
            $name    = explode('.', $name, 2);
            $name[0] = strtolower($name[0]);
            return isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null;
        }
    }

    public static function set($name, $value = null, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                self::$config[$range][strtolower($name)] = $value;
            } else {
                // 二维数组设置和获取支持
                $name                                                 = explode('.', $name, 2);
                self::$config[$range][strtolower($name[0])][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            // 批量设置
            if (!empty($value)) {
                self::$config[$range][$value] = isset(self::$config[$range][$value]) ?
                    array_merge(self::$config[$range][$value], $name) :
                    self::$config[$range][$value] = $name;
                return self::$config[$range][$value];
            } else {
                return self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
            }
        } else {
            // 为空直接返回 已有配置
            return self::$config[$range];
        }
    }
    public static function reset($range = '')
    {
        $range = $range ?: self::$range;
        if (true === $range) {
            self::$config = [];
        } else {
            self::$config[$range] = [];
        }
    }


}