<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Loader;
use app\common\logic\OssLogic;

class uploadify extends Controller
{
    public function upload()
    {
        $func = input('func');
        $path = input('path', 'temp');
        
        $image_upload_limit_size = 100;
        $fileType = input('fileType', 'Images');  //上传文件类型，视频，图片

        if ($fileType == "undefined") {
            $fileType = "Images";
        }

        if ($fileType == 'Flash') {
            $upload = url('Admin/Ueditor/videoUp', array('savepath' => $path, 'pictitle' => 'banner', 'dir' => 'video'));
            $type = 'mp4,3gp,flv,avi,wmv';
        } else {
            $upload = url('Admin/Ueditor/imageUp', array('savepath' => $path, 'pictitle' => 'banner', 'dir' => 'images'));
            $type = 'jpg,png,gif,jpeg';
        }
        $info = array(
            'num' => input('num/d'),
            'fileType' => $fileType,
            'title' => '',
            'upload' => $upload,
            'fileList' => url('Shop/Uploadify/fileList', array('path' => $path)),
            'size' => $image_upload_limit_size / (1024 * 1024) . 'M',
            'type' => $type,
            'input' => input('input'),
            'func' => empty($func) ? 'undefined' : $func,
        );

        $this->assign('info', $info);
        return $this->fetch();
    }


    /**
     * 每个商户，每个商店 图片列表
     */
    public function filelist()
    {

        $admin_id = input('admin_id');
        

        $icon = input('icon');
        if ($icon == 1) {
           // $list = [
                // ['name' => '地址.png', 'url' => 'https://pic.c3w.cc/icon/address.png'],
                // ['name' => '主播.png', 'url' => 'https://pic.c3w.cc/icon/face.png'],
                // ['name' => 'customer.png', 'url' => 'https://pic.c3w.cc/icon/customer.png'],
                // ['name' => 'extension.png', 'url' => 'https://pic.c3w.cc/icon/extension.png'],
                // ['name' => 'memberCenter.png', 'url' => 'https://pic.c3w.cc/icon/memberCenter.png'],
                // ['name' => 'qh.png', 'url' => 'https://pic.c3w.cc/icon/qh.png'],
                // ['name' => 'record1.png', 'url' => 'https://pic.c3w.cc/icon/record1.png'],
                // ['name' => 'record2.png', 'url' => 'https://pic.c3w.cc/icon/record2.png'],
                // ['name' => 'record3.png', 'url' => 'https://pic.c3w.cc/icon/record3.png'],
                // ['name' => 'record4.png', 'url' => 'https://pic.c3w.cc/icon/record4.png'],
                // ['name' => 'address.png', 'url' => 'https://pic.c3w.cc/icon/address.png'],
                // ['name' => 'balance.png', 'url' => 'https://pic.c3w.cc/icon/balance.png'],
                // ['name' => 'bargain.png', 'url' => 'https://pic.c3w.cc/icon/bargain.png'],
                // ['name' => 'collection.png', 'url' => 'https://pic.c3w.cc/icon/collection.png'],
                // ['name' => 'coupon.png', 'url' => 'https://pic.c3w.cc/icon/coupon.png'],
            //];
            $list = Db::name('icon')->order('sort desc')->select();
        } else {
           
            $store_id = session('shop_info.store_id');
            $list = Db::name('picture_storage')->where(['store_id'=>$store_id])->field('id as name,url')->order('id desc')->limit(100)->select();
            foreach($list as $k => $v){
                $list[$k]['name'] = $v['name'].'.jpg';
            }

        }

        ajaxReturn(['list' => $list]);
    }


    
}
