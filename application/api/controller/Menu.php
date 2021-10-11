<?php

namespace app\api\controller;

use think\Db;
use app\common\logic\OssLogic;


class Menu extends Base
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
     * 修改数量
     *
     * @Author DSJ
     * @DateTime 2020-06-22 15:48:49
     * @param undefined
     * @return void
     */
    public function update_num()
    {
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type不存在！', 'data' => []]);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id不存在！', 'data' => []]);
        }
        $num = input('num');
        if (!$num) {
            ajaxReturn(['status' => -1, 'msg' => 'num不存在！', 'data' => []]);
        }
        $menu_id = input('menu_id');
        if (!$menu_id) {
            ajaxReturn(['status' => -1, 'msg' => 'menu_id不存在！', 'data' => []]);
        }

        $sum = 0;
        //是否存在
        $user_menu = Db::name('user_menu')->where(['user_id' => $this->user_id, 'video_id' => $video_id, 'is_deleted' => 0, 'menu_id' => $menu_id])->find();
        if (!$user_menu) {
            ajaxReturn(['status' => -1, 'msg' => '请先勾选菜式后再调整数量！', 'data' => []]);
        } else {
            if ($type == 'plus') {
                Db::name('user_menu')->where(['user_id' => $this->user_id, 'video_id' => $video_id, 'is_deleted' => 0, 'menu_id' => $menu_id])->update(['num' => $num]);
            } else {
                // if ($num == 1) {
                //     ajaxReturn(['status' => -1, 'msg' => '个数不能为空！', 'data' => []]);
                // }
                Db::name('user_menu')->where(['user_id' => $this->user_id, 'video_id' => $video_id, 'is_deleted' => 0, 'menu_id' => $menu_id])->update(['num' => $num]);
            }
        }
        //计算总计
        $total = Db::name('user_menu')->alias('a')
            ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
            ->field('b.shop_price,a.num')
            ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id])
            ->select();
        foreach ($total as $k => $v) {
            $sum += $v['shop_price'] * $v['num'];
        }
        $result = [
            'total' => floor($sum * 100) / 100,
            'discount' => floor($sum * 0.85 * 100) / 100
        ];
        ajaxReturn(array('status' => 1, 'msg' => '操作成功!', 'data' => $result));
    }
    /**
     * 分类菜单列表
     *
     * @Author DSJ
     * @DateTime 2020-06-19 17:03:20
     * @param undefined
     * @return void
     */
    public function category_goods()
    {

        $cat_id = input('cat_id');
        if (!$cat_id) {
            ajaxReturn(['status' => -1, 'msg' => 'cat_id不存在！', 'data' => []]);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id不存在！', 'data' => []]);
        }
        $page = input('page', 1);
        $limit = input('limit', 20);
        $num = 0;

        $category = Db::name('menu')
            ->where(["cat_id" => $cat_id, 'is_on_sale' => 1])
            ->page($page, $limit)
            ->order('id desc')
            ->select();
        //dump($category);die;
        foreach ($category as $k => $v) {

            $res = Db::name('user_menu')->where(['user_id' => $this->user_id, 'menu_id' => $v['id'], 'is_deleted' => 0])->find();

            if ($res) {
                $category[$k]['select_status'] = true;
                $category[$k]['number'] = $res['num'];
            } else {
                $category[$k]['select_status'] = false;
                $category[$k]['number'] = 1;
            }
        }

        //计算总计
        $total = Db::name('user_menu')->alias('a')
            ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
            ->field('b.shop_price,a.num')
            ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id])
            ->select();
        foreach ($total as $k => $v) {
            $num += $v['shop_price'] * $v['num'];
        }


        $result = [
            'category' => $category,
            'total'    => floor($num * 100) / 100,
            'discount' => floor($num * 0.85 * 100) / 100
        ];

        ajaxReturn(['status' => 1, 'msg' => '请求接口成功！', 'data' => $result]);
    }
    /**
     * 用户选菜
     *
     * @Author DSJ
     * @DateTime 2020-06-19 17:18:52
     * @param undefined
     * @return void
     */
    public function user_menu()
    {
        $menu_id = input('menu_id');
        if (!$menu_id) {
            ajaxReturn(['status' => -1, 'msg' => 'menu_id不存在！', 'data' => []]);
        }
        $type = input('type');
        if (!$type) {
            ajaxReturn(['status' => -1, 'msg' => 'type不存在！', 'data' => []]);
        }
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(['status' => -1, 'msg' => 'video_id不存在！', 'data' => []]);
        }
        $number = input('num');

        $sum = 0;
        //dump($type);die;
        if ($type == 'true') {
            $data = [
                'user_id' => $this->user_id,
                'menu_id' => $menu_id,
                'video_id' => $video_id,
                'num'     => $number,
                'add_time' => time()
            ];
            Db::name('user_menu')->insert($data);
        } else {

            Db::name('user_menu')->where(['menu_id' => $menu_id])->delete();
        }
        //计算总计
        $total = Db::name('user_menu')->alias('a')
            ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
            ->field('b.shop_price,a.num')
            ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id])
            ->select();
        foreach ($total as $k => $v) {
            $sum += $v['shop_price'] * $v['num'];
        }
        $result = [
            'total' => floor($sum * 100) / 100,
            'discount' => floor($sum * 0.85 * 100) / 100
        ];

        ajaxReturn(array('status' => 1, 'msg' => '操作成功!', 'data' => $result));
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
        $menu = Db::name('menu')->where(['id' => $id])->find();
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
            $title = input('title');
            if (!$title) {
                ajaxReturn(array('status' => -1, 'msg' => 'title不存在!'));
            }

            $is_on_sale = input('is_on_sale');
            if (!isset($is_on_sale)) {
                ajaxReturn(array('status' => -1, 'msg' => 'is_on_sale不存在!'));
            }
            $cat_id = input('cat_id');
            if (!$cat_id) {
                ajaxReturn(array('status' => -1, 'msg' => 'cat_id不存在!'));
            }
            $shop_price = input('shop_price');
            if (!$shop_price) {
                ajaxReturn(array('status' => -1, 'msg' => 'shop_price不存在!'));
            }
            // $discount_price = input('discount_price');
            // if (!$discount_price) {
            //     ajaxReturn(array('status' => -1, 'msg' => 'discount_price不存在!'));
            // }
            $image = input('image');
            if (!$image) {
                ajaxReturn(array('status' => -1, 'msg' => 'image不存在!'));
            }
            $data = [
                'title'          => $title,
                'cat_id'         => $cat_id,
                'shop_price'     => $shop_price,
                // 'discount_price' => $discount_price,
                'image'          => $image,
                'is_on_sale'     => $is_on_sale,
                'user_id'        => $this->user_id,
                'add_time'       => time()
            ];

            $res = Db::name('menu')->insert($data);
            if ($res) {
                ajaxReturn(array('status' => 1, 'msg' => '添加成功!'));
            } else {
                ajaxReturn(array('status' => -1, 'msg' => '添加失败!'));
            }
        } else {

            $where['id'] = $id;

            //名称
            $title = input('title');
            if (!$title) {
                ajaxReturn(array('status' => -1, 'msg' => 'title不存在!'));
            }
            $is_on_sale = input('is_on_sale');
            if (!isset($is_on_sale)) {
                ajaxReturn(array('status' => -1, 'msg' => 'is_on_sale不存在!'));
            }
            $cat_id = input('cat_id');
            if (!$cat_id) {
                ajaxReturn(array('status' => -1, 'msg' => 'cat_id不存在!'));
            }
            $shop_price = input('shop_price');
            if (!$shop_price) {
                ajaxReturn(array('status' => -1, 'msg' => 'shop_price不存在!'));
            }
            // $discount_price = input('discount_price');
            // if (!$discount_price) {
            //     ajaxReturn(array('status' => -1, 'msg' => 'discount_price不存在!'));
            // }
            $image = input('image');
            if (!$image) {
                ajaxReturn(array('status' => -1, 'msg' => 'image不存在!'));
            }

            $data = [
                'title'          => $title,
                'cat_id'         => $cat_id,
                'shop_price'     => $shop_price,
                // 'discount_price' => $discount_price,
                'image'          => $image,
                'is_on_sale'     => $is_on_sale
            ];

            Db::name('menu')->where($where)->update($data);
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
        Db::name('menu')->where(['id' => $id])->delete();
        ajaxReturn(array('status' => 1, 'msg' => '删除成功!'));
    }
    /**
     * 下单选菜列表
     *
     * @Author DSJ
     * @DateTime 2020-06-18 17:43:44
     * @param token
     * @return void
     */
    public function buy_menu()
    {
        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(array('status' => -1, 'msg' => '视频id不存在!'));
        }

        $sum = 0;
        //where
        $where['a.video_id'] = $video_id;
        $where['a.user_id'] = $this->user_id;
        $where['a.is_deleted'] = 0;
        //选菜列表
        $menu_list = Db::name("user_menu")->alias('a')
            ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
            ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
            ->where($where)
            ->order('a.add_time desc')
            ->select();
        //计算总计
        $total = Db::name('user_menu')->alias('a')
            ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
            ->field('b.shop_price,a.num')
            ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id])
            ->select();
        foreach ($total as $k => $v) {
            $total[$k]['shop_price'] = $v['shop_price'] * 0.85;
            $sum += $v['shop_price'] * $v['num'];
        }
        $result = [
            'menu_list' => $menu_list,
            'total'     => floor($sum * 100) / 100,
            'discount'  => floor($sum * 0.85 * 100) / 100
        ];
        ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $result));
    }
    /**
     * 买家和商家选菜列表
     *
     * @Author DSJ
     * @DateTime 2020-06-22 17:35:43
     * @param undefined
     * @return void
     */
    public function select_menu()
    {
        $id = input('id');
        if (!$id) {
            ajaxReturn(array('status' => -1, 'msg' => 'id不存在!'));
        }

        $video_id = input('video_id');
        if (!$video_id) {
            ajaxReturn(array('status' => -1, 'msg' => '视频id不存在!'));
        }
        $sum = 0;
        $where['a.video_id'] = $video_id;

        $user_id = input('user_id');
        if ($user_id) {
            $where['a.user_id'] = $user_id;
            //是否存在订单
            $order = Db::name('order')->where(['type' => 4, 'user_id' => $user_id, 'vip_id' => $id])->find();
            if (!$order) {
                $where['a.is_deleted'] = 0;
                $where['a.buy_id'] = $id;
                //选菜列表
                $menu_list = Db::name("user_menu")->alias('a')
                    ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                    ->where($where)
                    ->order('a.add_time desc')
                    ->select();
                $total = Db::name('user_menu')->alias('a')
                    ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('b.shop_price,a.num')
                    ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id, 'buy_id' => $id])
                    ->select();
                foreach ($total as $k => $v) {
                    $sum += $v['shop_price'] * $v['num'];
                }
            } else {
                $where['a.is_deleted'] = 1;
                $where['a.buy_id'] = $id;
                $menu_list = Db::name("user_menu")->alias('a')
                    ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id')
                    ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                    ->where($where)
                    ->order('a.add_time desc')
                    ->select();
                // foreach ($menu_list as $k => $v) {
                //     $menu_list[$k]['shop_price'] = $v['shop_price'] * 0.85;
                // }
                //dump($menu_list);die;
                //计算总计
                $total = Db::name('user_menu')->alias('a')
                    ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('b.shop_price,a.num')
                    ->where(['a.user_id' => $user_id, 'a.is_deleted' => 1, 'a.video_id' => $video_id, 'buy_id' => $id])
                    ->select();
                foreach ($total as $k => $v) {
                    $sum += $v['shop_price'] * $v['num'];
                }
                //是否是退单菜单
                $return_goods = Db::name('return_goods')->where(['order_id' => $order['order_id']])->find();
                if ($return_goods) {
                    $menu_list = Db::name("user_menu")->alias('a')
                        ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                        ->join('yx_buy c', 'a.buy_id = c.id')
                        ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                        ->where(['a.user_id' => $user_id, 'a.is_deleted' => 2, 'a.video_id' => $video_id, 'buy_id' => $id])
                        ->order('a.add_time desc')
                        ->select();

                    //dump($menu_list);die;
                    //计算总计
                    $total = Db::name('user_menu')->alias('a')
                        ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                        ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                        ->field('b.shop_price,a.num')
                        ->where(['a.user_id' => $user_id, 'a.is_deleted' => 2, 'a.video_id' => $video_id, 'buy_id' => $id])
                        ->select();
                    foreach ($total as $k => $v) {
                        $sum += $v['shop_price'] * $v['num'];
                    }
                }
            }
        } else {
            $where['a.user_id'] = $this->user_id;
            //是否存在订单
            $order = Db::name('order')->where(['type' => 4, 'user_id' => $this->user_id, 'vip_id' => $id])->find();
            if (!$order) {
                $where['a.is_deleted'] = 0;
                $where['a.buy_id'] = $id;
                //选菜列表
                $menu_list = Db::name("user_menu")->alias('a')
                    ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                    ->where($where)
                    ->order('a.add_time desc')
                    ->select();

                $total = Db::name('user_menu')->alias('a')
                    ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('b.shop_price,a.num')
                    ->where(['a.user_id' => $this->user_id, 'a.is_deleted' => 0, 'a.video_id' => $video_id, 'buy_id' => $id])
                    ->select();
                foreach ($total as $k => $v) {
                    $sum += $v['shop_price'] * $v['num'];
                }
            } else {
                $where['a.is_deleted'] = 1;
                $where['a.buy_id'] = $id;
                $menu_list = Db::name("user_menu")->alias('a')
                    ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id')
                    ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                    ->where($where)
                    ->order('a.add_time desc')
                    ->select();

                //dump($menu_list);die;
                //计算总计
                $total = Db::name('user_menu')->alias('a')
                    ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                    ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                    ->field('b.shop_price,a.num')
                    ->where(['a.user_id' => $user_id, 'a.is_deleted' => 1, 'a.video_id' => $video_id, 'buy_id' => $id])
                    ->select();
                foreach ($total as $k => $v) {
                    $sum += $v['shop_price'] * $v['num'];
                }
                //是否是退单菜单
                $return_goods = Db::name('return_goods')->where(['order_id' => $order['order_id']])->find();
                if ($return_goods) {
                    $menu_list = Db::name("user_menu")->alias('a')
                        ->join('yx_menu b', 'a.menu_id = b.id', 'LEFT')
                        ->join('yx_buy c', 'a.buy_id = c.id')
                        ->field('a.user_id,b.title,b.shop_price,b.discount_price,b.image,b.is_on_sale,a.num')
                        ->where(['a.user_id' => $user_id, 'a.is_deleted' => 2, 'a.video_id' => $video_id, 'buy_id' => $id])
                        ->order('a.add_time desc')
                        ->select();

                    //dump($menu_list);die;
                    //计算总计
                    $total = Db::name('user_menu')->alias('a')
                        ->join("yx_menu b", 'a.menu_id = b.id', 'LEFT')
                        ->join('yx_buy c', 'a.buy_id = c.id', 'LEFT')
                        ->field('b.shop_price,a.num')
                        ->where(['a.user_id' => $user_id, 'a.is_deleted' => 2, 'a.video_id' => $video_id, 'buy_id' => $id])
                        ->select();
                    foreach ($total as $k => $v) {
                        $sum += $v['shop_price'] * $v['num'];
                    }
                }
            }
        }



        $result = [
            'menu_list' => $menu_list,
            'total'     => floor($sum * 100) / 100,
            'discount'  => floor($sum * 0.85 * 100) / 100
        ];
        ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $result));
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
        //dump($user_id);die;
        //条件
        $where['user_id'] = $this->user_id;
        //列表
        $menu = Db::name('menu')->where($where)->field('id,title,is_on_sale,cat_id,shop_price,discount_price,image,add_time')->select();
        foreach ($menu as $k => $v) {
            $menu[$k]['add_time'] = date('Y-m-d', $v['add_time']);
        }
        $result = [
            'menu' => $menu
        ];
        ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $result));
    }
}
