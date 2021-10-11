<?php
namespace think;

require_once __DIR__ . '/../../think-helper/src/Str.php';
require_once __DIR__ .'/queue/connector/Sync.php';
require_once __DIR__ .'/queue/connector/Redis.php';
require_once __DIR__ .'/queue/connector/Database.php';

use think\helper\Str;
use think\queue\Connector;

/**
 * Class Queue
 * @package think\queue
 *
 * @method static push($job, $data = '', $queue = null)
 * @method static later($delay, $job, $data = '', $queue = null)
 * @method static pop($queue = null)
 * @method static marshal()
 */
class Queue
{
    /** @var Connector */
    protected static $connector;

    private static function buildConnector()
    {
        $options = Config::get('queue');
        $type    = !empty($options['connector']) ? $options['connector'] : 'Sync';

        if (!isset(self::$connector)) {

            $class = false !== strpos($type, '\\') ? $type : '\\think\\queue\\connector\\' . Str::studly($type);

            self::$connector = new $class($options);
        }
        return self::$connector;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::buildConnector(), $name], $arguments);
    }
}
