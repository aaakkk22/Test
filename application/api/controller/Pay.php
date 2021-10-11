<?php

namespace app\api\controller;

use app\api\logic\LogLogic;
use think\Db;

class Pay extends Base
{

    /**
     * 获取支付方式
     */
    public function get_pay_type()
    {
        $log = new LogLogic;
        $log->write_log('line in 17');

        $data = [
            'pay_type' => 'weixin',
            'pay_name' => '微信支付'
        ];

        ajaxReturn(['status' => 1, 'msg' => '请求成功', 'data' => $data]);
    }
    /**
     * 支付 微信支付
     * 2020.04.17
     */
    public function pay()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(array('status' => -1, 'msg' => 'token不能为空'));
        }

        $id = input('id'); //套餐id
        if (!$id) {
            ajaxReturn(array('status' => -1, 'msg' => '套餐id不能为空'));
        }

        $type = input('type');
        if (!$type) {
            ajaxReturn(array('status' => -1, 'msg' => '套餐type不能为空'));
        }

        //套餐查询
        if ($type == 1) {
            $price = Db::name('company_vip')->where(['id' => $id])->value('price');
            $description = Db::name('company_vip')->where(['id' => $id])->value('description');
            $orderBody = $price . '元' . '(' . $description . ')';
        }
        if ($type == 2) {
            $price = Db::name('job_seeker_vip_type')->where(['id' => $id])->value('price');
            $description = Db::name('job_seeker_vip_type')->where(['id' => $id])->value('description');
            $orderBody = $price . '元' . '(' . $description . ')';
        }
        if ($type == 3) {
            $buy_res = Db::name('buy')->where(['id' => $id])->find();
            $price = $buy_res['price'] + $buy_res['shipping_fee'];
            $orderBody = '购买产品(包含物流费在内)';
        }
        if ($type == 4) { //餐厅
            $buy_res = Db::name('buy')->where(['id' => $id])->find();
            $price = $buy_res['discount'];
            $orderBody = '预订餐厅' . $buy_res['description_second'];
        }
        if ($type == 5) { //酒店
            $buy_res = Db::name('buy')->where(['id' => $id])->find();
            $price = $buy_res['discount'];
            $orderBody = '预订酒店';
        }
        if ($type == 6) { //找律师
            $buy_res = Db::name('buy')->where(['id' => $id])->find();
            $price = $buy_res['price'] + $buy_res['shipping_fee'];
            $orderBody = '律师咨询费用';
        }
        if ($type == 7) {
            $buy_res = Db::name('buy')->where(['id' => $id])->find();
            $price = $buy_res['price'] + $buy_res['shipping_fee'];
            $orderBody = '购买产品(包含物流费在内)';
        }
        $order_sn = date('YmdHis', time()) . rand(11111, 99999); //订单编号

        $total_fee = $price * 100;

        if (!$total_fee || $total_fee < 0) {
            ajaxReturn(['status' => -1, 'msg' => 'total_fee不存在，或者小于0']);
        }

        //获取配置
        $config = Db::name('config')
            ->where(['id' => 1])
            ->field('wepro_appid as appid,wepro_appsecret as appsecret,wepro_partnerid as mchid,wepro_key')->find();

        $config['key'] = $config['wepro_key'];

        //一个也不能缺
        if (!$config['appid']) {
            ajaxReturn(['status' => -1, 'msg' => 'appid支付未配置']);
        }
        if (!$config['appsecret']) {
            ajaxReturn(['status' => -1, 'msg' => 'appsecret支付未配置']);
        }
        if (!$config['mchid']) {
            ajaxReturn(['status' => -1, 'msg' => 'mchid支付未配置']);
        }
        if (!$config['key']) {
            ajaxReturn(['status' => -1, 'msg' => 'key支付未配置']);
        }
        //dump($config) ;die;

        $config['mch_id'] = $config['mchid'];

        $openid = Db::name('user')->where(array('user_id' => $user_id))->value('openid');

        if (!$openid) {
            ajaxReturn(['status' => -1, 'msg' => 'openid不存在']);
        }
        //out_trade_no
        $out_trade_no = date('YmdHis', time()) . rand(11111, 99999);
        //插入order
        $order_res = Db::name('order')->where(['vip_id' => $id, 'type' => $type, 'user_id' => $user_id])->find();

        if (!$order_res) {
            $data = [
                'order_sn' => $order_sn,
                'user_id'  => $user_id,
                'order_status' => 1,
                'pay_status' => 0,
                'shipping_status' => 0,
                'type' => $type,
                'pay_code' => '',
                'pay_name' => '',
                'out_trade_no' => $out_trade_no,
                'price' => $price,
                'user_money' => $price,
                'order_amount' => $price,
                'add_time' => time(),
                'vip_id' => $id,
                'is_delete' => 0
            ];

            Db::name('order')->insert($data);
        } else {
            $out_trade_no = $order_res['out_trade_no'];
            if (!$out_trade_no) {
                ajaxReturn(['status' => -1, 'msg' => 'out_trade_no不存在']);
            }
        }

        $params = [
            'body' => $orderBody,
            'out_trade_no' => $out_trade_no,
            'total_fee' => (int) $total_fee,
            'notify_url' => SITE_URL . '/api/pay/notify_url',
            'appid' => $config['appid'],
            'mch_id' => $config['mchid'],
            'key' => $config['key'],
            'app_secret' => $config['appsecret'],
        ];

        //密钥错误
        if (strlen($config['key']) != 32) {
            ajaxReturn(['status' => -1, 'msg' => '密钥错误']);
        }
        try {

            $x = \wxpay\JsapiPay::getParams($params, $openid);
            $x = json_decode($x, true);
            $data1['wdata'] = $x;
            $data1['pay_money'] = $price;
            $return_arr = array('status' => 1, 'msg' => '成功', 'result' => $data1); // 返回结果状态
            ajaxReturn($return_arr);
        } catch (\Exception $e) {

            $params = [
                'appid' => $config['appid'],
                'mch_id' => $config['mchid'],
                // 随机串，32字符以内
                'nonce_str' => md5(time()),
                // 商品名
                'body' => $orderBody,
                // 订单号，自定义，32字符以内。多次支付时如果重复的话，微信会返回“重复下单”
                'out_trade_no' => $out_trade_no,
                // 订单费用，单位：分
                'total_fee' => (int) $total_fee,
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
                // 支付成功后的回调地址，服务端不一定真得有这个地址
                'notify_url' => SITE_URL . '/api/pay/notify_url',
                'trade_type' => 'JSAPI',
                // 小程序传来的OpenID
                'openid' => $openid,
            ];

            //再次支付
            $r = $this->xcx_pay_again($params, $config['key']);

            if (isset($r['package'])) {
                $data1['wdata'] = $r;
                $data1['pay_money'] = $price;
                $return_arr = array('status' => 1, 'msg' => '成功', 'result' => $data1); // 返回结果状态
                ajaxReturn($return_arr);
            }

            $msg_res = $e->getMessage();
            $msg_array = explode(":", $msg_res);
            $msg = $msg_array[count($msg_array) - 1];
            $msg = trim($msg);
            ajaxReturn(['status' => -1, 'msg' => $msg, 'line' => $e->getLine()]);
        }
    }

    /**
     * 小程序支付
     */
    public function xcx_pay_again($params, $mch_key)
    {

        // 按照要求计算sign

        ksort($params);
        $sequence = '';
        foreach ($params as $key => $value) {
            $sequence .= "$key=$value&";
        }

        $sequence = $sequence . "key=" . $mch_key;

        $params['sign'] = strtoupper(md5($sequence));

        // 给微信发出的请求，整个参数是个XML

        $xml = '<xml>' . PHP_EOL;
        foreach ($params as $key => $value) {
            $xml .= "<$key>$value</$key>" . PHP_EOL;
        }
        $xml .= '</xml>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.mch.weixin.qq.com/pay/unifiedorder');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);

        if (false === $output) {
            return 'CURL Error:' . curl_error($ch);
        }

        // 下单成功的话，微信返回个XML，里面包含prepayID，提取出来
        if (0 === preg_match('/<prepay_id><\!\[CDATA\[(\w+)\]\]><\/prepay_id>/', $output, $match)) {
            return $output;
        }

        // 这里不是给小程序返回个prepayID，而是返回一个包含其他字段的JSON
        // 这个JSON小程序自己也可以生成，放在服务端生成是出于两个考虑：
        //
        // 1. 小程序的appid不用写在小程序的代码里，appid、secret信息全部由服务器管理，比较安全
        // 2. 计算paySign需要用到md5，小程序端使用的是JavaScript，没有内置的md5函数，放在服务端计算md5比较方便

        $prepayId = $match[1];

        $response = [
            'appId' => $params['appid'],
            // 随机串，32个字符以内
            'nonceStr' => md5(time()),
            // 微信规定
            'package' => 'prepay_id=' . $prepayId,
            'signType' => 'MD5',
            // 时间戳，注意得是字符串形式的
            'timeStamp' => (string) time(),
        ];
        $sequence = '';
        foreach ($response as $key => $value) {
            $sequence .= "$key=$value&";
        }
        $response['paySign'] = strtoupper(md5("{$sequence}key=" . $mch_key));

        return $response;
    }


    /**
     * 回调
     */
    public function notify_url()
    {

        $postStr = file_get_contents('php://input');

        $getData = $this->xmlstr_to_array($postStr); // 为了方便我就直接把结果转成数组，看个人爱好了

        $log = new LogLogic;
        $log->write_log('line in 269', json_encode($getData));

        if ('SUCCESS' == $getData['result_code']) {
            $transaction_id = $getData['transaction_id'];

            $osn = trim($getData['out_trade_no']);
            $log->write_log('line in 274', $osn);

            $res = Db::name('order')->where(array('out_trade_no' => $osn))->find();
            $log->write_log('line in 288', $res['pay_status']);

            if ($res && 0 == $res['pay_status']) {
                Db::name('order')->where(array('out_trade_no' => $osn))
                    ->update(
                        [
                            'pay_status' => 1,
                            'pay_time' => time(),
                            'pay_name' => "微信支付",
                            'pay_code' => "wechat",
                            'transaction_id' => $transaction_id,
                            'out_trade_no' => $osn,
                            'order_sn' => $osn
                        ]
                    );
                $log->write_log('line in 288' . $res);
                //用户信息
                $user_res = Db::name('user')->where(['user_id' => $res['user_id']])->find();

                //会员类型 身份
                if ($res['type'] == 1) {

                    $company_vip_res = Db::name('company_vip')->where(['id' => $res['vip_id']])->find();

                    if ($company_vip_res['description'] == '一个月免费发布') {
                        $time = time() + 2678400; //一个月时间
                    }
                    if ($company_vip_res['description'] == '半年免费发布') {
                        $time = time() + 15768000; //半年时间
                    }
                    if ($company_vip_res['description'] == '一年免费发布') {
                        $time = time() + 31536000; //一年时间
                    }

                    $company_data = [
                        'user_id' => $res['user_id'],
                        'is_validated' => 1,
                        'company_name' => '',
                        'company_vip_type' => $res['vip_id'],
                        'company_expire_time' => $time,
                        'add_time' => time()
                    ];
                    Db::name('company')->insert($company_data);
                }
                if ($res['type'] == 2) {

                    $seeker_vip_description = Db::name('job_seeker_vip_type')->where(['id' => $res['vip_id']])->value('description');

                    if ($seeker_vip_description == '半年免费观看招工视频') {
                        $time = time() + 15768000; //半年时间
                    }
                    if ($seeker_vip_description == '一年免费观看招工视频') {
                        $time = time() + 31536000; //一年时间
                    }
                    $job_seeker_data = [
                        'user_id' => $res['user_id'],
                        'realname' => '',
                        'seeker_vip_type' => $res['vip_id'],
                        'seeker_vip_expire_time' => $time,
                        'add_time' => time()
                    ];
                    Db::name('job_seeker')->insert($job_seeker_data);
                }

                if ($res['type'] == 3) {

                    //下单人信息
                    $buy_res = Db::name('buy')->where(['id' => $res['vip_id']])->find();

                    //修改订单的
                    $buy_data = [
                        'order_id' => $res['order_id']
                    ];
                    Db::name('buy')->where(['id' => $res['vip_id']])->update($buy_data);
                }
                if ($res['type'] == 4) {
                    //下单人信息
                    $buy_res = Db::name('buy')->where(['id' => $res['vip_id']])->find();
                    //修改选菜的
                    Db::name('user_menu')->where(['user_id' => $buy_res['user_id'], 'video_id' => $buy_res['video_id']])->update(['is_deleted' => 1]);
                    //修改订单的
                    $buy_data = [
                        'order_id' => $res['order_id']
                    ];
                    Db::name('buy')->where(['id' => $res['vip_id']])->update($buy_data);
                }
                if ($res['type'] == 5) {
                    //下单人信息
                    $buy_res = Db::name('buy')->where(['id' => $res['vip_id']])->find();

                    //修改订单的
                    $buy_data = [
                        'order_id' => $res['order_id']
                    ];
                    Db::name('buy')->where(['id' => $res['vip_id']])->update($buy_data);
                }
                if ($res['type'] == 7) {
                    //下单人信息
                    $buy_res = Db::name('buy')->where(['id' => $res['vip_id']])->find();

                    //修改订单的
                    $buy_data = [
                        'order_id' => $res['order_id']
                    ];
                    Db::name('buy')->where(['id' => $res['vip_id']])->update($buy_data);
                }
                ajaxReturn(['status' => 1, 'msg' => '支付成功', 'result' => '']);
            } else {
                ajaxReturn(['status' => -1, 'msg' => '支付失败', 'result' => '']);
            }
        }
    }

    /**
     * xml转成数组.
     */
    public function xmlstr_to_array($xmlstr)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }
}
