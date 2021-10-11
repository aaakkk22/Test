<?php

namespace app\common\logic;

use app\common\logic\SmsLogic;

use think\Db;

/**
 * 用户逻辑
 */
class MyLogic
{
    /**
     * 添加视频类型
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:07:27
     * @param type
     * @param [type] $type
     * @param [type] $user_id
     * @param [type] $city
     * @param [type] $cover_image
     * @param [type] $video_url
     * @param [type] $content
     * @return void
     */
    public function video($type, $user_id, $city, $cover_image, $video_url, $content, $lat, $lng)
    {
        //一个月时间
        $thismonth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $thismonth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        $where_month = array(array('gt', $thismonth_start), array('lt', $thismonth_end));

        //一年时间
        $begin_year = strtotime(date("Y", time()) . "-1" . "-1"); //本年开始
        $end_year = strtotime(date("Y", time()) . "-12" . "-31"); //本年结束
        $where_year = array(array('gt', $begin_year), array('lt', $end_year));

        //类型
        switch ($type) {
            case 1:
                $result = $this->send_video($type, $user_id, $city, $cover_image, $video_url, $content, $lat, $lng);
                break;
            case 2:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 3:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 4:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 5:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 6:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 7:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_month);
                break;
            case 8:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_year);
                break;
            case 9:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_year);
                break;
            case 10:
                $result = $this->send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where_year);
                break;
            default:
                $result = ['status' => -1, 'msg' => '类型传入错误！', 'data' => ''];
        }

        return $result;
    }
    /**
     * 操作不同身份不同发布视频
     *
     * @Author DSJ
     * @DateTime 2020-08-13 17:28:14
     * @param undefined
     * @param [type] $user_id
     * @param [type] $nickname
     * @param [type] $avatar
     * @return void
     */
    public function send_video_type($user_id, $type, $city, $cover_image, $video_url, $content, $lat, $lng, $where)
    {
        $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
        if ($company_vip) {
            $result = $this->send_video($type, $user_id, $city, $cover_image, $video_url, $content, $lat, $lng);
        } else {
            $video_res = Db::name('video')->where(['type' => $type, 'user_id' => $user_id])->where(array('add_time' => $where))->field('id , type')->count();
            if ($video_res >= 1) {
                return ['status' => -1, 'msg' => '本月免费发布次数已用完！', 'data' => ''];
            } else {
                $result = $this->send_video($type, $user_id, $city, $cover_image, $video_url, $content, $lat, $lng);
            }
        }
        return $result;
    }
    /**
     * 编辑操作
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:31:08
     * @param nickname
     * @param [type] $nickname
     * @return void
     */
    public function my_edit($user_id, $nickname, $avatar)
    {
        $user_update = [
            'nickname' => $nickname,
            'avatar'   => $avatar
        ];
        $user_update_res = Db::name('user')->where(['user_id' => $user_id])->update($user_update);
        if ($user_update_res) {
            return ['status' => 1, 'msg' => '编辑成功', 'data' => ''];
        } else {
            return ['status' => -1, 'msg' => '编辑失败', 'data' => ''];
        }
    }
    /**
     * 去关注
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [type] $follow_user_id
     * @return void
     */
    public function user_follow($user_id, $follow_user_id)
    {
        $count = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $follow_user_id])->count();
        if ($count > 0) {

            Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $follow_user_id])->delete();

            return ['status' => 1, 'msg' => '取消关注'];
        } else {
            $follow = [
                'user_id'        => $user_id,
                'follow_user_id' => $follow_user_id,
                'add_time'       => time()
            ];
            //添加关注列表
            Db::name('user_follow')->insert($follow);

            return ['status' => 1, 'msg' => '关注成功'];
        }
    }
    /**
     * 去点赞
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [video_id] $video_id
     * @return void
     */
    public function user_like($user_id, $video_id)
    {
        $count = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $video_id])->count();
        if ($count > 0) {

            Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $video_id])->delete();

            return ['status' => 1, 'msg' => '取消点赞'];
        } else {
            $follow = [
                'user_id'        => $user_id,
                'video_id'       => $video_id,
                'add_time'       => time()
            ];
            //添加关注列表
            Db::name('user_like')->insert($follow);

            return ['status' => 1, 'msg' => '点赞成功'];
        }
    }
    /**
     * 去评价
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [user_id] $user_id
     * @param [video_id] $video_id
     * @param [content] $content
     */
    public function add_comment($user_id, $video_id, $content)
    {
        //数据
        $comment_data = [
            'video_id'  => $video_id,
            'user_id'   => $user_id,
            'content'   => $content,
            'add_time'  => time(),
            'video_id'  => $video_id,
            'is_show'   => 1,
        ];
        $comment_res = Db::name('comment')->insert($comment_data);
        if ($comment_res) {
            return ['status' => 1, 'msg' => '评论成功', 'data' => ''];
        } else {
            return ['status' => -1, 'msg' => '评论失败', 'data' => ''];
        }
    }
    /**
     * 评论去点赞
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [user_id] $user_id
     * @param [comment_id] $comment_id
     */
    public function comment_like($user_id, $comment_id)
    {
        $count = Db::name('comment_like')->where(['user_id' => $user_id, 'comment_id' => $comment_id])->count();
        if ($count > 0) {

            Db::name('comment_like')->where(['user_id' => $user_id, 'comment_id' => $comment_id])->delete();
            Db::name('comment')->where(['id' => $comment_id])->setDec('like_num', 1);
            return ['status' => 1, 'msg' => '取消点赞'];
        } else {
            $comment = [
                'user_id'        => $user_id,
                'comment_id'     => $comment_id,
                'add_time'       => time()
            ];
            //添加关注列表
            Db::name('comment_like')->insert($comment);
            Db::name('comment')->where(['id' => $comment_id])->setInc('like_num', 1);
            return ['status' => 1, 'msg' => '点赞成功'];
        }
    }
    /**
     * 发送验证码
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [user_id] $user_id
     * @param [comment_id] $comment_id
     */
    public function send_code($phone)
    {
        $logic = new SmsLogic();
        //判断间隔是否超过60s
        $endtime = end(Db::name('sms_log')->where(['phone' => $phone, 'status' => 0])->field('add_time')->select());
        $endtime = intval(time()) - intval($endtime['add_time']);
        if ($endtime < 60) {
            ajaxReturn(['status' => -1, 'msg' => '发送失败,距离上次发送不足60s', 'retime' => 60 - $endtime]);
        }

        $signNam = '超级门店平台';
        $templateCode = 'SMS_134525030';
        $code = rand(1000, 9999);

        $res = $logic->sendSms($phone, $signNam, $templateCode, $code);

        if ($res->Message == 'OK') {
            //存表
            //先让之前的失效
            $temp['status'] = 1;
            Db::name('sms_log')->where(['status' => 0, 'phone' => $phone])->update($temp);

            $data['phone'] = $phone;
            $data['add_time'] = time();
            $data['status'] = 0;
            $data['code'] = $code;
            $data['appid'] = '';
            Db::name('sms_log')->insert($data);

            return ['status' => 1, 'msg' => '发送成功'];
        } else {
            return ['status' => -1, 'msg' => '发送失败' . $res->Message];
        }
    }
    /**
     * 添加视频 -无限制
     *
     * @Author DSJ
     * @DateTime 2020-06-08 16:49:06
     * @param undefined
     * @param [type] $type
     * @param [type] $user_id
     * @param [type] $city
     * @param [type] $cover_image
     * @param [type] $video_url
     * @param [type] $content
     * @return 'status' => 1, 'msg' => '发布成功', 'data' => ''
     */
    public function send_video($type, $user_id, $city, $cover_image, $video_url, $content, $lat, $lng)
    {
        $video_data = [
            'type'        => $type,
            'user_id'     => $user_id,
            'city'        => $city,
            'cover_image' => $cover_image,
            'video_url'   => $video_url,
            'content'     => $content,
            'lat'         => $lat,
            'lng'         => $lng,
            'add_time'    => time(),
            'status'      => 0
        ];
        $video_insert = Db::name('video')->insert($video_data);
        if ($video_insert) {
            return ['status' => 1, 'msg' => '发布成功', 'data' => ''];
        } else {
            return ['status' => -1, 'msg' => '发布失败', 'data' => ''];
        }
    }
}
