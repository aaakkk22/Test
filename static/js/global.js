/*
 * 上传图片 后台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify(num,elementid,path,callback,fileType="Images")
{	   	
	var upurl ='/Admin/Uploadify/upload?num='+num+'&input='+elementid+'&path='+path+'&func='+callback+'&fileType='+fileType;
    var title = '上传图片';
    if(fileType == 'Flash'){
        title = '上传视频';
    }
    layer.open({
        type: 2,
        title: title,
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: ['60%', '80%'],
        content: upurl
     });
}






