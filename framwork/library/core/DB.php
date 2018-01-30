<?php
/**
 * Created by PhpStorm.
 * User: mawei
 * Date: 2017/7/27
 * Time: 17:02
 */

namespace core;
use app\admin\controller\base;

/**
 * mysql数据库管理
 * @package core
 */

class DB
{
    private static $pdo;
    private static $pre;
    public static function instance(){
        if(self::$pdo==null){
            $cfg=Config::get('database');
            $dsn="mysql:dbname={$cfg['database']};host={$cfg['hostname']};port={$cfg['port']}";
            self::$pdo=new \PDO($dsn,$cfg['username'],$cfg['password'],$cfg['params']);
            self::$pdo->exec("set names '{$cfg['charset']}';");
            self::$pdo->exec("set sql_mode=''");
        }
        return self::$pdo;
    }


    /**
     * 获取query sql
     * @param $sql
     * @param array $params
     * @return bool|int
     */
    public  static function query($sql,$params=array()){
        if(empty($params)){
            $result=self::instance()->exec($sql);
            return $result;
        }
        $statement=self::instance()->prepare($sql);
//        var_dump($params);

        $result=$statement->execute($params);
        if(!$result){
            return false;
        }else{
            return $statement->rowCount();
        }
    }

    public  static function error(){
        return self::instance()->errorInfo();
    }

    /**
     * 获取单个字段
     * @param $sql
     * @param array $params
     * @param int $column
     * @return bool|string
     */
    public  static  function fetchcolumn($sql,$params=array(),$column=0){
        $statement=self::instance()->prepare($sql);
//        print_r($params);
        $result=$statement->execute($params);
        if(!$result){
            return false;
        }else{
            return $statement->fetchColumn($column);
        }
    }

    public static function showQuery($query, $params)
    {
        $keys = array();
        $values = array();

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_numeric($value)) {
                $values[] = intval($value);
            } else {
                $values[] = '"' . $value . '"';
            }
        }

        $query = preg_replace($keys, $values, $query, 1, $count);
        return $query;
    }

        /**
     * 获取一行
     * @param $sql
     * @param array $params
     * @return bool|mixed
     */
    public  static  function fetch($sql,$params=array()){
        $statement=self::instance()->prepare($sql);
        $result=$statement->execute($params);
        if(!$result){
            return false;
        }else{
            return $statement->fetch(\PDO::FETCH_ASSOC);
        }
    }

    public  static  function  fetchall($sql,$params=array()){
        $statement=self::instance()->prepare($sql);
        $result=$statement->execute($params);
        if(!$result){
            return false;
        }else{
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public  static function update($table,$data=array(),$params=array(),$glue = 'AND'){
        $fields=self::implode($data,',');
        $conditon=self::implode($params,$glue);
        $params=array_merge($fields['params'],$conditon['params']);
        $sql='update '.self::tablename($table)." set {$fields['fields']}";
        $sql.=$conditon['fields']?' where '.$conditon['fields']:'';
//        echo $sql;
//        var_dump($params);
//        echo self::showQuery($sql,$params);
        return self::query($sql,$params);
    }


    public static function insert($table,$data=array(),$replace=false){
        $cmd=$replace?'replace into':'insert into';
        $condition=self::implode($data,',');
        return self::query("$cmd ".self::tablename($table)." set {$condition['fields']}",$condition['params']);
    }

    public  static function insertid(){
        return self::instance()->lastInsertId();
    }


    public static function delete($table,$params=array(),$glue='and'){
        $condtion=self::implode($params,$glue);
        $sql="delete from ".self::tablename($table);
        $sql.=$condtion['fields']?' where '.$condtion['fields']:'';

        return self::query($sql,$condtion['params']);
    }



    public static  function begin(){
        self::instance()->beginTransaction();
    }
    public  static  function commit(){
        self::instance()->commit();
    }
    public  static  function rollback(){
        self::instance()->rollBack();
    }
    /**
     * 返回拼接好的table名称
     * @param $table
     * @return string
     */
    public static function tablename($table) {
        $cfg=Config::get('database');
        return "`{$cfg['prefix']}{$table}`";
    }

    /**
     * 拼接pdo字符
     * @param $params
     * @param string $glue
     * @return array
     */
    private static function implode($params, $glue = ',') {
        $result = array('fields' => ' 1 ', 'params' => array());
        $split = '';
        $suffix = '';
        if (in_array(strtolower($glue), array('and', 'or'))) {
            $suffix = '__';
        }
        if (!is_array($params)) {
            $result['fields'] = $params;
            return $result;
        }
        if (is_array($params)) {
            $result['fields'] = '';
            foreach ($params as $fields => $value) {
                $result['fields'] .= $split . "`$fields` =  :{$suffix}$fields";
                $split = ' ' . $glue . ' ';
                $result['params'][":{$suffix}$fields"] = is_null($value) ? '' : $value;
            }
        }
        return $result;
    }
}