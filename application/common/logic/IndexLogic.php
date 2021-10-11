<?php

namespace app\common\logic;

use app\common\logic\SmsLogic;

use think\Db;

/**
 * 用户逻辑
 */
class IndexLogic
{
    /**
     * 首页视频
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [user_id] $user_id
     * @param [comment_id] $comment_id
     */
    public function index($type, $user_id, $page, $limit, $limit_num, $company_limit_num, $area_limit_num, $where, $restaurant_limit_num, $hotel_limit_num, $lawyer_limit_num, $goods_limit_num, $select_room_limit_num, $buy_room_limit_num, $lat, $lng)
    {
        //判断是否充值VIP等级
        if ($type == 1) { //企业方
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                //视频列表
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    //视频列表
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->limit($company_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 1])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        //会员视频列表
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 1;
                        //非会员视频列表
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }

        if ($type == 2) { //求职者
            //是否充值发布VIP
            $company_vip_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($company_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status_second = true;

                $seeker_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($seeker_vip_list_res) {
                    $vip_status = true; //发布会员
                } else {
                    $vip_status = false; //发布会员
                }
            } else {
                $job_seeker = Db::name('job_seeker')->where(['user_id' => $user_id])->find();
                if ($job_seeker) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status = true;
                    $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status_second = true; //发布会员
                    } else {
                        $vip_status_second = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;
                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }
                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 2])->find();
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 2;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }

        if ($type == 3) { //地方
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($area_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 3])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 3;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //餐厅
        if ($type == 4) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($restaurant_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }
                    //dump($ids);die;
                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 4])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 4;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //酒店住宿
        if ($type == 5) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($hotel_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 5])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 5;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //律师
        if ($type == 6) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($lawyer_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 6])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 6;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //生活用品（装饰）
        if ($type == 7) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($goods_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => 7])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $type = 7;
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //租房
        if ($type == 8) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($select_room_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => $type])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //买房
        if ($type == 9) {
            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit($buy_room_limit_num)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => $type])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        //跑腿
        if ($type == 10) {

            //是否充值发布VIP
            $seeker_vip_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
            if ($seeker_vip_res) {
                $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                //是否vip
                $vip_status = true; //观看会员
                $company_vip_list_res = Db::name('company')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                if ($company_vip_list_res) {
                    $vip_status_second = true; //发布会员
                } else {
                    $vip_status_second = false; //发布会员
                }
            } else {
                //是否充值企业VIP
                $company_vip = Db::name('company')->where(['user_id' => $user_id])->find();
                if ($company_vip) {
                    $video = $this->member_data($user_id, $where, $page, $limit, $lat, $lng);
                    //是否vip
                    $vip_status_second = true;
                    $company_vip_list_res = Db::name('job_seeker')->where(['user_id' => $user_id])->find(); //成为发布vip可以免费观看找工作视频
                    if ($company_vip_list_res) {
                        $vip_status = true; //发布会员
                    } else {
                        $vip_status = false; //发布会员
                    }
                } else {
                    //是否vip
                    $vip_status = false;
                    $vip_status_second = false;

                    //视频列表 限制15个视频
                    $video_list = Db::name('video')
                        ->order('id desc')
                        ->field('id,user_id ,cover_image,video_url,content,add_time')
                        ->where($where)
                        ->where(['status' => 1])
                        ->limit(100)
                        ->select();
                    foreach ($video_list as $k => $v) {
                        //video_id 拼接
                        $str .= $v['id'] . ',';
                        $ids = rtrim($str, ',');
                    }

                    $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => $type])->find(); //视频ids列表是否存在
                    if ($video_list_res) {
                        $video = $this->putong_member($video_list_res, $user_id, $lat, $lng);
                    } else {
                        $video = $this->putong_member_list($user_id, $ids, $type, $lat, $lng);
                    }
                }
            }
        }
        $on_status = Db::name('config')->where(['id' => 1])->value('is_show');
        $result = [
            'status' => 1,
            'msg' => '获取成功!',
            'data' =>
            [
                'video' => $video,
                'vip_status' => $vip_status,
                'vip_status_second' => $vip_status_second,
                'on_status' => $on_status
            ]
        ];
        return $result;
    }
    /**
     * 普通会员
     *
     * @Author DSJ
     * @DateTime 2020-06-08 18:05:54
     * @param undefined
     * @param [type] $user_id
     * @param [type] $where
     * @param [type] $page
     * @param [type] $limit
     * @return void
     */
    public function putong_member($video_list_res, $user_id, $lat, $lng)
    {

        $ids = $video_list_res['ids'];
        $video = Db::name('video')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id', 'LEFT')
            ->order('id desc')
            ->field('a.id,a.user_id ,a.title,a.city,a.cover_image,a.video_url,a.content,a.add_time,b.nickname,b.avatar,a.lat,a.lng')
            ->where(['a.id' => array('in', $ids), 'a.status' => 1])
            ->select();
        foreach ($video as $k => $v) {
            $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['type'] == 1) {
                $video[$k]['type_name'] = '招人';
            }
            if ($v['type'] == 2) {
                $video[$k]['type_name'] = '找工作';
            }
            if ($v['type'] == 3) {
                $video[$k]['type_name'] = '地方';
            }
            $video[$k]['distance'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            if ($video[$k]['distance'] < 1) {
                $video[$k]['distance'] = $video[$k]['distance'] * 1000 . 'm';
            } else {
                $video[$k]['distance'] = $video[$k]['distance'] . 'km';
            }
            $video[$k]['distance_type'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            //是否关注
            $user_follow = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $v['user_id']])->find();
            $video[$k]['user_follow_status'] = $user_follow ? true : false;

            //是否点赞
            $user_like = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->find();
            $video[$k]['user_like_status'] = $user_like ? true : false;

            //个人标志
            $video[$k]['user_video_status'] = $v['user_id'] == $user_id ? true : false;

            //统计评论数
            $video[$k]['comment_num'] = Db::name('comment')->where(['video_id' => $v['id'], 'is_show' => 1])->field('video_id')->count();
            //统计点赞
            $video[$k]['like_num'] = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->field('user_id ,video_id')->count();
        }

        $video = change_sort($video, 'distance_type', SORT_ASC); //降序
        return $video;
    }
    /**
     * 普通会员列表
     *
     * @Author DSJ
     * @DateTime 2020-06-08 18:06:06
     * @param undefined
     * @param [type] $user_id
     * @param [type] $where
     * @param [type] $page
     * @param [type] $limit
     * @return void
     */
    public function putong_member_list($user_id, $ids, $type, $lat, $lng)
    {
        $video_data = [
            'user_id' => $user_id,
            'date'    => time(),
            'ids'     => $ids,
            'type'    => $type
        ];
        Db::name('video_visit')->insert($video_data);

        $video_list_res = Db::name('video_visit')->where(['user_id' => $user_id, 'type' => $type])->find();
        $ids = $video_list_res['ids'];
        $video = Db::name('video')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id', 'LEFT')
            ->order('id desc')
            ->field('a.id,a.user_id ,a.title,a.city,a.cover_image,a.video_url,a.content,a.add_time,b.nickname,b.avatar,a.lat,a.lng')
            ->where(['a.id' => array('in', $ids), 'a.status' => 1])
            ->select();
        foreach ($video as $k => $v) {
            $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['type'] == 1) {
                $video[$k]['type_name'] = '招人';
            }
            if ($v['type'] == 2) {
                $video[$k]['type_name'] = '找工作';
            }
            if ($v['type'] == 3) {
                $video[$k]['type_name'] = '地方';
            }
            $video[$k]['distance'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            if ($video[$k]['distance'] < 1) {
                $video[$k]['distance'] = $video[$k]['distance'] * 1000 . 'm';
            } else {
                $video[$k]['distance'] = $video[$k]['distance'] . 'km';
            }
            $video[$k]['distance_type'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            //是否关注
            $user_follow = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $v['user_id']])->find();
            $video[$k]['user_follow_status'] = $user_follow ? true : false;

            //是否点赞
            $user_like = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->find();
            $video[$k]['user_like_status'] = $user_like ? true : false;

            //个人标志
            $video[$k]['user_video_status'] = $v['user_id'] == $user_id ? true : false;

            //统计评论数
            $video[$k]['comment_num'] = Db::name('comment')->where(['video_id' => $v['id'], 'is_show' => 1])->field('video_id')->count();
            //统计点赞
            $video[$k]['like_num'] = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->field('user_id ,video_id')->count();
        }
        $video = change_sort($video, 'distance_type', SORT_ASC); //降序
        return $video;
    }
    /**
     * 会员数据
     *
     * @Author DSJ
     * @DateTime 2020-06-08 17:35:38
     * @param user_id
     * @param [user_id] $user_id
     * @param [comment_id] $comment_id
     */
    public function member_data($user_id, $where, $page, $limit, $lat, $lng)
    {
        //视频列表 免费观看
        $video = Db::name('video')->alias('a')
            ->join('yx_user b', 'a.user_id = b.user_id', 'LEFT')
            ->order('id desc')
            ->field('a.id,a.user_id ,a.title,a.city,a.cover_image,a.video_url,a.content,a.add_time,b.nickname,b.avatar,a.lat,a.lng')
            ->where($where)
            ->where(['a.status' => 1])
            ->page($page, $limit)
            ->select();

        foreach ($video as $k => $v) {
            $video[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['type'] == 1) {
                $video[$k]['type_name'] = '招人';
            }
            if ($v['type'] == 2) {
                $video[$k]['type_name'] = '找工作';
            }
            if ($v['type'] == 3) {
                $video[$k]['type_name'] = '地方';
            }
            $video[$k]['distance'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            if ($video[$k]['distance'] < 1) {
                $video[$k]['distance'] = $video[$k]['distance'] * 1000 . 'm';
            } else {
                $video[$k]['distance'] = $video[$k]['distance'] . 'km';
            }
            $video[$k]['distance_type'] = getDistance($lat, $lng, $v['lat'], $v['lng']);
            //是否关注
            $user_follow = Db::name('user_follow')->where(['user_id' => $user_id, 'follow_user_id' => $v['user_id']])->find();
            $video[$k]['user_follow_status'] = $user_follow ? true : false;

            //是否点赞
            $user_like = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->find();
            $video[$k]['user_like_status'] = $user_like ? true : false;

            //个人标志
            $video[$k]['user_video_status'] = $v['user_id'] == $user_id ? true : false;

            //统计评论数
            $video[$k]['comment_num'] = Db::name('comment')->where(['video_id' => $v['id'], 'is_show' => 1])->field('video_id')->count();
            //统计点赞
            $video[$k]['like_num'] = Db::name('user_like')->where(['user_id' => $user_id, 'video_id' => $v['id']])->field('user_id ,video_id')->count();
        }
        $video = change_sort($video, 'distance_type', SORT_ASC); //降序

        return $video;
    }
}
