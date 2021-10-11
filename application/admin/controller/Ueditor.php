<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Loader;
use app\common\logic\OssLogic;


use Common\Util\File;


/**
 * Class UeditorController
 * @package Admin\Controller
 * 
 * 不要继承 base
 */
class Ueditor extends Controller
{
    private $sub_name = array('date', 'Y/m-d');
    private $savePath = 'temp/';

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set("Asia/Shanghai");
        
        $this->savePath = input('savepath','temp').'/';
        
        error_reporting(E_ERROR | E_WARNING);
    }
    
    public function getContent()
    {
        echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
            <script src="' . __ROOT__ . '/Public/plugins/Ueditor/ueditor.parse.js" type="text/javascript"></script>
            <script>' . " uParse('.content',{
                  'highlightJsUrl':'" . __ROOT__ . "/Public/plugins/Ueditor/third-party/SyntaxHighlighter/shCore.js',
                  'highlightCssUrl':" . __ROOT__ . "/Public/plugins/Ueditor/third-party/SyntaxHighlighter/shCoreDefault.css'
              })</script>";
        $content = htmlspecialchars(stripslashes($_REQUEST ['myEditor']));
        echo "<div class='content'>" . htmlspecialchars_decode($content) . "</div>";
    }

    /**
     *上传文件
     */
    // public function fileUp()
    // {
        // $config = array(
        //     "savePath" => 'File/',
        //     "maxSize" =>  20000000, // 单位B
        //     "exts" => explode(",",  'zip,rar,doc,docx,zip,pdf,txt,ppt,pptx,xls,xlsx'),
        //     "subName" => $this->sub_name,
        // );

        // $upload = new Upload($config);
        // $info = $upload->upload();

        // $oss = new OssLogic();
        // $url = $oss->uploadFile($filePath);

        // if ($info) {
        //     $state = "SUCCESS";
        // } else {
        //     $state = "ERROR" . $upload->getError();
        // }

        // $return_data['url'] = $info['upfile']['urlpath'];
        // $return_data['fileType'] = $info['upfile']['ext'];
        // $return_data['original'] = $info['upfile']['name'];
        // $return_data['state'] = $state;

        // ajaxReturn($return_data);
    // }

  
  
    /**
     * @function imageUp
     */
    public function imageUp()
    {
        // 上传图片框中的描述表单名称，
        $title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
        $path = htmlspecialchars($_POST['dir'], ENT_QUOTES);        

        $config = array(
            "savePath" => $this->savePath,
            "maxSize" =>  20000000, // 单位B
            "exts" => explode(",", 'gif,png,jpg,jpeg,bmp'),
            "subName" => $this->sub_name,
        );

        // $upload = new Upload($config);
        // $info = $upload->upload();

        $file = request()->file('file');  //获取上传文件信息
        if ($file) {
            $ext = strrchr($_FILES['file']['name'], '.');
            $name = md5(time()) . round(1000, 9999) . $ext;
            $fileName = 'video_xcx/' . date("Ymd") . '/' . $name;

            //传admin_id、store_id
            $admin_id = session('shop_info.admin_id');
            $store_id = session('shop_info.store_id');
            
            $ossClient = new OssLogic($admin_id,$store_id);
            
            $url = $ossClient->uploadFile($_FILES['file']['tmp_name'], $fileName);
        } else {

            $return_data['state'] = "ERROR";
            ajaxReturn($return_data);
        }


        if ($url) {
            $state = "SUCCESS";         
        } else {
            $state = "ERROR" ;
            // $state = "ERROR" . $upload->getError();
        }

        // if(!isset($info['upfile'])){
        // 	$info['upfile'] = $info['Filedata'];
        // }else{

        // 	//编辑器插入图片水印处理
        // 	if($this->savePath=='Goods/'){
        // 		$image = new \Think\Image();
        // 		$water = tpCache('water');
        // 		$imgresource = ".".$info['upfile']['urlpath'];
        // 		$image->open($imgresource);
        // 		if($water['is_mark']==1 && $image->width()>$water['mark_width'] && $image->height()>$water['mark_height']){
        // 			if($water['mark_type'] == 'text'){
        // 				$image->text($water['mark_txt'],'./hgzb.ttf',20,'#000000',9)->save($imgresource);
        // 			}else{
        // 				$image->water(".".$water['mark_img'],9,$water['mark_degree'])->save($imgresource);
        // 			}
        // 		}
        // 	}
        // }
        
        //$info['upfile']['urlpath'];
        $return_data['url'] =  $url;
        $return_data['title'] = $title;
        $return_data['original'] = $title;
        $return_data['state'] = $state;
        ajaxReturn($return_data);
    }



}