<?php

namespace app\api\controller;

use think\Db;
use app\common\logic\SmsLogic;
use Exception;
use think\Redis;
use app\common\logic\OssLogic;
use app\common\logic\MyLogic;

class My extends Base
{
    /**
     * 我的
     *
     * @Author DSJ
     * @DateTime 2020-04-15 17:26:49
     * @param token 用户名
     * @param page 默认页数 1
     * @param limit 条数 10
     */
    public function index()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $page = input('page', 1);
        $limit = input('limit', 10);
        //个人中心
        $my_res = Db::name('user')->where(['user_id' => $user_id])->field('user_id,nickname,avatar,is_black,mobile')->find();
        if ($my_res['is_black'] == 1) {
            $my_res['black_name'] = '禁用';
        } else {
            $my_res['black_name'] = '启用';
        }
        //关注次数
        $follow_total = Db::name('user_follow')->where(['user_id' => $user_id])->count();

        //我的作品
        $video = Db::name('video')
            ->where(['user_id' => $user_id, 'status' => 1])
            ->order('add_time desc')
            ->page($page, $limit)
            ->select();
        foreach ($video as $k => $v) {
            //点赞数
            $like_num  = Db::name('user_like')->where(['video_id' => $v['id']])->count();
        }
        //是否vip
        $vip_res = Db::name('company')->where(['user_id' => $user_id])->find();
        if ($vip_res) {
            $vip_status = true;
            $vip_cn = '黄金VIP(发布)';
            $come_time = date('Y-m-d', $vip_res['company_expire_time']);
        } else {
            $vip_status = false;
        }
        //用户和企业vip
        $vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find();
        if ($vip_res) {
            $vip_status_second = true;
            $vip_cn_second = '尊贵VIP(观看)';
            $come_time_second = date('Y-m-d', $vip_res['seeker_vip_expire_time']);
        } else {
            $vip_status_second = false;
        }
        //点赞数
        $like_num = $like_num;
        //开关
        $on_status = Db::name('config')->where(['id' => 1])->value('is_show');
        //电话号码
        $mobile = Db::name('config')->where(['id' => 1])->value('mobile');
        //数据
        $result = [
            'my'                => $my_res,
            'like_num'          => $like_num,
            'follow_total'      => $follow_total,
            'vip_status'        => $vip_status,
            'vip_cn'            => $vip_cn,
            'come_time'         => $come_time,
            'vip_status_second' => $vip_status_second,
            'vip_cn_second'     => $vip_cn_second,
            'come_time_second'  => $come_time_second,
            'on_status'         => $on_status,
            'mobile'            => $mobile
        ];
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $result]);
    }
    /**
     * 编辑个人资料
     *
     * @Author DSJ
     * @DateTime 2020-04-15 17:29:12
     * @param token 用户名
     */
    public function my_data()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        //个人资料
        $my_res = Db::name('user')->where(['user_id' => $user_id])->field('nickname,avatar,is_black,mobile,account')->find();
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $my_res]);
    }
    /**
     * 编辑操作
     *
     * @Author DSJ
     * @DateTime 2020-04-15 17:29:53
     * @param token  用户名
     * @param nickname 昵称
     * @param avatar 头像
     */
    public function my_edit()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $nickname = input('nickname');
        if (!$nickname) {
            ajaxReturn(['status' => -1, 'msg' => 'nickname参数不存在', 'data' => '']);
        }
        $avatar = input('avatar');
        if (!$avatar) {
            ajaxReturn(['status' => -1, 'msg' => 'avatar参数不存在', 'data' => '']);
        }

        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->my_edit($user_id, $nickname, $avatar);
        ajaxReturn($result);
    }
    /**
     * 商家开户卡号
     *
     * @Author DSJ
     * @DateTime 2020-06-30 16:43:42
     * @param undefined
     * @return void
     */
    public function edit_store()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }

        $account = input('account');
        if (!$account) {
            ajaxReturn(['status' => -1, 'msg' => 'account参数不存在', 'data' => '']);
        }
        $kaihu = input('kaihu');
        if (!$kaihu) {
            ajaxReturn(['status' => -1, 'msg' => 'kaihu参数不存在', 'data' => '']);
        }
        $kaihu_hang = input('kaihu_hang');
        if (!$kaihu_hang) {
            ajaxReturn(['status' => -1, 'msg' => 'kaihu_hang参数不存在', 'data' => '']);
        }
        $user_update = [
            'account'  => $account,
            'kaihu'    => $kaihu,
            'kaihu_hang'  => $kaihu_hang
        ];
        $user_update_res = Db::name('user')->where(['user_id' => $user_id])->update($user_update);
        if ($user_update_res) {
            ajaxReturn(['status' => 1, 'msg' => '编辑成功', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '编辑失败', 'data' => '']);
        }
    }
    /**
     * 发布视频
     *
     * @Author DSJ
     * @DateTime 2020-04-17 17:31:34
     * @param token 用户名
     * @param type  类型 1招人 2找工作 3个人特色产品
     * @param cover_image 封面
     * @param video_url 视频连接
     * @param content 内容
     * @param city 城市
     */
    public function send_video()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        $cover_image = input('cover_image');
        // if (!$cover_image) {
        //     ajaxReturn(['status' => -1, 'msg' => 'cover_image参数不存在', 'data' => '']);
        // }
        $video_url = input('video_url');
        if (!$video_url) {
            ajaxReturn(['status' => -1, 'msg' => 'video_url参数不存在', 'data' => '']);
        }
        $content = input('content');
        if (!$content) {
            ajaxReturn(['status' => -1, 'msg' => 'content参数不存在', 'data' => '']);
        }
        $city = input('city');
        if (!$city) {
            ajaxReturn(['status' => -1, 'msg' => 'city参数不存在', 'data' => '']);
        }
        $lat = input('lat'); //weidu
        if (!$lat) {
            ajaxReturn(['status' => -1, 'msg' => 'lat参数不存在', 'data' => '']);
        }
        $lng = input('lng'); //jingdu
        if (!$lng) {
            ajaxReturn(['status' => -1, 'msg' => 'lng参数不存在', 'data' => '']);
        }
    
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->video($type,$user_id,$city,$cover_image,$video_url,$content,$lat,$lng);
        ajaxReturn($result);
    }
    /**
     * 充值套餐
     * 2020.04.17
     */
    public function member_meun()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        if ($type == 1) { //招人
            $vip_res = Db::name('company_vip')->select();
            $num = Db::name('config')->where(['id' => 1])->value('company_limit_num');
        }
        if ($type == 2) { //找工作
            $vip_res = Db::name('job_seeker_vip_type')->select();
            $num = Db::name('config')->where(['id' => 1])->value('limit_num');
        }
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => array('vip_res' => $vip_res, 'num' => $num)]);
    }
    /**
     * 去关注
     * 2020.04.18
     */
    public function user_follow()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $follow_user_id = input('user_id'); //关注用户id
        if (!$follow_user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'user_id参数不存在', 'data' => '']);
        }
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->user_follow($user_id, $follow_user_id);
        ajaxReturn($result);
    }
    /**
     * 去点赞
     * 2020.04.18
     */
    public function user_like()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->user_like($user_id, $video_id);
        ajaxReturn($result);
    }
    /**
     * 发布评论
     * 2020.04.18
     */
    public function add_comment()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $content = input('content');
        if (!$content) {
            ajaxReturn(['status' => -1, 'msg' => 'content参数不存在', 'data' => '']);
        }
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->add_comment($user_id, $video_id, $content);
        ajaxReturn($result);
    }
    /**
     * 评论去点赞
     * 2020.04.18
     */
    public function comment_like()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $comment_id = input('comment_id');
        if (!$comment_id) {
            ajaxReturn(['status' => -1, 'msg' => 'comment_id参数不存在', 'data' => '']);
        }
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->comment_like($user_id, $comment_id);
        ajaxReturn($result);
    }
    /**
     * 观看视频
     * 2020.04.18
     */
    public function watch_video()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $user_view = Db::name('user_view')->where(['video_id' => $video_id])->find();
        if (!$user_view) {
            $view_data = [
                'video_id' => $video_id,
                'user_id'  => $user_id,
                'add_time' => time()
            ];
            Db::name('user_view')->insert($view_data);
        }
    }
    /**
     * 更新用户信息
     * 2020.04.18
     */
    public function userinfo()
    {
        $user_id = $this->get_user_id(); //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $user_res = Db::name('user')->where(['user_id' => $user_id])->find();

        $post = input('');

        if ($user_res['avatar'] == 'https://www.c3w.com.cn/public/images/avatar.png') {
            isset($post['avatar']) ? $update['avatar'] = $post['avatar'] : "";
        }
        if ($user_res['nickname'] == '请点击授权') {
            isset($post['nickname']) ? $update['nickname'] = $post['nickname'] : "";
            $update['status'] = 0;
        } else {
            $update['nickname'] = $user_res['nickname'];
            $update['status'] = 1;
        }

        isset($post['city']) ? $update['city'] = $post['city'] : "";

        Db::name('user')->where(['user_id' => $user_id])->update($update);

        ajaxReturn(['status' => 1, 'msg' => '更新成功', 'data' => '']);
    }
    /**
     * 绑定手机号码
     */
    public function bind_phone()
    {
        $user_id = $this->get_user_id();
        $phone = input('phone');
        if (empty($phone)) {
            ajaxReturn(['status' => -1, 'msg' => '手机号不能为空']);
        }
        $code = input('code');
        if (empty($code)) {
            ajaxReturn(['status' => -1, 'msg' => '验证码不能为空']);
        }

        $temp = Db::name('sms_log')->where(['phone' => $phone, 'status' => 0])->find();
        $between = time() - $temp['add_time'];
        $between = date('i', $between);
        if ($between > 5) {
            ajaxReturn(['status' => -1, 'msg' => '验证码过期,请重新发送！']);
        }
        if ($code == $temp['code']) {
            Db::name('sms_log')->where(['id' => $temp['id']])->update(['status' => 1]);

            Db::name('user')->where('user_id', $user_id)->update(['mobile' => $phone]);

            ajaxReturn(['status' => 1, 'msg' => '绑定成功！']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '验证码错误！']);
        }
    }
    /**
     * 发送验证码
     */
    public function send_code()
    {
        $phone = input('phone');
        if (!$phone) {
            ajaxReturn(['status' => -1, 'msg' => 'phone不能为空']);
        }
        //开启逻辑
        $logic = new MyLogic;
        $result = $logic->send_code($phone);
        ajaxReturn($result);
    }
    /**
     * 删除自己作品
     * 2020.04.24
     */
    public function delete_video()
    {
        $video_id = input('video_id');
        if (empty($video_id)) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在!']);
        }
        $delete_video = Db::name('video')->where(['id' => $video_id])->delete();
        if ($delete_video) {
            Db::name('user_like')->where(['video_id' => $video_id])->delete();
            ajaxReturn(['status' => 1, 'msg' => '删除成功!', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '删除失败!', 'data' => '']);
        }
    }
    /**
     * 单独上传照片
     * 2020.06.03
     */
    public function upload_image()
    {

        $file = request()->file('file'); //获取上传文件信息
        if ($file) {
            $ext = strrchr($_FILES['file']['name'], '.');
            $name = md5(time()) . round(10000, 99999) . $ext;
            $fileName = 'video_xcx/' . date("Ymd") . '/' . $name;
            $ossClient = new OssLogic();
            $info = $ossClient->uploadFile($_FILES['file']['tmp_name'], $fileName);
        }

        if ($info) {
            ajaxReturn(array('status' => 1, 'msg' => '成功！', 'data' => $info));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '失败！', 'data' => ''));
        }
    }
    /**
     * 申请退款退货
     * 2020.06.03
     */
    public function return_goods_text()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => 2, 'msg' => '缺少token', 'result' => '']);
        }

        $order_id = input('order_id');
        if (!$order_id) {
            ajaxReturn(['status' => -1, 'msg' => '缺少order_id', 'result' => '']);
        }

        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在']);
        }

        $where['order_id'] = $order_id;
        $where['user_id'] = $user_id;
        //是否有该订单
        $c = Db::name('order')->where($where)->count();
        if (0 == $c) {
            ajaxReturn(['status' => -1, 'msg' => '订单不存在!']);
        }

        $type = input('type'); // 0 退货  1为换货

        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => '缺少type', 'result' => '']);
        }

        $reason = input('reason'); // 问题描述
        if (!$reason) {
            ajaxReturn(['status' => -1, 'msg' => '请填写退款原因']);
        }

        $img = input('img');
        if (!$img) {
            ajaxReturn(['status' => -1, 'msg' => 'img参数不存在']);
        }

        $img =  htmlspecialchars_decode($img);

        $img = json_decode($img, true);

        // if (!is_array($img)) {
        //     ajaxReturn(['status' => -1, 'msg' => '上传照片格式不正确!']);
        // }

        //合成字符串
        $image = implode(",", $img);

        //order_sn
        $order_sn = Db::name('order')->where($where)->value('order_sn');
        $return_goods = Db::name('return_goods')->where("order_id = $order_id")->find();

        $data['order_id'] = $order_id;
        $data['add_time'] = time();
        $data['user_id'] = $user_id;
        $data['type'] = $type; // 服务类型  退货 或者 换货
        $data['reason'] = $reason; // 问题描述
        $data['imgs'] = $image; // 问题描述
        $data['video_id'] = $video_id; // 问题描述
        $data['order_sn'] = $order_sn; // 问题描述
        $data['status'] = 0; // 问题描述

        if (!$return_goods) {
            //新增

            $id = Db::name('return_goods')->insertGetId($data);
            Db::name('order')->where($where)->update(array('order_status' => 2));
            Db::name('buy')->where($where)->update(array('meal_status' => 1));
            Db::name('user_menu')->where(['user_id' => $user_id, 'video_id' => $video_id])->update(array('is_deleted' => 2));

            $user_res = Db::name("user")->where(['user_id' => $user_id])->find();
            $return_goods_res = Db::name('return_goods')->where("id = $id")->find();
            $time = date('Y年m月d日', $return_goods_res['add_time']);

            //商家手机号码
            $store_mobile = Db::name('video')->alias('a')
                ->join('yx_user b', 'a.user_id = b.user_id')
                ->field('b.mobile')
                ->where(['a.id' => $return_goods_res['video_id']])
                ->find();
            //发送短信退款
            //发送短信通知商家
            // 大中小企业直聘】（大中小企业直聘）xxx用户于xx年xx月× x日已退单，请商家查看
            //新款发送短信
            $content = '【大中小企业直聘】' . $user_res['nickname'] . '用户于' . $time . '已退单，请商家查看。';
            $res = send_sms_chenxi($store_mobile['mobile'], $content);
            if ($res['returnstatus'] == 'Success') {
                $data = [
                    'user_id' => $user_id,
                    'desc' => $content,
                    'add_time' => time()
                ];
                Db::name('account_log')->insert($data);
            }
        } else {
            $id = $return_goods['id'];
            //编辑
            Db::name('return_goods')->where(array('id' => $id))->update($data);
        }

        if ($id) {
            ajaxReturn(['status' => 1, 'msg' => '申请成功', 'result' => '']);
        }
    }
    /**
     * 客服电话
     *
     * @Author DSJ
     * @DateTime 2020-06-30 14:53:28
     * @param undefined
     * @return void
     */
    public function contact()
    {
        //客服电话
        $config = Db::name('contact')->order('id desc')->select();

        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'result' => $config]);
    }
    /*
     * 购买信息填写
     * 2020.05.27
     * dsj
     */
    public function buy()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $username = input('username');
        if (!$username) {
            ajaxReturn(['status' => -1, 'msg' => 'username参数不存在', 'data' => '']);
        }
        $phone = input('phone');
        if (!$phone) {
            ajaxReturn(['status' => -1, 'msg' => 'phone参数不存在', 'data' => '']);
        }
        $address = input('address');
        if (!$address) {
            ajaxReturn(['status' => -1, 'msg' => 'address参数不存在', 'data' => '']);
        }
        $remark = input('remark');
        if (!$remark) {
            ajaxReturn(['status' => -1, 'msg' => 'remark参数不存在', 'data' => '']);
        }
        $price = input('price');
        if (!$price) {
            ajaxReturn(['status' => -1, 'msg' => 'price参数不存在', 'data' => '']);
        }
        $shipping_fee = input('shipping_fee');
        if (!$shipping_fee) {
            ajaxReturn(['status' => -1, 'msg' => 'shipping_fee参数不存在', 'data' => '']);
        }
        //本视频是否是自己user_id
        $video_res = Db::name('video')->where(['id' => $video_id, 'user_id' => $user_id])->find();
        if ($video_res) {
            ajaxReturn(['status' => -1, 'msg' => '该视频是自己本人，无法下单！', 'data' => '']);
        }
        //获取类型
        $type = Db::name('video')->where(['id' => $video_id])->value('type');
        //添加数据
        $data = [
            'user_id' => $user_id,
            'video_id' => $video_id,
            'type' => $type,
            'username' => $username,
            'phone' => $phone,
            'address' => $address,
            'remark' => $remark,
            'price' => $price,
            'shipping_fee' => $shipping_fee,
            'status' => 0,
            'add_time' => time()
        ];
        // dump($data);
        // die;
        $buy_insert_res = Db::name('buy')->insertGetId($data);
        if ($type == 3 || $type == 7) {

            //下单人信息
            $buy_res = Db::name('buy')->where(['id' => $buy_insert_res])->find();

            //用户信息
            $user_res = Db::name('user')->where(['user_id' => $user_id])->find();

            $time = date('Y年m月d日', $buy_res['add_time']);

            //商家手机号码
            $store_mobile = Db::name('video')->alias('a')
                ->join('yx_user b', 'a.user_id = b.user_id')
                ->field('b.mobile')
                ->where(['a.id' => $buy_res['video_id']])
                ->find();
            //发送短信通知商家
            // 大中小企业直聘】下单成功，用户XXX于XX年XX月XX日预订了餐厅，请商家接单。流程:我的－我的订单－我收到的－接单
            //新款发送短信
            $content = '【大中小企业直聘】下单成功，用户' . $user_res['nickname'] . '于' . $time . '预订了产品，请商家接单。流程：我的－我的订单－我收到的－接单';
            $res = send_sms_chenxi($store_mobile['mobile'], $content);
            if ($res['returnstatus'] == 'Success') {
                $data = [
                    'user_id' => $user_id,
                    'desc' => $content,
                    'add_time' => time()
                ];
                Db::name('account_log')->insert($data);
            }
        }
        if ($buy_insert_res) {
            ajaxReturn(['status' => 1, 'msg' => '填写成功!', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '填写失败!', 'data' => '']);
        }
    }
    /**
     * 填写信息-订酒店
     *
     * @Author DSJ
     * @DateTime 2020-06-10 18:55:51
     * @param undefined
     * @return void
     */
    public function buy_hotel()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $username = input('username');
        if (!$username) {
            ajaxReturn(['status' => -1, 'msg' => 'username参数不存在', 'data' => '']);
        }
        $phone = input('phone');
        if (!$phone) {
            ajaxReturn(['status' => -1, 'msg' => 'phone参数不存在', 'data' => '']);
        }
        $credit_card = input('credit_card');
        if (!$credit_card) {
            ajaxReturn(['status' => -1, 'msg' => 'credit_card参数不存在', 'data' => '']);
        }
        $remark = input('remark');
        if (!$remark) {
            ajaxReturn(['status' => -1, 'msg' => 'remark参数不存在', 'data' => '']);
        }
        $price = input('price'); //价格
        if (!$price) {
            ajaxReturn(['status' => -1, 'msg' => 'price参数不存在', 'data' => '']);
        }
        $room_num = input('room_num');
        if (!$room_num) {
            ajaxReturn(['status' => -1, 'msg' => 'room_num参数不存在', 'data' => '']);
        }
        $room_time = input('room_time');
        if (!$room_time) {
            ajaxReturn(['status' => -1, 'msg' => 'room_time参数不存在', 'data' => '']);
        }
        $total_money = input('total_money');
        if (!$total_money) {
            ajaxReturn(['status' => -1, 'msg' => 'total_money参数不存在', 'data' => '']);
        }
        $discount = input('discount');
        if (!$discount) {
            ajaxReturn(['status' => -1, 'msg' => 'discount参数不存在', 'data' => '']);
        }
        //本视频是否是自己user_id
        $video_res = Db::name('video')->where(['id' => $video_id, 'user_id' => $user_id])->find();
        if ($video_res) {
            ajaxReturn(['status' => -1, 'msg' => '该视频是自己本人，无法下单！', 'data' => '']);
        }
        //获取类型
        $type = Db::name('video')->where(['id' => $video_id])->value('type');
        //添加数据
        $data = [
            'user_id' => $user_id,
            'video_id' => $video_id,
            'type' => $type,
            'username' => $username,
            'phone' => $phone,
            'credit_card' => $credit_card,
            'remark' => $remark,
            'price' => $price,
            'room_time' => $room_time,
            'room_num' => $room_num,
            'total_money' => $total_money,
            'discount' => $discount,
            'status' => 0,
            'add_time' => time()
        ];
        // dump($data);
        // die;
        $buy_insert_res = Db::name('buy')->insertGetId($data);

        if ($type == 5) {

            //下单人信息
            $buy_res = Db::name('buy')->where(['id' => $buy_insert_res])->find();

            //用户信息
            $user_res = Db::name('user')->where(['user_id' => $user_id])->find();

            $time = date('Y年m月d日', $buy_res['add_time']);

            //商家手机号码
            $store_mobile = Db::name('video')->alias('a')
                ->join('yx_user b', 'a.user_id = b.user_id')
                ->field('b.mobile')
                ->where(['a.id' => $buy_res['video_id']])
                ->find();
            //发送短信通知商家
            // 大中小企业直聘】下单成功，用户XXX于XX年XX月XX日预订了餐厅，请商家接单。流程:我的－我的订单－我收到的－接单
            //新款发送短信
            $content = '【大中小企业直聘】下单成功，用户' . $user_res['nickname'] . '于' . $time . '预订了酒店，请商家接单。流程：我的－我的订单－我收到的－接单';
            $res = send_sms_chenxi($store_mobile['mobile'], $content);
            if ($res['returnstatus'] == 'Success') {
                $data = [
                    'user_id' => $user_id,
                    'desc' => $content,
                    'add_time' => time()
                ];
                Db::name('account_log')->insert($data);
            }
        }
        if ($buy_insert_res) {
            ajaxReturn(['status' => 1, 'msg' => '填写成功!', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '填写失败!', 'data' => '']);
        }
    }
    /**
     * 填写信息-订餐厅
     *
     * @Author DSJ
     * @DateTime 2020-06-10 18:55:51
     * @param undefined
     * @return void
     */
    public function buy_restaurant()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $username = input('username');
        if (!$username) {
            ajaxReturn(['status' => -1, 'msg' => 'username参数不存在', 'data' => '']);
        }
        $phone = input('phone');
        if (!$phone) {
            ajaxReturn(['status' => -1, 'msg' => '手机号码不能为空', 'data' => '']);
        }
        $meals_time = input('meals_time');
        if (!$meals_time) {
            ajaxReturn(['status' => -1, 'msg' => '用餐时间不能为空', 'data' => '']);
        }
        $remark = input('remark');
        // $description_second = input('description_second');
        // if (!$description_second) {
        //     ajaxReturn(['status' => -1, 'msg' => '订餐备注不能为空', 'data' => '']);
        // }
        $total_money = input('total_money');
        if (!$total_money) {
            ajaxReturn(['status' => -1, 'msg' => 'total_money参数不存在', 'data' => '']);
        }
        $discount = input('discount');
        if (!$discount) {
            ajaxReturn(['status' => -1, 'msg' => 'discount参数不存在', 'data' => '']);
        }
        //本视频是否是自己user_id
        $video_res = Db::name('video')->where(['id' => $video_id, 'user_id' => $user_id])->find();
        if ($video_res) {
            ajaxReturn(['status' => -1, 'msg' => '该视频是自己本人，无法下单！', 'data' => '']);
        }
        //获取类型
        $type = Db::name('video')->where(['id' => $video_id])->value('type');
        //添加数据
        $data = [
            'user_id' => $user_id,
            'video_id' => $video_id,
            'type' => $type,
            'username' => $username,
            'phone' => $phone,
            'meals_time' => $meals_time,
            'remark' => $remark,
            // 'description_second' => $description_second,
            'total_money' => $total_money,
            'discount' => $discount,
            'status' => 0,
            'add_time' => time()
        ];
        // dump($data);
        // die;
        $buy_insert_res = Db::name('buy')->insertGetId($data);

        if ($type == 4) {

            //下单人信息
            $buy_res = Db::name('buy')->where(['id' => $buy_insert_res])->find();

            //用户信息
            $user_res = Db::name('user')->where(['user_id' => $user_id])->find();

            $time = date('Y年m月d日', $buy_res['add_time']);

            //商家手机号码
            $store_mobile = Db::name('video')->alias('a')
                ->join('yx_user b', 'a.user_id = b.user_id')
                ->field('b.mobile')
                ->where(['a.id' => $buy_res['video_id']])
                ->find();
            //发送短信通知商家
            // 大中小企业直聘】下单成功，用户XXX于XX年XX月XX日预订了餐厅，请商家接单。流程:我的－我的订单－我收到的－接单
            //新款发送短信
            $content = '【大中小企业直聘】下单成功，用户' . $user_res['nickname'] . '于' . $time . '预订了餐厅，请商家接单。流程：我的－我的订单－我收到的－接单';
            $res = send_sms_chenxi($store_mobile['mobile'], $content);
            if ($res['returnstatus'] == 'Success') {
                $data = [
                    'user_id' => $user_id,
                    'desc' => $content,
                    'add_time' => time()
                ];
                Db::name('account_log')->insert($data);
            }
        }

        //选菜列表修改
        Db::name('user_menu')->where(['user_id' => $user_id, 'video_id' => $video_id, 'is_deleted' => 0])->update(['buy_id' => $buy_insert_res]);
        if ($buy_insert_res) {
            ajaxReturn(['status' => 1, 'msg' => '填写成功!', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '填写失败!', 'data' => '']);
        }
    }
    /**
     * 商家、买家交易审核列表
     * 2020.05.28
     * @return video_id 视频id
     * @return username 用户名
     * @return phone  手机号
     * @return address 地址
     * @return remark 备注
     * @return price  价格
     * @return status 状态
     * @param status status == 1  待付款 || status == 0  等待商家同意
     * @param order_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 0 待发货
     * @param pay_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 1 待收货
     * @param shipping_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 2 交易成功
     * @param status status == 2  交易失败 有数据返回 不同意备注
     */
    public function confirm_transaction()
    {
        $user_id = $this->get_user_id();  //当前用户商家
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $type = input('type'); // 1买家 2商家
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        $page = input('page', 1);
        $limit = input('limit', 10);
        if ($type == 1) { //买家
            //在交易中数据
            $buy_res = Db::name('buy')->alias("a")
                ->join('yx_order b', 'a.order_id = b.order_id', 'LEFT')
                ->join('yx_return_goods c', 'b.order_id = c.order_id', 'LEFT')
                ->field('a.id,a.video_id,a.user_id,a.order_id,a.type,a.username,a.phone,a.address,a.remark,a.price,a.status,a.add_time,a.description,b.order_status,b.pay_status,b.shipping_status,b.type as order_type,a.shipping_fee,a.discount,a.meals_time,a.total_money,a.room_num,a.credit_card,a.description_second,a.meal_status,c.status as return_status,a.room_time')
                ->where(['a.user_id' => $user_id])
                ->page($page, $limit)
                ->order('a.add_time desc')
                ->select();
            foreach ($buy_res as $key => $value) {
                if ($value['status'] == 0) {
                    $buy_res[$key]['status_cn'] = '等待商家同意';
                }
                if ($value['type'] == 3) {
                    $buy_res[$key]['type_cn'] = '特色产品';
                }
                if ($value['type'] == 4) {
                    $buy_res[$key]['type_cn'] = '餐厅';
                }
                if ($value['type'] == 5) {
                    $buy_res[$key]['type_cn'] = '酒店';
                }
                if ($value['type'] == 7) {
                    $buy_res[$key]['type_cn'] = '生活用品';
                }
                if ($value['type'] == 3 || $value['type'] == 7) {
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '待付款';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待发货';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '待收货';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }
                if ($value['type'] == 4) { //餐厅
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '待付款';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待用餐';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 2 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '等待退款';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }
                if ($value['type'] == 5) { //酒店
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '待付款';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待入住';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 2 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '等待退款';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }
                //是否退款成功
                if ($value['return_status'] == 2) {
                    $buy_res[$key]['status_cn'] = '退款成功';
                }
                $buy_res[$key]['add_time'] = date('Y-m-d', $value['add_time']);
            }
        } else { //商家
            $ids = [];
            //找出商家发布视频id
            $video_list = Db::name('video')->where(['user_id' => $user_id])->field('id')->select();

            foreach ($video_list as $k => $v) {
                $ids[] = $v['id'];
            }
            //dump($ids);
            //在交易中是否存在
            $buy_res = Db::name('buy')
                ->alias("a")
                ->join('yx_order b', 'a.order_id = b.order_id', 'LEFT')
                ->join('yx_return_goods c', 'b.order_id = c.order_id', 'LEFT')
                ->field('a.id,a.video_id,a.user_id,a.order_id,a.type,a.username,a.phone,a.address,a.remark,a.price,a.status,a.add_time,a.description,b.order_status,b.pay_status,b.shipping_status,b.type as order_type,a.shipping_fee,a.discount,a.meals_time,a.total_money,a.room_num,a.credit_card,a.description_second,a.meal_status,c.status as return_status,a.room_time')
                ->where(['a.video_id' => array('in', $ids)])
                ->page($page, $limit)
                ->order('a.add_time desc')
                ->select();
            foreach ($buy_res as $key => $value) {

                if ($value['status'] == 0) {
                    $buy_res[$key]['status_cn'] = '去同意';
                }
                if ($value['type'] == 3) {
                    $buy_res[$key]['type_cn'] = '特色产品';
                }
                if ($value['type'] == 4) {
                    $buy_res[$key]['type_cn'] = '餐厅';
                }
                if ($value['type'] == 5) {
                    $buy_res[$key]['type_cn'] = '酒店';
                }
                if ($value['type'] == 7) {
                    $buy_res[$key]['type_cn'] = '生活用品';
                }
                if ($value['type'] == 3 || $value['type'] == 7) {
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已同意';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待发货';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已发货';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['shipping_status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }

                if ($value['type'] == 4) { //餐厅
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已同意';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待客户确认用餐';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 2 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已退单';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }
                if ($value['type'] == 5) { //酒店
                    if ($value['status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已同意';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 0) {
                        $buy_res[$key]['status_cn'] = '待客户确认入住';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 1 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '交易成功';
                    }
                    if ($value['status'] == 1 && $value['order_status'] == 2 && $value['pay_status'] == 1 && $value['meal_status'] == 1) {
                        $buy_res[$key]['status_cn'] = '已退单';
                    }
                    if ($value['status'] == 2) {
                        $buy_res[$key]['status_cn'] = '交易失败';
                    }
                }
                //是否退款成功
                if ($value['return_status'] == 2) {
                    $buy_res[$key]['status_cn'] = '退款成功';
                }
                $buy_res[$key]['add_time'] = date('Y-m-d', $value['add_time']);
            }
        }
        //dump($buy_res);die;
        $return = [
            'confirm_list' => $buy_res
        ];
        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $return]);
    }
    /**
     * 查看详情
     *
     * @Author DSJ
     * @DateTime 2020-05-29 15:05:01
     * @param status status == 1  待付款 || status == 0  等待商家同意
     * @param order_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 0 待发货
     * @param pay_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 1 待收货
     * @param shipping_status status == 1 and order_status == 1 and  pay_status == 1 and shipping_status == 2 交易成功
     * @param status status == 2  交易失败 有数据返回 不同意备注
     */
    public function details()
    {
        $user_id = $this->get_user_id();  //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $id = input('id'); // 1买家 2商家
        if (!$id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        $type = input('type'); // 1咨询 2收到
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        //交易数据
        $buy_res = Db::name('buy')->alias('a')
            ->join('yx_order b', 'a.order_id = b.order_id', 'LEFT')
            ->field('a.id,a.video_id,a.user_id,a.order_id,a.type,a.username,a.phone,a.address,a.remark,a.price,a.status,a.add_time,a.description,b.order_status,b.pay_status,b.shipping_status,b.type as order_type,a.shipping_fee,a.discount,a.meals_time,a.total_money,a.room_num,a.credit_card,a.description_second,a.meal_status,a.room_time')
            ->where(['a.id' => $id])
            ->find();
        if ($type == 1) { //咨询
            $buy_res['return_goods_status'] = 0; //默认没有退款
            if ($buy_res['status'] == 0); {
                $buy_res['status_cn'] = '等待商家同意';
            }
            if ($buy_res['type'] == 3) {
                $buy_res['type_cn'] = '特色产品';
            }
            if ($buy_res['type'] == 4) {
                $buy_res['type_cn'] = '餐厅';
            }
            if ($buy_res['type'] == 5) {
                $buy_res['type_cn'] = '酒店';
            }
            if ($buy_res['type'] == 7) {
                $buy_res['type_cn'] = '生活用品';
            }
            if ($buy_res['type'] == 3 || $buy_res['type'] == 7) {
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '待付款';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 0) {
                    $buy_res['status_cn'] = '待发货';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 1) {
                    $buy_res['status_cn'] = '待收货';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 2) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易失败';
                }
                $buy_res['total_price'] = $buy_res['price'] + $buy_res['shipping_fee'];
            }

            if ($buy_res['type'] == 4) { //餐厅
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '待付款';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 0) {
                    $buy_res['status_cn'] = '确认用餐或退单';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 1) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易失败';
                }
            }
            if ($buy_res['type'] == 5) { //酒店
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '待付款';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 0) {
                    $buy_res['status_cn'] = '确认入住或退单';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 1) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易失败';
                }
            }
            $return_goods = Db::name("return_goods")->where(['user_id' => $buy_res['user_id'], 'order_id' => $buy_res['order_id']])->find();
            if ($return_goods) {
                if ($return_goods['status'] == 0) {
                    $buy_res['status_cn'] = '等待退款';
                } else {
                    $buy_res['status_cn'] = '退款成功';
                }
            }
            $buy_res['add_time'] = date('Y-m-d', $buy_res['add_time']);
        } else { //商家
            //担保金
            $deposit_money = Db::name("config")->where(['id' => 1])->value('deposit_money');
            $buy_res['return_goods_status'] = 0; //默认没有退款

            if ($buy_res['status'] == 0) {
                $buy_res['status_cn'] = '去同意';
            }
            if ($buy_res['type'] == 3) {
                $buy_res['type_cn'] = '特色产品';
            }
            if ($buy_res['type'] == 4) {
                $buy_res['type_cn'] = '餐厅';
            }
            if ($buy_res['type'] == 5) {
                $buy_res['type_cn'] = '酒店';
            }
            if ($buy_res['type'] == 7) {
                $buy_res['type_cn'] = '生活用品';
            }
            if ($buy_res['type'] == 3 || $buy_res['type'] == 7) {
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '等待买家支付';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 0) {
                    $buy_res['status_cn'] = '待发货';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 1) {
                    $buy_res['status_cn'] = '已发货';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['shipping_status'] == 2) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易不同意';
                }
                $buy_res['total_price'] = $buy_res['price'] + $buy_res['shipping_fee'];
            }

            if ($buy_res['type'] == 4) { //餐厅
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '等待买家支付';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 0) {
                    $buy_res['status_cn'] = '待客户确认用餐';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 1) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易失败';
                }
            }
            if ($buy_res['type'] == 5) { //酒店
                if ($buy_res['status'] == 1) {
                    $buy_res['status_cn'] = '等待买家支付';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 0) {
                    $buy_res['status_cn'] = '待客户确认入住';
                }
                if ($buy_res['status'] == 1 && $buy_res['order_status'] == 1 && $buy_res['pay_status'] == 1 && $buy_res['meal_status'] == 1) {
                    $buy_res['status_cn'] = '交易成功';
                    $buy_res['return_goods_status'] = 1;
                }
                if ($buy_res['status'] == 2) {
                    $buy_res['status_cn'] = '交易失败';
                }
            }

            $return_goods = Db::name("return_goods")->where(['user_id' => $buy_res['user_id'], 'order_id' => $buy_res['order_id']])->find();
            if ($return_goods) {
                if ($return_goods['status'] == 0) {
                    $buy_res['status_cn'] = '收到退单通知';
                } else {
                    $buy_res['status_cn'] = '退款成功';
                }
            }
            $buy_res['add_time'] = date('Y-m-d', $buy_res['add_time']);
        }
        $return = [
            'details' => $buy_res,
            'deposit_money' => $deposit_money
        ];
        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $return]);
    }
    /**
     * 确认发货
     *
     * @Author DSJ
     * @DateTime 2020-06-02 14:35:21
     * @param order_id
     */
    public function agree_express()
    {
        $user_id = $this->get_user_id();  //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $order_id = input('order_id');
        if (!$order_id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        //订单是否存在
        $order = Db::name('order')->where(['order_id' => $order_id])->find();
        if (!$order) {
            ajaxReturn(['status' => -1, 'msg' => '订单不存在', 'data' => '']);
        }
        //修改
        Db::name('order')->where(['order_id' => $order_id])->update(array('shipping_status' => 1));

        ajaxReturn(['status' => 1, 'msg' => '操作成功!', 'data' => '']);
    }
    /**
     * 填写单号
     * 2020.05.28
     */
    public function add_express()
    {
        $user_id = $this->get_user_id();  //当前用户
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $order_id = input('order_id');
        if (!$order_id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        $shipping_name = input('shipping_name'); // 快递公司名称
        if (!$shipping_name) {
            ajaxReturn(['status' => -1, 'msg' => 'shipping_name参数不存在', 'data' => '']);
        }
        $courier_number = input('courier_number'); // 快递单号
        if (!$courier_number) {
            ajaxReturn(['status' => -1, 'msg' => 'courier_number参数不存在', 'data' => '']);
        }
        //订单是否存在
        $order = Db::name('order')->where(['order_id' => $order_id])->find();
        if (!$order) {
            ajaxReturn(['status' => -1, 'msg' => '订单不存在', 'data' => '']);
        }
        $data = [
            'shipping_name' => $shipping_name,
            'courier_number' => $courier_number,
            'shipping_status' => 1
        ];
        //修改
        Db::name('order')->where(['order_id' => $order_id])->update($data);

        ajaxReturn(['status' => 1, 'msg' => '发货成功!', 'data' => '']);
    }
    /**
     * 商家去同意 
     * 2020.05.28
     */
    public function agree()
    {
        $id = input('id'); // 1买家 2商家
        if (!$id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        //修改状态
        Db::name('buy')->where(['id' => $id])->update(array('status' => 1));

        ajaxReturn(['status' => 1, 'msg' => '同意成功!', 'data' => '']);
    }
    /**
     * 商家不同意
     * 2020.05.28
     */
    public function disagree()
    {
        $id = input('id'); // 1买家 2商家
        if (!$id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        $description = input('description'); // 1买家 2商家
        if (!$description) {
            ajaxReturn(['status' => -1, 'msg' => 'description参数不存在', 'data' => '']);
        }
        //修改状态
        Db::name('buy')->where(['id' => $id])->update(array('status' => 2, 'description' => $description));

        ajaxReturn(['status' => 1, 'msg' => '操作成功!', 'data' => '']);
    }
    /**
     * 编辑数据
     *
     * @Author DSJ
     * @DateTime 2020-06-01 18:36:23
     * @param id 详情id
     */
    public function edit_data()
    {
        $id = input('id'); // 列表id
        if (!$id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        //获取渲染数据
        $buy_data = Db::name('buy')->where(['id' => $id])->find();

        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $buy_data]);
    }
    /**
     * 编辑
     *
     * @Author DSJ
     * @DateTime 2020-06-01 18:52:10
     * @param undefined
     * @return void
     */
    public function edit()
    {
        $id = input('id'); // 列表id
        if (!$id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        $username = input('username');
        if (!$username) {
            ajaxReturn(['status' => -1, 'msg' => 'username参数不存在', 'data' => '']);
        }
        $phone = input('phone');
        if (!$phone) {
            ajaxReturn(['status' => -1, 'msg' => 'phone参数不存在', 'data' => '']);
        }
        $address = input('address');
        if (!$address) {
            ajaxReturn(['status' => -1, 'msg' => 'address参数不存在', 'data' => '']);
        }
        $remark = input('remark');
        if (!$remark) {
            ajaxReturn(['status' => -1, 'msg' => 'remark参数不存在', 'data' => '']);
        }
        $price = input('price');
        if (!$price) {
            ajaxReturn(['status' => -1, 'msg' => 'price参数不存在', 'data' => '']);
        }
        $shipping_fee = input('shipping_fee');
        if (!$shipping_fee) {
            ajaxReturn(['status' => -1, 'msg' => 'shipping_fee参数不存在', 'data' => '']);
        }
        //修改数据
        $data = [
            'username' => $username,
            'phone' => $phone,
            'address' => $address,
            'remark' => $remark,
            'price' => $price,
            'shipping_fee' => $shipping_fee
        ];
        // dump($data);
        // die;
        $buy_update_res = Db::name('buy')->where(['id' => $id])->update($data);
        if ($buy_update_res) {
            ajaxReturn(['status' => 1, 'msg' => '编辑成功!', 'data' => '']);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '编辑失败!', 'data' => '']);
        }
    }
    /**
     * 买家确认收货
     *
     * @Author DSJ
     * @DateTime 2020-05-28 18:52:33
     * @param order_id 订单id
     * @return msg 收货成功
     */
    public function confirm_receipt()
    {

        $order_id = input('order_id'); // 1买家 2商家
        if (!$order_id) {
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        //订单是否存在
        $order = Db::name('order')->where(['order_id' => $order_id])->find();
        if (!$order) {
            ajaxReturn(['status' => -1, 'msg' => '订单不存在', 'data' => '']);
        }
        //修改状态
        Db::name('order')->where(['order_id' => $order_id])->update(array('shipping_status' => 2));

        Db::name('buy')->where(['order_id' => $order_id])->update(array('meal_status' => 1));
        ajaxReturn(['status' => 1, 'msg' => '操作成功!', 'data' => '']);
    }
}
