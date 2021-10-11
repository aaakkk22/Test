<?php

namespace app\api\controller;

use think\Db;
use app\common\logic\OssLogic;


class Category extends Base
{
    /**
     * 初始化
     *
     * @Author DSJ
     * @DateTime 2020-06-18 17:43:44
     * @param token
     * @return void
     */
    public function _initialize()
    {
        $user_id = $this->get_user_id();
        if (!$user_id) {
            ajaxReturn(['status' => -1, 'msg' => 'token参数不存在', 'data' => '']);
        }

        $this->user_id = $user_id; //用户id
    }
    /**
     * 编辑数据
     *
     * @Author DSJ
     * @DateTime 2020-06-18 19:27:22
     * @param undefined
     * @return void
     */
    public function edit_data()
    {
        $id =  input('id');

        //列表
        $menu = Db::name('menu_category')->where(['id' => $id])->find();
        ajaxReturn(array('status' => 1, 'msg' => '获取成功!', 'data' => $menu));
    }
    /**
     * 添加、编辑
     *
     * @Author DSJ
     * @DateTime 2020-06-18 17:57:02
     * @param undefined
     * @return void
     */
    public function add_edit()
    {
        $id =  input('id');

        if (!$id) {
            //名称
            $name = input('name');
            if (!$name) {
                ajaxReturn(array('status' => -1, 'msg' => 'name不存在!'));
            }
            $is_show = input('is_show');
            if (!isset($is_show)) {
                ajaxReturn(array('status' => -1, 'msg' => 'is_show不存在!'));
            }
            $data = [
                'name'     => $name,
                'user_id'  => $this->user_id,
                'sort'     => 0,
                'is_show'  => $is_show,
                'add_time' => time()
            ];

            $res = Db::name('menu_category')->insert($data);
            if ($res) {
                ajaxReturn(array('status' => 1, 'msg' => '添加成功!'));
            } else {
                ajaxReturn(array('status' => -1, 'msg' => '添加失败!'));
            }
        } else {

            $where['id'] = $id;

            //名称
            $name = input('name');
            if (!$name) {
                ajaxReturn(array('status' => -1, 'msg' => 'name不存在!'));
            }
            $is_show = input('is_show');
            if (!isset($is_show)) {
                ajaxReturn(array('status' => -1, 'msg' => 'is_show不存在!'));
            }
            $data = [
                'name'     => $name,
                'is_show'  => $is_show,
            ];
            Db::name('menu_category')->where($where)->update($data);
            ajaxReturn(array('status' => 1, 'msg' => '编辑成功!'));
        }
    }
    /**
     * 删除
     *
     * @Author DSJ
     * @DateTime 2020-06-18 18:02:03
     * @param undefined
     * @return void
     */
    public function delete()
    {
        $id =  input('id');
        if (!$id) {
            ajaxReturn(array('status' => -1, 'msg' => 'id不存在!'));
        }
        Db::name('menu_category')->where(['id' => $id])->delete();
        ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
    }
    /**
     * 分类列表
     *
     * @Author DSJ
     * @DateTime 2020-06-18 17:43:44
     * @param token
     * @return void
     */
    public function menu_list()
    {
        $video_id = input('video_id');
        
        if ($video_id) {
            $user_id = Db::name('video')->where(['id' => $video_id])->value('user_id');
        } else {
            $user_id = $this->user_id;
        }
        //条件
        $where['user_id'] = $user_id;
        //列表
        $menu_category = Db::name('menu_category')->where($where)->field('id,name,is_show,add_time')->select();
        foreach ($menu_category as $k => $v) {
            $menu_category[$k]['add_time'] = date('Y-m-d', $v['add_time']);
        }
        $result = [
            'menu_category' => $menu_category
        ];
        ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $result));
    }
}
