<?php

namespace app\common\logic;

use OSS\OssClient;
use OSS\Core\OssException;

require __DIR__ . '/../../../vendor/aliyun-oss-php-sdk/autoload.php';

use think\Model;
use think\Db;

/**
 * Class OssLogic
 * 对象存储逻辑类
 */
class OssLogic
{
    static private $initConfigFlag = false;
    static private $accessKeyId = '';
    static private $accessKeySecret = '';
    static private $endpoint = '';
    static private $bucket = '';
    
    static private $admin_id = 0;
    static private $store_id = 0;

    /** @var \OSS\OssClient */
    static private $ossClient = null;
    static private $errorMsg = '';
    
    static private $waterPos = [
        1 => 'nw',     //标识左上角水印
        2 => 'north',  //标识上居中水印
        3 => 'ne',     //标识右上角水印
        4 => 'west',   //标识左居中水印
        5 => 'center', //标识居中水印
        6 => 'east',   //标识右居中水印
        7 => 'sw',     //标识左下角水印
        8 => 'south',  //标识下居中水印
        9 => 'se',     //标识右下角水印
    ];
    
    public function __construct($admin_id=0,$store_id=0)
    {
        self::$admin_id          = $admin_id;
        self::$store_id         = $store_id;

        self::initConfig();
    }
    
    /**
     * 获取错误信息，一旦其他接口返回false时，可调用此接口查看具体错误信息
     * @return type
     */
    public function getError()
    {
        return self::$errorMsg;
    }
    
    static private function initConfig()
    {
        if (self::$initConfigFlag) {
            return;
        }
        
        // $c = [];
        // $configItems = 'oss_key_id,oss_key_secret,oss_endpoint,oss_bucket';
        // $config = M('config')->field('name,value')->where('name', 'IN', $configItems)->select();
        // foreach ($config as $v) {
        //     $c[$v['name']] = $v['value'];
        // }
        // self::$accessKeyId     = $c['oss_key_id'] ?: '';
        // self::$accessKeySecret = $c['oss_key_secret'] ?: '';
        // self::$endpoint        = $c['oss_endpoint'] ?: '';
        // self::$bucket          = $c['oss_bucket'] ?: '';
        
        self::$accessKeyId     = 'LTAINwrNkyMMZo0J';
        self::$accessKeySecret = 'guo9PiV368mD6sebZhCkCOwm9X5DZR';
        self::$endpoint        = 'oss-cn-shenzhen.aliyuncs.com';
        self::$bucket          = 'pic-c3w-cc';


        self::$initConfigFlag  = true;
    }

    static private function getOssClient()
    {
        if (!self::$ossClient) {
            self::initConfig();
            try {
                self::$ossClient = new OssClient(self::$accessKeyId, self::$accessKeySecret, self::$endpoint, false);
            } catch (OssException $e) {
                self::$errorMsg = "创建oss对象失败，".$e->getMessage();
                return null;
            }
        }
        return self::$ossClient;
    }
    
    public function getSiteUrl()
    {
        // return "https://" .self::$bucket . "." . self::$endpoint;
        return "https://pic.c3w.cc";
    }

    public function uploadFile($filePath, $object = null)
    {  
        $ossClient = self::getOssClient();
        if (!$ossClient) {
            return 'ossClient not found';
        }
        
        if (is_null($object)) {
            $object = $filePath;
        }
    
        try {
            $ossClient->uploadFile(self::$bucket, $object, $filePath);
        } catch (OssException $e) {
            self::$errorMsg = "oss上传文件失败，".$e->getMessage();
            return self::$errorMsg;
        }
        $url = $this->getSiteUrl().'/'.$object;


        try{
            // $filesize_res = get_headers($url,true);   
            // $filesize = (int) $filesize_res['Content-Length'];
            // Db::name('picture_storage')->insert(['admin_id'=>self::$admin_id,'store_id'=>self::$store_id,'url'=>$url,'filesize'=>$filesize,'add_time'=>time()]);
        } catch (OssException $e) {
            //throw $th;
            // write_log($e);
        }
       

        return $url;
    }
    
    /**
     * 获取商品图片的url
     * @param type $originalImg
     * @param type $width
     * @param type $height
     * @param type $defaultImg
     * @return type
     */
    public function getGoodsThumbImageUrl($originalImg, $width, $height, $defaultImg = '')
    {
        if (!$this->isOssUrl($originalImg)) {
            return $defaultImg;
        }
        
        // 图片缩放（等比缩放）
        $url = $originalImg."?x-oss-process=image/resize,m_pad,h_$height,w_$width";
        
        $water = tpCache('water');
        if ($water['is_mark']) {
            if ($width > $water['mark_width'] && $height > $water['mark_height']) {
                if ($water['mark_type'] == 'img') {
                    if ($this->isOssUrl($water['mark_img'])) {
                        $url = $this->withImageWaterUrl($url, $water['mark_img'], $water['mark_degree'], $water['sel']);
                    }
                } else {
                    $url = $this->withTextWaterUrl($url, $water['mark_txt'], $water['mark_txt_size'], $water['mark_txt_color'], $water['mark_degree'], $water['sel']);
                }
            }
        }
        return $url;
    }
    
    /**
     * 获取商品相册的url
     * @param type $originalImg
     * @param type $width
     * @param type $height
     * @param type $defaultImg
     * @return type
     */
    public function getGoodsAlbumThumbUrl($originalImg, $width, $height, $defaultImg = '')
    {
        if (!($originalImg && strpos($originalImg, 'http') === 0 && strpos($originalImg, 'aliyuncs.com'))) {
            return $defaultImg;
        }
        
        // 图片缩放（等比缩放）
        $url = $originalImg."?x-oss-process=image/resize,m_pad,h_$height,w_$width";
        return $url;
    }
    
    /**
     * 链接加上文本水印参数（文字水印(方针黑体，黑色)）
     * @param string $url
     * @param type $text
     * @param type $size
     * @param type $posSel
     * @return string
     */
    private function withTextWaterUrl($url, $text, $size, $color, $transparency, $posSel)
    {
        $color = $color ?: '#000000';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#000000';
        }
        $color = ltrim($color, '#');
        $text_encode = urlsafe_b64encode($text);
        $url .= ",image/watermark,text_{$text_encode},type_ZmFuZ3poZW5naGVpdGk,color_{$color},size_{$size},t_{$transparency},g_" . self::$waterPos[$posSel];
        return $url;
    }
    
    /**
     * 链接加上图片水印参数
     * @param string $url
     * @param type $image
     * @param type $transparency
     * @param type $posSel
     * @return string
     */
    private function withImageWaterUrl($url, $image, $transparency, $posSel)
    {
        $image = ltrim(parse_url($image, PHP_URL_PATH), '/');
        $image_encode = urlsafe_b64encode($image);
        $url .= ",image/watermark,image_{$image_encode},t_{$transparency},g_" . self::$waterPos[$posSel];
        return $url;
    }
    
    /**
     * 是否是oss的链接
     * @param type $url
     * @return boolean
     */
    public function isOssUrl($url)
    {
        if ($url && strpos($url, 'http') === 0 && strpos($url, 'aliyuncs.com')) {
            return true;
        }
        return false;
    }
}