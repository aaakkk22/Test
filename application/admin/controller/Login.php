<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;

class Login extends Controller
{
    // public function _initialize()
    // {
    //     $userinfo = Session::get('userinfo');;
    //     //判断用户是否登录
    //     if ($userinfo) {
    //         $url = url('index/index');
    //         echo "<script>top.location.href='$url'</script>";
    //         exit;
    //     }
    // }

    public function login()
    {

        if ($this->request->method() == 'POST') {
            $post = input('');

            $name = $post['name'];
            $password = $post['password'];
            $roles = $post['role'];

            if (!$name) {
                ajaxReturn(['status' => -1, 'msg' => '用户名不能为空', 'data' => '']);
            }
            if (!$password) {
                ajaxReturn(['status' => -1, 'msg' => '密码不能为空', 'data' => '']);
            }
            if (!isset($roles)) {
                ajaxReturn(['status' => -1, 'msg' => '权限不能为空', 'data' => '']);
            }
            // dump($roles);
            // dump($password);
            
            // die;
            $data = Db::name('admin')->where(['username' => $name, 'roles' => $roles])->find();
            if (!$data) {
                ajaxReturn(['status' => -1, 'msg' => '用户尚未注册,请立即注册!', 'data' => '']);
            }
            //dump($data);die;
            if (md5($password) != $data['password']) {
                ajaxReturn(['status' => -1, 'msg' => '密码错误', 'data' => '']);
            }

            Session::set('userinfo',$data);
            ajaxReturn(['status' => 1, 'msg' => '登录成功!']);
        }
        $role_list =  $this->staff_list();
        $this->assign('list', $role_list);
        return $this->fetch();
    }
    public function reg()
    {

        if ($this->request->method() == 'POST') {
            $post = input('');
            //dump($post);die;
            $username = $post['name'];
            if (empty($username)) {
                ajaxReturn(['status' => -1, 'msg' => '用户名不能为空']);
            }
            $email = $post['email'];
            if (empty($email)) {
                ajaxReturn(['status' => -1, 'msg' => '邮箱不能为空']);
            }
            $role = $post['role'];
            if (!isset($role)) {
                ajaxReturn(['status' => -1, 'msg' => '权限不能为空']);
            }
            $password = $post['password'];
            if (empty($password)) {
                ajaxReturn(['status' => -1, 'msg' => '密码不能为空']);
            }
            $password_two = $post['password_two'];
            if (empty($password_two)) {
                ajaxReturn(['status' => -1, 'msg' => '密码2不能为空']);
            }
            if ($password != $password_two) {
                ajaxReturn(['status' => -1, 'msg' => '两次密码输入不一致']);
            }

            if(!is_email($email)){
                ajaxReturn(['status' => -1, 'msg' => '邮箱格式不正确，请重新填写！']);
            }

            //是否存在
            $user = Db::name('admin')->where(['username' => $username ,'roles' => $role])->find();
            if ($user) {
                ajaxReturn(['status' => 1, 'msg' => '用户已存在，无需注册!', 'data' => '']);
            } else {
                $user_data = array(
                    'username' => $username,
                    'password' => md5($password),
                    'add_time' => time(),
                    'email'    => $email,
                    'roles'    => $role,

                );
                //dump($user_data);die;
                $admin_insert_res = Db::name('admin')->insert($user_data);
                if($admin_insert_res){
                    ajaxReturn(['status' => 1, 'msg' => '注册成功！', 'data' => '']);
                }
                else {
                    ajaxReturn(['status' => -1, 'msg' => '注册失败！']);
                }
                
            }
        } 
        $role_list =  $this->staff_list();
        $this->assign('list', $role_list);
        return $this->fetch();
    }
    /**
     * 权限列表
     */
    public function staff_list()
    {
        $list  = Db::name('role_list')->select();
        return $list;
    }
    /**
     * 退出登录
     */
    public function logout()
    {
        Session::delete('userinfo');
        cookie('userinfo', null);
        $this->redirect('admin/login/login');
    }
}
