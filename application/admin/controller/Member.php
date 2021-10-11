<?php

namespace app\admin\controller;

use think\Db;

class Member extends Base
{
    /**
     * 客户列表
     * 2020.12.16
     */
    public function member_list()
    {
        $keyword = input('keyword');

        if ($keyword) {
            $where['username'] = array('like', "%" . $keyword . "%");
        }

        $member_list = Db::name('users')
            ->order('user_id desc')
            ->where($where)
            ->paginate(10);

        //会员列表
        $page = $member_list->render();
        $member_list = $member_list->toArray();
        $member_list = $member_list['data'];

        $this->assign('list', $member_list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    /**
     * 添加用户
     *
     * @Author DSJ
     * @DateTime 2020-08-01 11:00:37
     * @param undefined
     * @return void
     */
    public function add_user()
    {

        $id =  input('id');

        if (!$id) {

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];
                //如果头像为空
                $data['image'] = $data['image'] ? $data['image'] : 'https://www.c3w.com.cn/public/images/avatar.png';

                $data['add_time'] = time();
                //dump($data);die;
                $res = Db::name('users')->insert($data);
                if (!$res) {
                    ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
                } else {
                    ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                }
            }
        } else {

            $where['user_id'] = $id;
            $list = Db::name('users')->where($where)->find();
            $this->assign('list', $list);

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];
                //如果头像为空
                $data['image'] = $data['image'] ? $data['image'] : 'https://www.c3w.com.cn/public/images/avatar.png';

                //dump($data);die;
                $res = Db::name('users')->where($where)->update($data);
                
                ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                
            }
        }
        return $this->fetch();
    }

    /**
     * 客户列表
     * 2020.12.16
     */
    public function admin_list()
    {
        $keyword = input('keyword');

        if ($keyword) {
            $where['username'] = array('like', "%" . $keyword . "%");
        }

        $member_list = Db::name('admin')
            ->order('id desc')
            ->where($where)
            ->paginate(10);

        //会员列表
        $page = $member_list->render();
        $member_list = $member_list->toArray();
        $member_list = $member_list['data'];


        $this->assign('list', $member_list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    /**
     * 添加用户
     *
     * @Author DSJ
     * @DateTime 2020-08-01 11:00:37
     * @param undefined
     * @return void
     */
    public function add_edit()
    {

        $id =  input('id');

        if (!$id) {

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];

                $data['password'] = md5($data['password']);

                $data['add_time'] = time();

                if (!is_email($data['email'])) {
                    ajaxReturn(['status' => -1, 'msg' => '邮箱格式不正确，请重新填写！']);
                }
                //dump($data);die;
                $user = Db::name('admin')->where(['username' => $data['username'] ,'roles' => $data['roles']])->find();
                if ($user) {
                    ajaxReturn(['status' => 1, 'msg' => '用户已存在，无需注册!', 'data' => '']);
                } 

                $res = Db::name('admin')->insert($data);
                if (!$res) {
                    ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
                } else {
                    ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                }
            }
        } else {

            $where['id'] = $id;
            $list = Db::name('admin')->where($where)->find();
            $this->assign('list', $list);

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];
                $data['password'] = md5($data['password']);
                //dump($data);die;
                if (!is_email($data['email'])) {
                    ajaxReturn(['status' => -1, 'msg' => '邮箱格式不正确，请重新填写！']);
                }
                $res = Db::name('admin')->where($where)->update($data);

                ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
            }
        }
        $staff = staff_list();
        $this->assign('staff', $staff);
        return $this->fetch();
    }
    /**
     * 删除
     */
    public function del()
    {
        $id = input('id');
        if (!$id) {
            $this->redirect('member/admin_list');
        }

        if (Db::name('admin')->delete($id)) {
            ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '删除失败!'));
        }
    }
    /**
     * 删除2
     */
    public function del_users()
    {
        $id = input('id');
        if (!$id) {
            $this->redirect('member/member_list');
        }

        if (Db::name('users')->delete($id)) {
            ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '删除失败!'));
        }
    }
}
