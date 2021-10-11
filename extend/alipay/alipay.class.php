<?php
namespace alipay;

/**
 * 支付 逻辑定义
 * Class AlipayPayment
 * @package Home\Payment
 */

class alipay {

    public $tableName = 'plugin'; // 插件表        
    public $alipay_config = array();// 支付宝支付配置参数
    
    /**
     * 析构流函数
     */
    public function  __construct() {   
      
        
        $this->alipay_config['alipay_pay_method'] = '2'; // 1 使用担保交易接口  2 使用即时到帐交易接口s
        $this->alipay_config['partner']       = '2088531154918656';//合作身份者id，以2088开头的16位纯数字
        $this->alipay_config['seller_email']  = 'gzyx5558@163.com';//收款支付宝账号，一般情况下收款账号就是签约账号
        $this->alipay_config['key']	      = 'drbrjnmp3bz0sdkkf9euhm1xm587zz47';//安全检验码，以数字和字母组成的32位字符
        $this->alipay_config['transport']   = 'https';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http

    
        $this->alipay_config['sign_type']     = strtoupper('MD5');//签名方式 不需修改
        $this->alipay_config['input_charset'] = strtolower('utf-8');//字符编码格式 目前支持 gbk 或 utf-8
        $this->alipay_config['cacert']        = getcwd().'\\cacert.pem'; //ca证书路径地址，用于curl中ssl校验 //请保证cacert.pem文件在当前文件夹目录中
        
        if(!$this->alipay_config['alipay_pay_method']){
            $this->error('alipay_pay_method不存在');
        }
        if(!$this->alipay_config['partner']){
            $this->error('partner不存在');
        }
        if(!$this->alipay_config['seller_email']){
            $this->error('seller_email不存在');
        }
        if(!$this->alipay_config['key']){
            $this->error('key不存在');
        }
        if(!$this->alipay_config['transport']){
            $this->error('transport不存在');
        }
    }    
    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $config_value    支付方式信息
     */
    function get_code($order, $config_value)
    {         
             // 接口类型
            $service = array(             
                 1 => 'create_partner_trade_by_buyer', //使用担保交易接口
                 2 => 'create_direct_pay_by_user', //使用即时到帐交易接口
            );

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service" => $service[$this->alipay_config['alipay_pay_method']],   // 1 使用担保交易接口  2 使用即时到帐交易接口 
                "partner" => trim($this->alipay_config['partner']),
                "seller_email" => trim($this->alipay_config['seller_email']),
                "payment_type"	=> 1, // 默认值为：1（商品购买）。
                "notify_url"	=> $order['notify_url'] , //服务器异步通知页面路径 //必填，不能修改
                "return_url"	=>  $order['return_url'],  //页面跳转同步通知页面路径
                "out_trade_no"	=> $order['out_trade_no'], //商户订单号                        
                "subject"	=> $order['subject'], //订单名称 可以中文
                "total_fee"	=> $order['total_fee'], //付款金额
                "_input_charset"=> trim(strtolower($this->alipay_config['input_charset'])) //字符编码格式 目前支持 gbk 或 utf-8
            );

            //  如果是支付宝网银支付    
            if(!empty($config_value['bank_code']))
            {            
                $parameter["paymethod"] = 'bankPay'; // 若要使用纯网关，取值必须是bankPay（网银支付）。如果不设置，默认为directPay（余额支付）。
                $parameter["defaultbank"] = $config_value['bank_code'];
                $parameter["service"] = 'create_direct_pay_by_user';
            }        
           
            //建立请求
            include_once(__DIR__."/lib/alipay_submit.class.php");
           
            $alipaySubmit = new AlipaySubmit($this->alipay_config);

            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            return $html_text;         
    }
    
    /**
     * 服务器点对点响应操作给支付接口方调用
     * 
     */
    function response()
    {                

        require_once("lib/alipay_notify.class.php");  // 请求返回
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config); // 使用支付宝原生自带的累 和方法 这里只是引用了一下 而已
        $verify_result = $alipayNotify->verifyNotify();

        //if($verify_result) {
            //验证成功
            $order_sn = $out_trade_no = $_POST['out_trade_no']; //商户订单号                    
            $trade_no = $_POST['trade_no']; //支付宝交易号                   
            $trade_status = $_POST['trade_status']; //交易状态
            
            write_log("Payment line 118 trade_status ".$_POST['trade_status']);

            // 支付宝解释: 交易成功且结束，即不可再做任何操作。
            if($_POST['trade_status'] == 'TRADE_FINISHED') 
            {                         
                update_pay_status($order_sn); // 修改订单支付状态
            }
            //支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
            elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') 
            { 
                update_pay_status($order_sn); // 修改订单支付状态
            }
            echo "success"; // 告诉支付宝处理成功

        //}else {                
           // echo "fail"; //验证失败                                
       // }
    }
    
    /**
     * 页面跳转响应操作给支付接口方调用
     */
    function respond2()
    {
        require_once("lib/alipay_notify.class.php");  // 请求返回
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        //if($verify_result) {//验证成功

            $order_sn = $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no = $_GET['trade_no']; //支付宝交易号                   
            $trade_status = $_GET['trade_status']; //交易状态
            
            write_log("Payment line 154 trade_status ".$trade_status);

            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') 
            {                           
                return array('status'=>1,'order_sn'=>$order_sn);//跳转至成功页面
            }
            else {                        
                return array('status'=>0,'order_sn'=>$order_sn); //跳转至失败页面
            }               
        //}else {                     
        //   return array('status'=>0,'order_sn'=> $out_trade_no);//跳转至失败页面
        // }

    }
    
}