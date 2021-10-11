<?php

namespace think;

use \Predis\Autoloader;
use \Predis\Client;

require __DIR__ . '/../../../vendor/predis/autoload.php';

//   https://www.php.cn/php-weizijiaocheng-392744.html
class Redis
{

    /**
     * 构造函数
     * @access protected
     * @param array $options 参数
     */
    protected function __construct($options = [])
    {
        Autoloader::register();
        $redis = new Client();
    }

    //设置
    public static function set($name, $value)
    {
        $redis = new Client();
        return $redis->set($name, $value);
    }

    //取出
    public static function get($name)
    {
        $redis = new Client();
        return $redis->get($name);
    }

    //设置有效期为N秒的键值
    public static function setex($name, $time = 60, $value)
    {
        $redis = new Client();
        return $redis->setex($name, $time, $value);
    }

    // 在h表中 添加name字段 value为TK
    public static function hSet($table, $name, $value)
    {
        $redis = new Client();
        return $redis->hSet($table, $name, $value);
    }

    // 获取h表中name字段value
    public static function hGet($table, $name)
    {
        $redis = new Client();
        return $redis->hGet($table, $name);
    }

    //判断email 字段是否存在与表h 不存在返回false
    public static function hExists($table, $name)
    {
        $redis = new Client();
        return $redis->hExists($table, $name);
    }

    //获取h表中所有字段value
    public static function hKeys($table)
    {
        $redis = new Client();
        return $redis->hKeys($table);
    }

    //获取h表中所有字段value
    public static function hVals($table)
    {
        $redis = new Client();
        return $redis->hVals($table);
    }

    // 获取h表中所有字段和value 返回一个关联数组(字段为键值)
    public static function hGetAll($table)
    {
        $redis = new Client();
        return $redis->hGetAll($table);
    }
    // 删除h表中email 字段
    public static function hDel($table, $name)
    {
        $redis = new Client();
        return $redis->hDel($table, $name);
    }
}
