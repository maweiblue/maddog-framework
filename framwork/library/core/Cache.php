<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/29
 * Time: 13:07
 */

namespace core;


class Cache
{
    protected static $instance;
    protected static $handler;
    public  static  function init(array $option=[],$name=false){
            if(is_null(self::$handler)){
                self::$handler=new \Redis();
                self::$handler->connect("127.0.0.1",'6379');
                self::$handler->select(14);
            }
            return self::$handler;
    }

    public static function has($name)
    {
        return self::init()->get($name)?true:false;
    }

    public  static  function get($name,$default=false){
        $value=self::init()->get($name);
        $jsonData=json_decode($value,true);
        return (null===$jsonData)?$value:$jsonData;
    }

    public  static function set($name,$value,$expire=null){
        if(is_null($expire)){
            $expire=3600;
        }
        if(is_int($expire)&&$expire){
            $result=self::init()->setex($name,$expire,$value);
        }else{
            $result=self::init()->set($name,$value);
        }
        return $result;
    }

    public static function inc($name,$step=1){
        return self::init()->incrBy($name,$step);
    }

    public static  function dec($name,$step=1){
        return self::init()->decrBy($name,$step);
    }

    public static function rm($name){
        return self::init()->delete($name);
    }

    public  static  function clearall(){
        return self::init()->flushDB();
    }

}