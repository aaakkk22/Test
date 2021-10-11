<?php

namespace app\admin\controller;

use think\Db;
/**
 * 供应商控制器
 *
 * @Description
 */
class Supplier extends Base
{
    /**
     * @供应商列表
     * @return void
     */
    public function index()
    {
        $keyword = input('keyword');

        if ($keyword) {
            $where['name'] = array('like', "%" . $keyword . "%");
        }

        $list = Db::name('supplier_list')
            ->order('id desc')
            ->where($where)
            ->paginate(10);
        $page = $list->render();
        $list = $list->toArray()['data'];

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
                $res = Db::name('supplier_list')->insert($data);
                if (!$res) {
                    ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
                } else {
                    ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                }
            }
        } else {

            $where['id'] = $id;
            $list = Db::name('supplier_list')->where($where)->find();
            $this->assign('list', $list);

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];
                //$data['add_time'] = time();
                unset($post['id']);
                //dump($data);die;
                $res = Db::name('supplier_list')->where($where)->update($data);
               
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
            $this->redirect('supplier/index');
        }

        if (Db::name('supplier_list')->delete($id)) {
            ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '删除失败!'));
        }
    }
}