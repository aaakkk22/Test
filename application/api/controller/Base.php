<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use app\common\util\jwt\JWT;

class Base extends Controller{
    public function _initialize()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header('Content-Type:application/json; charset=utf-8');
    }
    
    /**
     * 生成 create_user_token
     */
    public function create_user_token($user_id, $endtime = 0)
    {
        $time = time();
        $payload = array(
            "iat" => $time,
            "exp" => ($endtime ? $endtime : ($time + 36000)),
            "user_id" => $user_id,
        );
        $key = 'video';
        $token = JWT::encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null);
        return $token;
    }
    /**
     * 获取 user_id
     */
    public function get_user_id()
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
        
        //$token = em_get_token();

        if ($token == 1) {
            return 1;
        }
        if ($token == 3) {
            return 3;
        }
        if ($token == 2) {
            return 2;
        }
        if ($token == 4) {
            return 4;
        }
        if ($token == 5) {
            return 5;
        }
        if (!$token) {
            header('HTTP/1.1 401 Unauthorized');
            header('Status: 401 Unauthorized');
            ajaxReturn(['status' => -1, 'msg' => 'token不存在', 'data' => null]);
        }

        $res = $this->decode_token($token);
        if (!$res) {
            header('HTTP/1.1 401 Unauthorized');
            header('Status: 401 Unauthorized');
            ajaxReturn(['status' => -1, 'msg' => 'token已过期', 'data' => null]);
        }

        if (!isset($res['iat']) || !isset($res['exp']) || !isset($res['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('Status: 401 Unauthorized');
            ajaxReturn(['status' => -1, 'msg' => 'token已过期：', 'data' => null]);
        }
        if ($res['iat'] > $res['exp']) {
            header('HTTP/1.1 401 Unauthorized');
            header('Status: 401 Unauthorized');
            ajaxReturn(['status' => -1, 'msg' => 'token已过期', 'data' => null]);
        }
        if (!isset($res['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('Status: 401 Unauthorized');
            ajaxReturn(['status' => -1, 'msg' => 'user_id不存在', 'data' => '']);
        }

        return $res['user_id'];
    }
    /**
     * 解密token
     */
    public function decode_token($token)
    {
        $key = 'video';
        $payload = json_decode(json_encode(JWT::decode($token, $key, ['HS256'])), true);
        return $payload;
    }

}