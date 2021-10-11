<?php

// 应用公共文件

use think\Db;
use think\Request;

/**
 * 单独获取药品名称
 */
function get_medicine_name($id)
{

    $medicine_name = Db::name("medicine_list")->where(['id' => $id])->value('medicine_name');

    return $medicine_name;
}
/**
 * 单独获取供应商名称
 */
function get_supplier_name($id)
{

    $name = Db::name("supplier_list")->where(['id' => $id])->value('name');

    return $name;
}
/**
 * 单独获取医护人员名称
 */
function get_worker_name($id)
{

    $username = Db::name("admin")->where(['id' => $id])->value('username');

    return $username;
}
/**
 * 单独获取药品价格
 */
function get_medicine_price($id)
{

    $price = Db::name("medicine_list")->where(['id' => $id])->value('price');

    return $price;
}
/**
 * 单独获取客户名称
 */
function get_users_name($id)
{

    $users_name = Db::name("users")->where(['user_id' => $id])->value('username');

    return $users_name;
}
/**
 * 检测库存
 */
function get_medicine_stock($id)
{
    $stock = Db::name("medicine_list")->where(['id' => $id])->value('stock');

    return $stock;
}
/**
 * 检测库存
 */
function get_medicine_nums($id)
{
    $stock = Db::name("medicine_list")->where(['id' => $id])->value('nums');

    return $stock;
}
/**
 * 公用减少库存
 */
function reduce_medicine_stock($id, $nums)
{
    $reduce_medicine_stock_res = Db::name("medicine_list")->where(['id' => $id])->setDec('stock', $nums);
    if ($reduce_medicine_stock_res) {
        return true;
    } else {
        return false;
    }
}
/**
 * 公用修改库存
 */
function update_medicine_stock($id, $nums, $stock)
{
    Db::name("medicine_list")->where(['id' => $id])->update(['stock' => $stock]);

    $reduce_medicine_stock_res = Db::name("medicine_list")->where(['id' => $id])->setDec('stock', $nums);
    if ($reduce_medicine_stock_res) {
        return true;
    } else {
        return false;
    }
}
/**
 * 公用减少库存
 */
function reduce_medicine_add_stock($id, $nums)
{
    $reduce_medicine_add_stock_res = Db::name("medicine_add_store")->where(['medicine_id' => $id])->setDec('nums', $nums);
    if ($reduce_medicine_add_stock_res) {
        return true;
    } else {
        return false;
    }
}
/**
 * 公用减少库存
 */
function get_medicine_add_stock($id)
{
    $stock = Db::name("medicine_add_store")->where(['medicine_id' => $id])->value('nums');

    return $stock;
}
/**
 * 接口返回数据
 */
function ajaxReturn($data)
{
    // header('Access-Control-Allow-Origin:*');
    // header('Access-Control-Allow-Headers:*');
    // header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
    header('Content-Type:application/json; charset=utf-8');

    $return = json_encode($data, JSON_UNESCAPED_UNICODE);
    exit(str_replace("\\/", "/", $return));
}
/**
 * 权限列表
 */
function staff_list()
{
    $list  = Db::name('role_list')->select();
    return $list;
}
/**
 * 发送短信
 */
function send_sms_chenxi($phone, $content)
{
    $url = 'http://120.25.105.164:8888/sms.aspx';
    $postfields['action'] = 'send';
    $postfields['userid'] = 3887;
    $postfields['account'] = 'oubisi';
    $postfields['password'] = 'oubisi123456';
    $postfields['mobile'] = $phone;
    $postfields['content'] = $content;
    $res = httpRequest($url,  "POST", $postfields);
    $xml = json_decode(json_encode((array) simplexml_load_string($res)), 1);
    return $xml;
}
/**
 * CURL请求
 * @param $url string 请求url地址
 * @param $method string 请求方法 get post
 * @param mixed $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method = "GET", $postfields = null, $headers = array(), $debug = false, $timeout = 60)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $timeout); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}
//输入地址获取经纬度（腾讯地图）
//小邓 --key值：CHEBZ-UTOK3-AW53X-3HHPD-VUU66-67BC5
function getAddress($address)
{
    header("Content-type:text/html;charset=utf-8");

    $ak = 'CHEBZ-UTOK3-AW53X-3HHPD-VUU66-67BC5'; //你腾讯地图的k值

    $address = $address;

    $url = "http://apis.map.qq.com/ws/geocoder/v1/?address={$address}&key={$ak}";

    $json = file_get_contents($url);

    $data = json_decode($json, TRUE);

    return $data['result']['location']; //获取地址的 经纬度 

}
/**
 * 验证邮箱格式
 */
function is_email($email)
{
    $pattern = "/^[^_][\w]*@[\w.]+[\w]*[^_]$/";
    if (preg_match($pattern, $email, $matches)) {
        return true;
    }
    return false;
}
