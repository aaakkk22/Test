<?php
namespace app\common\model;

class MdSystem extends \think\Model
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'tp_md_system';
    

    /**
     * 通过 admin_id name 得到 value
     */
    public static function get_value($admin_id,$name){
        return self::where(['admin_id'=>$admin_id,'config_name'=>$name])->value('config_text');
    }

    
}