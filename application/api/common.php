<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;
// 应用公共文件


function jm($a)
{
    return md5($a);
}

function IsLogin()
{
    return jm(cookie('pay_user_name') . cookie('pay_user_id') . Config('COO_KIE') . session('pay_user_id')) === cookie('pay_key');
}

function IsIds($id, $ids)
{
    //判断是否在数组中存在
    if (in_array($id, $ids) || input('role_id') == 1) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function check_mobile($mobile)
{
    if (preg_match('/1[3456789]\d{9}$/', $mobile)) {
        return true;
    } else {
        return false;
    }
}

function filter_by_value($array, $index, $value, $index1 = false, $value1 = false)
{

    if (is_array($array) && count($array) > 0) {
        if ($index1 != false and $value1 != false) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];
                $temp1[$key] = $array[$key][$index1];
                if ($temp[$key] == $value and $temp1[$key] == $value1) {
                    $newarray[$key] = $array[$key];
                }
            }
            return $newarray;
        }


        foreach (array_keys($array) as $key) {
            $temp[$key] = $array[$key][$index];
            if ($temp[$key] == $value) {
                $newarray[$key] = $array[$key];
            }
        }
        return $newarray;
    }
    /**
     * 全局的用户注册
     */
    function user_register($unionid, $openid, $userinfo = [])
    {
        //开启事物
        Db::startTrans();

        //先注册
        //看看unionid
        $users = Db::name('user')->where(['unionid' => $unionid])->find();
        if ($users) {
            return (['status' => 1, 'msg' => '已注册！', 'data' => $users]);
        }

        if (!$users) {
            //注册一下
            //不存在
            $update = array(
                'unionid' => $unionid,
                'nickname' => isset($userinfo['nickname']) ? $userinfo['nickname'] : '新用户',
                // 'sex' => isset($userinfo['sex']) ? $userinfo['sex'] : 0,
                'avatar' => isset($userinfo['avatar']) ? $userinfo['avatar'] : 'https://www.c3w.com.cn/public/images/avatar.png',
                'last_login' => time(),
                'last_ip' => get_ip()
            );
            Db::name('user')->insert($update);
            $users = Db::name('user')->where(['unionid' => $unionid])->find();
            if (!$users) {
                //回滚
                Db::rollback();
                return (['status' => -1, 'msg' => '注册失败！', 'data' => []]);
            }
            if (!$users['user_id']) {
                //回滚
                Db::rollback();
                return (['status' => -1, 'msg' => '注册失败！', 'data' => []]);
            }
        }
        //提交事物
        Db::commit();
        return (['status' => 1, 'msg' => '注册成功！', 'data' => $users]);
    }
    function get_ip()
    {

        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknown";
        }

        return $ip;
    }
    /**
     *接收token
     */
    function em_get_token()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        $token = isset($headers['Token']) ? $headers['Token'] : '';
        if (!$token) {
            $token = input('token');
        }

        return $token;
    }
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
} //从二维数组中用一维数组的某个字段的值，找出这个一维数组，返回一维数组

function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $p = 3.1415926535898;
    $r = 6378.137;

    $radLat1 = $lat1 * ($p / 180);
    $radLat2 = $lat2 * ($p / 180);
    $a = $radLat1 - $radLat2;
    $b = ($lng1 * ($p / 180)) - ($lng2 * ($p / 180));
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $r;
    $s = round($s * 10000) / 10000;
    return round($s,2);
}
function change_sort($array,$field,$sort)
{
    $sort_field = array();
    foreach($array as $k => $v){
        $sort_field[] = $v[$field];
    }
    array_multisort($sort_field ,$sort ,$array);

    return $array;
}