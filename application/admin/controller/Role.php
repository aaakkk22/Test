<?php

namespace app\admin\controller;

use think\Db;

class Role extends Base
{
    /**
     * @角色列表
     * @return void
     */
    public function index()
    {
        $list = Db::name('role_list')
            ->order('id desc')
            ->paginate(10);
        $page = $list->render();
        $list = $list->toArray()['data'];

        // dump($list);die;
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
        
    }
    public function add_edit()
    {
        $id =  input('id');

        if (!$id) {
          
            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];

                $data['add_time'] = time();
                //dump($data);die;
                $res = Db::name('role_list')->insert($data);
                if (!$res) {
                    ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
                } else {
                    ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                }
            }
        } else {

            $where['id'] = $id;
            $list = Db::name('role_list')->where($where)->find();
            $this->assign('list', $list);

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];
                //$data['add_time'] = time();
                //dump($data);die;
                $res = Db::name('role_list')->where($where)->update($data);
               
                ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                
            }
        }
        return $this->fetch();
    }

   /**
     * 删除
     */
    public function del()
    {
        $id = input('id');
        if (!$id) {
            $this->redirect('role/index');
        }

        if (Db::name('role_list')->delete($id)) {
            ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '删除失败!'));
        }
    }
}