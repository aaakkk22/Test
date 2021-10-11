<?php

namespace app\api\controller;

use think\Db;
use app\common\logic\OssLogic;
use app\common\logic\IndexLogic;

class Index extends Base
{
    /**
     * 开关
     */
    public function on_status()
    {
        $on_status = Db::name('config')->where(['id' => 1])->value('is_show');
        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => ['on_status' => $on_status]]);
    }


    /**
     * 首页视频展示
     * 2020.04.15
     */
    public function index()
    {
        $type = input('type');
        if ($type) {
            $where['type'] = $type;
        }
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $lat = input('lat'); //weidu
        if (!$lat) {
            ajaxReturn(['status' => -1, 'msg' => 'lat参数不存在', 'data' => '']);
        }
        $lng = input('lng'); //jingdu
        if (!$lng) {
            ajaxReturn(['status' => -1, 'msg' => 'lng参数不存在', 'data' => '']);
        }

        //分页
        $page = input('page', 1);
        $limit = input('limit', 15);
        //限制条数
        $limit_num = Db::name('config')->where(['id' => 1])->value('limit_num'); //找工作视频个数
        //限制条数
        $company_limit_num = Db::name('config')->where(['id' => 1])->value('company_limit_num'); //招工视频个数
        //限制条数
        $area_limit_num = Db::name('config')->where(['id' => 1])->value('area_limit_num'); //地方视频个数
        //限制条数
        $restaurant_limit_num = Db::name('config')->where(['id' => 1])->value('restaurant_limit_num'); //餐厅视频个数
        //限制条数
        $hotel_limit_num = Db::name('config')->where(['id' => 1])->value('hotel_limit_num'); //酒店视频个数
        //限制条数
        $lawyer_limit_num = Db::name('config')->where(['id' => 1])->value('lawyer_limit_num'); //律师视频个数
        //限制条数
        $goods_limit_num = Db::name('config')->where(['id' => 1])->value('goods_limit_num'); //商品视频个数
        //限制条数
        $select_room_limit_num = Db::name('config')->where(['id' => 1])->value('select_room_limit_num'); //租房个数
        //限制条数
        $buy_room_limit_num = Db::name('config')->where(['id' => 1])->value('buy_room_limit_num'); //买房个数
        //开启逻辑
        $logic = new IndexLogic;
        $result = $logic->index($type, $user_id, $page, $limit, $limit_num, $company_limit_num, $area_limit_num, $where, $restaurant_limit_num, $hotel_limit_num, $lawyer_limit_num, $goods_limit_num, $select_room_limit_num, $buy_room_limit_num,$lat,$lng);

        ajaxReturn($result);
    }
    /**
     * 首页点赞
     * 2020.04.21
     */
    public function like_num()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $like_num = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $video_id])->count();

        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $like_num]);
    }
    /**
     * 关键词渲染
     * 2020.04.15
     */
    public function keyword_list()
    {
        $keyword = Db::name('keyword')->select();

        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $keyword]);
    }
    /**
     * 测试
     *
     * @Author DSJ
     * @DateTime 2020-07-13 15:42:55
     * @param undefined
     * @return void
     */
    public function ceshi()
    {
        // 大中小企业直聘】下单成功，用户XXX于XX年XX月XX日预订了餐厅，请商家接单。流程:我的－我的订单－我收到的－接单
        //新款发送短信
        $content = '【大中小企业直聘】下单成功，用户XXX于XX年XX月XX日预订了餐厅，请商家接单。流程：我的－我的订单－我收到的－接单';
        $r = send_sms_chenxi(13652001271, $content);
        ajaxReturn(['status' => 1, 'msg' => '发送成功!', 'data' => $r]);
    }
    /**
     * 搜索功能
     * 2020.04.15
     */
    public function search()
    {
        $keyword = input('keyword');

        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }

        if (!$keyword) {
            ajaxReturn(['status' => -1, 'msg' => 'keyword参数不存在', 'data' => '']);
        } else {
            $where['content'] = array('like', '%' . $keyword . '%');
            //公用条件
            $where['status'] = 1;
            $where['type'] = $type;
        }
        $page = input('page', 1);
        $limit = input('limit', 10);

        //视频列表
        $video = Db::name('video')
            ->order('add_time desc')
            ->page($page, $limit)
            ->where($where)
            ->select();
        foreach ($video as $k => $v) {
            $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            // if ($v['type'] == 1) {
            //     $video[$k]['type_name'] = '招人';
            // }
            // if ($v['type'] == 2) {
            //     $video[$k]['type_name'] = '找工作';
            // }
        }
        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $video]);
    }
    /**
     * 城市搜索
     *
     * @Author DSJ
     * @DateTime 2020-07-27 17:15:09
     * @param undefined
     * @return void
     */
    public function search_city()
    {
        $keyword = input('keyword');

        if (!$keyword) {
            ajaxReturn(['status' => -1, 'msg' => 'keyword参数不存在', 'data' => '']);
        } else {
            $where['name'] = array('like', '%' . $keyword . '%');
        }
        //城市列表
        $region = Db::name('region')
            ->where($where)
            ->select();

        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => $region]);
    }
    /**
     * 评论列表
     * 2020.04.18
     */
    public function comment_list()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        //分页
        $page = input('page', 1);
        $limit = input('limit', 10);

        //视频列表
        $comment = Db::name('comment')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id', 'LEFT')
            ->order('add_time desc')
            ->field('a.id,a.video_id ,a.content,a.add_time,a.user_id,a.like_num,b.nickname,b.avatar')
            ->where(['a.is_show' => 1, 'video_id' => $video_id])
            ->page($page, $limit)
            ->select();
        foreach ($comment as $k => $v) {
            $comment[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            //是否点赞
            $comment_like = Db::name('comment_like')->where(['user_id' => $user_id, 'comment_id' => $v['id']])->find();
            if ($comment_like) {
                $comment[$k]['comment_like_status'] = true;
            } else {
                $comment[$k]['comment_like_status'] = false;
            }
        }
        //统计评论数
        $comment_num =  Db::name('comment')->where(['is_show' => 1, 'video_id' => $video_id])->count();
        ajaxReturn(['status' => 1, 'msg' => '获取成功!', 'data' => array('comment' => $comment, 'comment_num' => $comment_num)]);
    }
    /**
     * 点击头像后获取别人信息
     * 2020.04.18
     */
    public function my_center()
    {
        $user_id = input('user_id');
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'user_id参数不存在', 'data' => '']);
        }
        $user_id2 = $this->get_user_id(); //当前用户
        if (!$user_id2) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }

        $page = input('page', 1);
        $limit = input('limit', 10);
        //个人中心
        $my_res = Db::name('user')->where(['user_id' => $user_id])->field('user_id,nickname,avatar,is_black')->find();
        if ($my_res['is_black'] == 1) {
            $my_res['black_name'] = '禁用';
        } else {
            $my_res['black_name'] = '启用';
        }
        //我的作品
        $video = Db::name('video')
            ->where(['user_id' => $user_id])
            ->order('add_time desc')
            ->page($page, $limit)
            ->select();
        foreach ($video as $k => $v) {
            //点赞数
            $like_num  = Db::name('user_like')->where(['video_id' => $v['id']])->count();
        }
        //点赞数
        $like_num = $like_num;

        //关注次数
        $follow_total = Db::name('user_follow')->where(['user_id' => $user_id])->count();

        //是否关注
        $user_follow = Db::name('user_follow')->where(['user_id' => $user_id2, 'follow_user_id' => $user_id])->find();
        if ($user_follow) {
            $user_follow = true;
        } else {
            $user_follow = false;
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
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => array('my' => $my_res, 'like_num' => $like_num, 'follow_total' => $follow_total, 'user_follow_status' => $user_follow, 'vip_status' => $vip_status, 'vip_cn' => $vip_cn, 'come_time' => $come_time, 'vip_status_second' => $vip_status_second, 'vip_cn_second' => $vip_cn_second, 'come_time_second' => $come_time_second)]);
    }
    /**
     * 我的作品、我的喜欢
     * 2020.04.18
     */
    public function my_project()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        $page = input('page', 1);
        $limit = input('limit', 10);
        if ($type == 1) {
            //我的作品
            $video = Db::name('video')
                ->where(['user_id' => $user_id, 'status' => 1])
                ->order('add_time desc')
                ->page($page, $limit)
                ->select();
            foreach ($video as $k => $v) {

                $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        if ($type == 2) {
            //点赞视频
            $video = Db::name('user_like')->alias('a')
                ->join('yx_video b', 'a.video_id = b.id', 'LEFT')
                ->where(['a.user_id' => $user_id, 'b.status' => 1])
                ->field('b.id,b.add_time,b.cover_image,b.video_url,b.content,b.type')
                ->order('a.add_time desc')
                ->page($page, $limit)
                ->select();
            foreach ($video as $k => $v) {

                $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        //统计数量
        $video_count1 = Db::name('video')->where(['user_id' => $user_id, 'status' => 1])->count();

        //统计数量
        $video_count2 = Db::name('user_like')
            ->alias('a')
            ->join('yx_video b', 'a.video_id = b.id', 'LEFT')
            ->where(['a.user_id' => $user_id, 'b.status' => 1])
            ->count();

        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => array('video' => $video, 'video_like' => $video_count2, 'video_project' => $video_count1)]);
    }
    /**
     * 点击别人进去我的作品、我的喜欢
     * 2020.04.18
     */
    public function user_project()
    {
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        $user_id = Db::name('video')->where(['id' => $video_id])->value('user_id');
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type参数不存在', 'data' => '']);
        }
        $page = input('page', 1);
        $limit = input('limit', 10);
        if ($type == 1) {
            //我的作品
            $video = Db::name('video')
                ->where(['user_id' => $user_id, 'status' => 1])
                ->order('add_time desc')
                ->page($page, $limit)
                ->select();
            foreach ($video as $k => $v) {

                $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        if ($type == 2) {
            //点赞视频
            $video = Db::name('user_like')->alias('a')
                ->join('yx_video b', 'a.video_id = b.id', 'LEFT')
                ->where(['a.user_id' => $user_id, 'b.status' => 1])
                ->field('b.id,b.add_time,b.cover_image,b.video_url,b.content,b.type')
                ->order('a.add_time desc')
                ->page($page, $limit)
                ->select();
            foreach ($video as $k => $v) {

                $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        //统计数量
        $video_count1 = Db::name('video')->where(['user_id' => $user_id, 'status' => 1])->count();

        //统计数量
        $video_count2 = Db::name('user_like')
            ->alias('a')
            ->join('yx_video b', 'a.video_id = b.id', 'LEFT')
            ->where(['a.user_id' => $user_id, 'b.status' => 1])
            ->count();

        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => array('video' => $video, 'video_like' => $video_count2, 'video_project' => $video_count1)]);
    }
    /**
     * 作品视频详情
     * 2020.04.20
     */
    public function video_details()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id参数不存在', 'data' => '']);
        }
        //视频详情
        $video_res = Db::name('video')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id', 'LEFT')
            ->field('a.id,a.user_id ,a.title,a.city,a.type,a.cover_image,a.video_url,a.content,a.add_time,b.nickname,b.avatar')
            ->where(['id' => $video_id])
            ->find();

        $video_res['add_time'] = date('Y-m-d H:i:s', $video_res['add_time']);
        if ($video_res['type'] == 1) {
            $video_res['type_name'] = '招人';
        }
        if ($video_res['type'] == 2) {
            $video_res['type_name'] = '找工作';
        }
        if ($video_res['type'] == 3) {
            $video_res['type_name'] = '个人特色产品视频';
        }
        if ($video_res['type'] == 4) {
            $video_res['type_name'] = '餐厅';
        }
        if ($video_res['type'] == 5) {
            $video_res['type_name'] = '酒店住宿';
        }
        if ($video_res['type'] == 6) {
            $video_res['type_name'] = '律师';
        }
        if ($video_res['type'] == 7) {
            $video_res['type_name'] = '生活用品（装饰）';
        }

        //是否关注
        $user_follow = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $video_res['user_id']])->find();
        if ($user_follow) {
            $video_res['user_follow_status'] = true;
        } else {
            $video_res['user_follow_status'] = false;
        }
        //是否点赞
        $user_like = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $video_id])->find();
        if ($user_like) {
            $video_res['user_like_status'] = true;
        } else {
            $video_res['user_like_status'] = false;
        }
        //个人标志
        $video_res['user_video_status'] = $video_res['user_id'] == $user_id ? true : false;

        //统计评论数
        $video_res['comment_num'] = Db::name('comment')->where(['video_id' => $video_res['id'], 'is_show' => 1])->count();
        //统计点赞
        $video_res['like_num'] = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $video_id])->count();
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $video_res]);
    }
    /**
     * 关注列表
     * 2020.04.21
     */
    public function follow_list()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $page = input('page', 0);
        $limit = input('limit', 10);
        $user_follow = Db::name('user_follow')->alias('a')
            ->join('yx_user b', 'a.follow_user_id = b.user_id', 'LEFT')
            ->field('a.id ,a.user_id,a.follow_user_id,a.add_time,b.nickname,b.avatar')
            ->order('a.id desc')
            ->where(['a.user_id' => $user_id])
            ->page($page, $limit)
            ->select();
        foreach ($user_follow as $k => $v) {
            $user_follow[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            //是否关注
            $user_follow_res = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $v['follow_user_id']])->find();
            if ($user_follow_res) {
                $user_follow[$k]['user_follow_status'] = true;
            } else {
                $user_follow[$k]['user_follow_status'] = false;
            }
        }
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $user_follow]);
    }
    /**
     * 上传视频或照片
     * 2020.04.21
     */
    public function upload()
    {
        $file = request()->file('file');  //获取上传文件信息
        if ($file) {

            $ext = strrchr($_FILES['file']['name'], '.');

            $name = md5(time()) . round(10000, 99999) . $ext;

            $fileName = 'video_xcx/' . date("Ymd") . '/' . $name;

            $ossClient = new OssLogic();
            $url = $ossClient->uploadFile($_FILES['file']['tmp_name'], $fileName);
            if ($url) {
                ajaxReturn(array('status' => 1, 'msg' => '成功', 'data' => $url));
            } else {
                ajaxReturn(array('status' => -1, 'msg' => '失败', 'data' => ''));
            }
        } else {
            ajaxReturn(array('status' => -1, 'msg' => 'file文件缺失', 'data' => ''));
        }
    }
    /**
     * 定时器 每天刷新不是会员更新最新视频
     * 2020.04.23
     */
    public function update_video()
    {
        //符合到期的会员删除
        $member_delete = Db::name('company')->field('id ,user_id,company_vip_type,company_expire_time')->count();
        if ($member_delete > 2) {
            $company_res =  Db::name('company')->field('id ,user_id,company_vip_type,company_expire_time')->select();
            foreach ($company_res as $k => $v) {
                if ($v['company_expire_time'] < time()) {
                    Db::name('company')->where(['id' => $v['id']])->delete();
                    Db::name('order')->where(['user_id' => $v['user_id'], 'vip_id' => $v['company_vip_type']])->delete();
                }
            }
        } else {
            $company_res =  Db::name('company')->field('id ,user_id,company_vip_type,company_expire_time')->find();
            if ($company_res['company_expire_time'] < time()) {
                Db::name('company')->where(['id' => $company_res['id']])->delete();
                Db::name('order')->where(['user_id' => $company_res['user_id'], 'vip_id' => $company_res['company_vip_type']])->delete();
            }
        }

        $seeker_delete = Db::name('job_seeker')->field('id ,user_id,seeker_vip_type, seeker_vip_expire_time')->count();
        if ($seeker_delete > 2) {
            $seeker_res =  Db::name('job_seeker')->field('id ,user_id,seeker_vip_type, seeker_vip_expire_time')->select();
            foreach ($seeker_res as $k => $v) {
                if ($v['seeker_vip_expire_time'] < time()) {
                    Db::name('job_seeker')->where(['id' => $v['id']])->delete();
                    Db::name('order')->where(['user_id' => $v['user_id'], 'vip_id' => $v['seeker_vip_type']])->delete();
                }
            }
        } else {
            $seeker_res =  Db::name('job_seeker')->field('id ,user_id,seeker_vip_type, seeker_vip_expire_time')->find();
            if ($seeker_res['seeker_vip_expire_time'] < time()) {
                Db::name('job_seeker')->where(['id' => $seeker_res['id']])->delete();
                Db::name('order')->where(['user_id' => $seeker_res['user_id'], 'vip_id' => $seeker_res['company_vip_type']])->delete();
            }
        }

        $delete = Db::name('video_visit')->where('id', '>', 1)->delete();
        if ($delete) {
            ajaxReturn(array('status' => 1, 'msg' => '成功', 'data' => ''));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '失败', 'data' => ''));
        }
    }
    /**
     * 搜索地址列表
     * 2020.04.24
     */
    public function city_video()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }
        $city = input('city');
        if (!$city) {
            ajaxReturn(['status' => -1, 'msg' => 'city参数不存在', 'data' => '']);
        }
        $page = input('page', 0);
        $limit = input('limit', 10);
        $city_video = Db::name('video')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id')
            ->field('a.id ,a.user_id,a.title,a.city,a.cover_image,a.video_url,a.content,a.add_time,b.nickname,b.avatar')
            ->order('a.id desc')
            ->where(['a.city' => array('like', '%' . $city . '%'), 'a.status' => 1])
            ->page($page, $limit)
            ->select();
        foreach ($city_video as $k => $v) {
            $city_video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $city_video]);
    }
    /**
     * 首页是否开启
     * 2020.05.12
     */
    public function index_status()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }

        $status = Db::name('user')->where(['user_id' => $user_id])->value('status');
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => array('status' => $status)]);
    }

    /**
     * 取汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     * @user qichao
     * @date 2017-04-13
     */
    public function _getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{
            0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{
            0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{
            0}) * 256 + ord($s{
            1}) - 65536;

        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776 || $asc == -9743) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711 || $asc == -9767) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641 || $asc == -7182 || $asc == -6928) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631 || $asc == -6745) return 'P';
        if ($asc >= -14630 && $asc <= -14150 || $asc == -7703) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';

        return null;
    }
    public function getRegionlist()
    {
        $regionData = $this->_regionNamesArray();
        //dump($regionData);
        $settlesRes = array();
        foreach ($regionData as $key => $sett) {
            $name = $sett['name'];
            $nameFirstChar = $this->_getFirstCharter($name); //取出第一个汉字的首字母

            $settlesRes[$nameFirstChar][] = $sett; //以这个首字母作为key
        }
        ksort($settlesRes);

        //重组
        $shunxu = [
            ['name' => 'A'],
            ['name' => 'B'],
            ['name' => 'C'],
            ['name' => 'D'],
            ['name' => 'E'],
            ['name' => 'F'],
            ['name' => 'G'],
            ['name' => 'H'],
            ['name' => 'I'],
            ['name' => 'J'],
            ['name' => 'K'],
            ['name' => 'L'],
            ['name' => 'M'],
            ['name' => 'N'],
            ['name' => 'O'],
            ['name' => 'P'],
            ['name' => 'Q'],
            ['name' => 'R'],
            ['name' => 'S'],
            ['name' => 'T'],
            ['name' => 'U'],
            ['name' => 'V'],
            ['name' => 'W'],
            ['name' => 'X'],
            ['name' => 'Y'],
            ['name' => 'Z']
        ];
        foreach ($shunxu as $k => $v) {
            $shunxu[$k]['res'] = $settlesRes[$v['name']];
        }

        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $shunxu]);
    }

    public function _regionNamesArray()
    {
        //获取id和城市name
        $result = Db::name('region')->field('id ,name')->where(['level' => 2])->select();
        return $result;
    }
}
