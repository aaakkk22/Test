<?php

namespace app\common\model;

use think\Db;

class Goods extends \think\Model
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'tp_goods';


    // goodsCatelist($cat_id)
    /*
     * 商品一级分类显示
     * @author 精
     * @time   2019.9.17
     */
    public static function goodsCatelist($store_id, $cat_id)
    {
        $goodscate = Db::name('goods')->where(["cat_id" => $cat_id, 'store_id' => $store_id])->field('goods_id,cat_id,shop_price,sales_sum,goods_name,original_img')->order('goods_id desc')->select();

        foreach ($goodscate as $key => $value) {
            $goodscate[$key]['original_img'] = check_image($value['original_img']);
        }

        return  array('status' => 1, 'msg' => '获取成功', 'result' => $goodscate);
    }
}
