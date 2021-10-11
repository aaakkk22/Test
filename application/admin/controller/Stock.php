<?php

namespace app\admin\controller;

use think\Db;

/**
 * 仓库控制器
 *
 * @Description
 */
class Stock extends Base
{
    /**
     * @入库列表
     * @return void
     */
    public function add_stock()
    {
        $keyword = input('keyword');

        if ($keyword) {
            $where['b.medicine_name'] = array('like', "%" . $keyword . "%");
        }

        $list = Db::name('medicine_add_store')->alias('a')
            ->join('tp_medicine_list b' ,'a.medicine_id = b.id','LEFT')
            ->field('a.*,b.medicine_name')
            ->order('a.id desc')
            ->where($where)
            ->paginate(10);
        $page = $list->render();
        $list = $list->toArray()['data'];

        // dump($list);die;
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    /**
     * 药品入库
     */
    public function add_stock_add_edit()
    {
        $id =  input('id');

        if (!$id) {

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];

                $data['add_time'] = time();

                $stock = get_medicine_stock($data['medicine_id']);

                if ($stock <= 0) {
                    ajaxReturn(array('status' => -1, 'msg' => '库存不足!'));
                } else if ($stock < $data['nums']) {
                    ajaxReturn(array('status' => -1, 'msg' => '超出原库存!'));
                }
                reduce_medicine_stock($data['medicine_id'], $data['nums']); //减少库存
                // dump($data);
                // die;
                $res = Db::name('medicine_add_store')->insert($data);
                if (!$res) {
                    ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
                } else {

                    ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
                }
            }
        } else {

            $where['id'] = $id;
            $list = Db::name('medicine_add_store')->where($where)->find();
            $this->assign('list', $list);

            if ($this->request->method() == 'POST') {

                $post = input('');
                $data = $post['data'];

                $stock = get_medicine_nums($data['medicine_id']);

                if ($stock <= 0) {
                    ajaxReturn(array('status' => -1, 'msg' => '库存不足!'));
                } else if ($stock < $data['nums']) {
                    ajaxReturn(array('status' => -1, 'msg' => '超出原库存!'));
                }

                update_medicine_stock($data['medicine_id'], $data['nums'], $stock); //修改库存
                //dump($data);die;
                $res = Db::name('medicine_add_store')->where($where)->update($data);

                ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
            }
        }
        $medicine_list = Db::name('medicine_list')->where(['stock' => array('>', 0)])->order('add_time desc')->select();


        $supplier_list = Db::name('supplier_list')->order('add_time desc')->select();

        $admin_list = Db::name("admin")->where(['roles' => 3])->order('add_time desc')->select();

        $this->assign('medicine_list', $medicine_list);
        $this->assign('supplier_list', $supplier_list);
        $this->assign('admin_list', $admin_list);
        return $this->fetch();
    }
    /**
     * 出库
     */
    public function out_stock()
    {
        $keyword = input('keyword');

        if ($keyword) {
            $where['b.medicine_name'] = array('like', "%" . $keyword . "%");
        }
        $list = Db::name('medicine_out_store')->alias('a')
            ->join('tp_medicine_list b' ,'a.medicine_id = b.id','LEFT')
            ->field('a.*,b.medicine_name')
            ->order('a.id desc')
            ->where($where)
            ->paginate(10);
        $page = $list->render();
        $list = $list->toArray()['data'];

        foreach ($list as $k => $v) {
            $price = get_medicine_price($v['medicine_id']);

            $list[$k]['total_price'] = $price * $v['nums'];
        }
        // dump($list);die;
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    /**
     * 出库操作
     */
    public function out_stock_add_edit()
    {


        if ($this->request->method() == 'POST') {

            $post = input('');
            $data = $post['data'];

            $data['update_time'] = time();

            $stock = get_medicine_add_stock($data['medicine_id']);

            if ($stock <= 0) {
                ajaxReturn(array('status' => -1, 'msg' => '库存不足!'));
            } else if ($stock < $data['nums']) {
                ajaxReturn(array('status' => -1, 'msg' => '超出原库存!'));
            }
            reduce_medicine_add_stock($data['medicine_id'], $data['nums']); //减少库存
            // dump($data);
            // die;
            $res = Db::name('medicine_out_store')->insert($data);
            if (!$res) {
                ajaxReturn(array('status' => -1, 'msg' => '操作失败!'));
            } else {

                ajaxReturn(array('status' => 1, 'msg' => '操作成功!'));
            }
        }

        $medicine_list = Db::name('medicine_add_store')->alias('a')
            ->join('tp_medicine_list b', 'a.medicine_id = b.id', 'LEFT')
            ->field("a.medicine_id , b.medicine_name,a.nums")
            ->order('a.add_time desc')
            ->where(['a.nums' => array('>' ,0)])
            ->select();


        $user_list = Db::name('users')->order('add_time desc')->select();

        $admin_list = Db::name("admin")->where(['roles' => 3])->order('add_time desc')->select();

        $this->assign('medicine_list', $medicine_list);
        $this->assign('user_list', $user_list);
        $this->assign('admin_list', $admin_list);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('id');
        if (!$id) {
            $this->redirect('medicine/index');
        }

        if (Db::name('medicine_list')->delete($id)) {
            ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
        } else {
            ajaxReturn(array('status' => -1, 'msg' => '删除失败!'));
        }
    }
}
