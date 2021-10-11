<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use app\common\logic\OssLogic;

class Base extends Controller
{

    public function _initialize()
    {
        header("content-type:text/html;charset=utf-8");  //设置编码

        //判断用户是否登录
        if (!Session::get('userinfo')) {
            $url = url('login/login');
            echo "<script>top.location.href='$url'</script>";
            exit;
        }
        $userinfo = Session::get('userinfo');

        $this->roles = $userinfo['roles'];
    }

    function treeList($data, $keyname = 'id', $pid = 0, $count = 1)
    {
        static $treeList = array();
        foreach ($data as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['level'] = $count;
                $treeList[] = $value;
                unset($data[$key]);
                $this->treeList($data, $value[$keyname], $count + 1);
            }
        }
        return $treeList;
    }

    public function upload()
    {
        $file = request()->file('file');  //获取上传文件信息
        if ($file) {
            $info = $file->move(ROOT_PATH, 'aaa');
            ajaxReturn(['status' => 1, 'msg' => '上传成功', 'filename' => $info->getFilename()]);
        } else {
            ajaxReturn(['status' => -1, 'msg' => '上传失败']);
        }
    }

    //上传照片
    public function layupload_oss()
    {
        $file = request()->file('file');  //获取上传文件信息
        if ($file) {

            $ext = strrchr($_FILES['file']['name'], '.');

            $name = md5(time()) . round(10000, 99999) . $ext;

            $fileName = 'yao/' . date("Ymd") . '/' . $name;

            $ossClient = new OssLogic();
            $url = $ossClient->uploadFile($_FILES['file']['tmp_name'], $fileName);

            return array(
                'code' => 0,
                'data' => array('src' => $url),
            );
        } else {
            return $file->getError();
        }
    }
}
