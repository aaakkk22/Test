
function copy_button(){
    var upurl ='/shop/goods/copy_goods';
    layer.open({
        type: 2,
        title: '输入链接链接地址',
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: ['50%', '60%'],
        content: upurl
     });
}
