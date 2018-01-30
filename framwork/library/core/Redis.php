<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/27
 * Time: 13:28
 */

namespace core;

/**
 * 单例的Redis类,对\Redis进行简单包装
 * @method sScan($key, $iterator, $pattern = '', $count = 0) static
 * @method connect( $host, $port = 6379, $timeout = 0.0, $retry_interval = 0 ) static
 * @method psetex($key, $ttl, $value) static
 * @method scan( &$iterator, $pattern = null, $count = 0 ) static
 * @method zScan($key, $iterator, $pattern = '', $count = 0) static
 * @method hScan($key, $iterator, $pattern = '', $count = 0) static
 * @method client($command, $arg = '') static
 * @method open( $host, $port = 6379, $timeout = 0.0, $retry_interval = 0 )  static
 * @method pconnect( $host, $port = 6379, $timeout = 0.0 )  static
 * @method popen( $host, $port = 6379, $timeout = 0.0 )  static
 * @method close( )  static
 * @method setOption( $name, $value ) static
 * @method getOption( $name )static
 * @method ping( ) static
 * @method get( $key ) static
 * @method set( $key, $value, $timeout = 0 ) static
 * @method setex( $key, $ttl, $value ) static
 * @method setnx( $key, $value )static
 * @method del( $key1, $key2 = null, $key3 = null ) static
 * @method delete( $key1, $key2 = null, $key3 = null ) static
 * @method multi( $mode = Redis::MULTI ) static
 * @method exec( ) static
 * @method discard( ) static
 * @method watch( $key ) static
 * @method unwatch() static
 * @method subscribe( $channels, $callback ) static
 * @method psubscribe( $patterns, $callback ) static
 * @method publish( $channel, $message ) static
 * @method pubsub( $keyword, $argument ) static
 * @method exists( $key ) static
 * @method incr( $key ) static
 * @method incrByFloat( $key, $increment ) static
 * @method incrBy( $key, $value ) static
 * @method decr( $key ) static
 * @method decrBy( $key, $value )static
 * @method getMultiple( array $keys )static
 * @method lPush( $key, $value1, $value2 = null, $valueN = null ) static
 * @method rPush( $key, $value1, $value2 = null, $valueN = null ) static
 * @method lPushx( $key, $value ) static
 * @method rPushx( $key, $value ) static
 * @method lPop( $key )  static
 * @method rPop( $key )  static
 * @method blPop( array $keys, $timeout)   static
 * @method brPop( array $keys, $timeout )    static
 * @method lSize( $key )   static
 * @method lIndex( $key, $index )  static
 * @method lGet( $key, $index )  static
 * @method lSet( $key, $index, $value )  static
 * @method lRange( $key, $start, $end )  static
 * @method lGetRange( $key, $start, $end )  static
 * @method lTrim( $key, $start, $stop ) static
 * @method listTrim( $key, $start, $stop ) static
 * @method lRem( $key, $value, $count ) static
 * @method lRemove( $key, $value, $count ) static
 * @method lInsert( $key, $position, $pivot, $value ) static
 * @method sAdd( $key, $value1, $value2 = null, $valueN = null ) static
 * @method sAddArray( $key, array $values) static
 * @method sRem( $key, $member1, $member2 = null, $memberN = null ) static
 * @method sRemove( $key, $member1, $member2 = null, $memberN = null ) static
 * @method sMove( $srcKey, $dstKey, $member ) static
 * @method ssIsMember( $key, $value ) static
 * @method sContains( $key, $value ) static
 * @method sCard( $key ) static
 * @method sPop( $key ) static
 * @method sRandMember( $key, $count = null ) static
 * @method sInter( $key1, $key2, $keyN = null ) static
 * @method sInterStore( $dstKey, $key1, $key2, $keyN = null ) static
 * @method sUnion( $key1, $key2, $keyN = null ) static
 * @method sUnionStore( $dstKey, $key1, $key2, $keyN = null ) static
 * @method sDiff( $key1, $key2, $keyN = null ) static
 * @method sDiffStore( $dstKey, $key1, $key2, $keyN = null ) static
 * @method sMembers( $key ) static
 * @method sGetMembers( $key )  static
 * @method getSet( $key, $value )  static
 * @method randomKey( )  static
 * @method select( $dbindex )  static
 * @method move( $key, $dbindex )  static
 * @method rename( $srcKey, $dstKey )  static
 * @method renameKey( $srcKey, $dstKey )  static
 * @method renameNx( $srcKey, $dstKey )  static
 * @method expire( $key, $ttl )  static
 * @method pExpire( $key, $ttl )  static
 * @method setTimeout( $key, $ttl )  static
 * @method expireAt( $key, $timestamp )  static
 * @method pExpireAt( $key, $timestamp )  static
 * @method keys( $pattern )  static
 * @method getKeys( $pattern )  static
 * @method dbSize( )  static
 * @method auth( $password ) static
 * @method bgrewriteaof( ) static
 * @method slaveof( $host = '127.0.0.1', $port = 6379 ) static
 * @method object( $string = '', $key = '' ) static
 * @method save( ) static
 * @method bgsave( ) static
 * @method lastSave( ) static
 * @method wait( $numSlaves, $timeout ) static
 * @method type( $key ) static
 * @method append( $key, $value ) static
 * @method  getRange( $key, $start, $end ) static
 * @method  substr( $key, $start, $end ) static
 * @method  setRange( $key, $offset, $value ) static
 * @method  bitpos( $key, $bit, $start = 0, $end = null) static
 * @method  getBit( $key, $offset ) static
 * @method  setBit( $key, $offset, $value ) static
 * @method  bitCount( $key ) static
 * @method  bitOp( $operation, $retKey, ...$keys) static
 * @method  flushDB( ) static
 * @method  flushAll( ) static
 * @method  sort( $key, $option = null ) static
 * @method  info( $option = null ) static
 * @method  resetStat( ) static
 * @method  ttl( $key ) static
 * @method  pttl( $key ) static
 * @method  persist( $key ) static
 * @method  mset( array $array ) static
 * @method  mget( array $array ) static
 * @method  msetnx( array $array ) static
 * @method  rpoplpush( $srcKey, $dstKey ) static
 * @method  brpoplpush( $srcKey, $dstKey, $timeout ) static
 * @method  zAdd( $key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null ) static
 * @method  zRange( $key, $start, $end, $withscores = null ) static
 * @method  zRem( $key, $member1, $member2 = null, $memberN = null ) static
 * @method  zDelete( $key, $member1, $member2 = null, $memberN = null ) static
 * @method  zRevRange( $key, $start, $end, $withscore = null ) static
 * @method  zRangeByScore( $key, $start, $end, array $options = array() ) static
 * @method  zRevRangeByScore( $key, $start, $end, array $options = array() ) static
 * @method  zRangeByLex( $key, $min, $max, $offset = null, $limit = null ) static
 * @method  zRevRangeByLex( $key, $min, $max, $offset = null, $limit = null ) static
 * @method  zCount( $key, $start, $end ) static
 * @method  zRemRangeByScore( $key, $start, $end ) static
 * @method  zDeleteRangeByScore( $key, $start, $end ) static
 * @method  zRemRangeByRank( $key, $start, $end ) static
 * @method  zDeleteRangeByRank( $key, $start, $end ) static
 * @method  zCard( $key ) static
 * @method  zSize( $key ) static
 * @method  zScore( $key, $member ) static
 * @method  zRank( $key, $member ) static
 * @method  zRevRank( $key, $member ) static
 * @method  zIncrBy( $key, $value, $member ) static
 * @method  zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') static
 * @method  zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') static
 * @method  hSet( $key, $hashKey, $value ) static
 * @method  hSetNx( $key, $hashKey, $value ) static
 * @method  hGet($key, $hashKey) static
 * @method  hLen( $key ) static
 * @method  hDel( $key, $hashKey1, $hashKey2 = null, $hashKeyN = null ) static
 * @method  hKeys( $key ) static
 * @method  hVals( $key ) static
 * @method  hGetAll( $key ) static
 * @method  hExists( $key, $hashKey ) static
 * @method  hIncrBy( $key, $hashKey, $value ) static
 * @method  hIncrByFloat( $key, $field, $increment ) static
 * @method  hMset( $key, $hashKeys ) static
 * @method  hMGet( $key, $hashKeys ) static
 * @method  config( $operation, $key, $value ) static
 * @method  evalSha( $scriptSha, $args = array(), $numKeys = 0 ) static
 * @method  evaluateSha( $scriptSha, $args = array(), $numKeys = 0 ) static
 * @method  script( $command, $script ) static
 * @method  getLastError() static
 * @method  clearLastError() static
 * @method _prefix( $value ) static
 * @method _unserialize( $value ) static
 * @method _serialize( $value ) static
 * @method dump( $key ) static
 * @method restore( $key, $ttl, $value ) static
 * @method migrate( $host, $port, $key, $db, $timeout, $copy = false, $replace = false ) static
 * @method time() static
 * @method pfAdd( $key, array $elements ) static
 * @method pfCount( $key ) static
 * @method pfMerge( $destkey, array $sourcekeys ) static
 * @method rawCommand( $command, $arguments ) static
 * @method getMode() static
 * @link \Redis
 */
class Redis
{
    private  static $instance;
    private static  function instance(){
        if(is_null(self::$instance)){
            self::$instance=new \Redis();
//            Config::get
            self::$instance->connect('127.0.0.1', 6379);
        }
        return self::$instance;
    }

    public  static  function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        $instance=self::instance();
        switch (count($arguments)){
            case 0:
                return $instance->$name();
            case 1:
                return $instance->$name($arguments[0]);
            case 2:
                return $instance->$name($arguments[0],$arguments[1]);
            case 3:
                return $instance->$name($arguments[0],$arguments[1],$arguments[2]);
            case 4:
                return $instance->$name($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
            case 5:
                return $instance->$name($arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4]);
            default:
                return call_user_func_array([$instance,$name],$arguments);
        }
    }

}