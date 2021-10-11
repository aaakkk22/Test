<?php

namespace app\api\controller;

use think\Controller;
use think\Db;


class News extends Base
{
    /**
     * 消息列表
     * 2020.04.15
     */
    public function news_list()
    {

        $page = input('page', 1);
        $limit = input('limit', 10);
        //新闻列表
        $news_list = Db::name('news')
            ->field('id,title,description,add_time,image')
            ->page($page, $limit)
            ->order('add_time desc')
            ->select();
        foreach($news_list as $k =>$v){
            $news_list[$k]['add_time'] = date('Y-m-d',$v['add_time']);
        }
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $news_list]);
    }
    /**
     * 消息详情
     * 2020.04.15
     */
    public function news_details()
    {
        $id = input('id');
        if(!$id){
            ajaxReturn(['status' => -1, 'msg' => 'id参数不存在', 'data' => '']);
        }
        //新闻列表
        $news_res = Db::name('news')
            ->where(['id' => $id])
            ->field('title,description,add_time,image,content')
            ->find();
        $news_res['add_time'] = date('Y-m-d H:i:s',$news_res['add_time']);
        ajaxReturn(['status' => 1, 'msg' => '获取成功', 'data' => $news_res]);    
    }
   
}
