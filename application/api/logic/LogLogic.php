<?php
namespace app\api\logic;

use think\Db;

/**
 * 分类逻辑定义
 * Class OrderLogic.
 */
class LogLogic
{
    function write_log($content)
    {
        $content = "[" . date('Y-m-d H:i:s') . "]" . $content . "\r\n";
        $dir = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') . '/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir . '/' . date('Ymd') . '.txt';
        file_put_contents($path, $content, FILE_APPEND);
    }
}
