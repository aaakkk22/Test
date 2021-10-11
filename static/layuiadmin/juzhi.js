var juzhi = function () {

};

/**
 * [open 打开弹出层]
 * @param  {[type]}  title [弹出层标题]
 * @param  {[type]}  url   [弹出层地址]
 * @param  {[type]}  w     [宽]
 * @param  {[type]}  h     [高]
 * @param  {Boolean} full  [全屏]
 * @return {[type]}        [description]
 */

juzhi.open = function(title, url,buttonname='添加', w, h, full){
    if (title == null || title == '') {
        var title = false;
    }
    ;
    if (url == null || url == '') {
        var url = "404.html";
    }
    ;
    if (w == null || w == '') {
        var w = ($(window).width() * 0.9);
    }
    ;
    if (h == null || h == '') {
        var h = ($(window).height() - 50);
    }
    ;
    var index = layer.open({
        type: 2,
        area: [w + 'px', h + 'px'],
        fix: false, //不固定
        maxmin: true,
        // shadeClose: true,
        // shade: 0.4,
        title: title,
        content: url,
        btn: [buttonname, '取消'],
        yes: function (index, layero) {
            //点击确认触发 iframe 内容中的按钮提交
            var submit = layero.find('iframe').contents().find("#app-form-submit");
            submit.click();
        }
    });
    if (full) {
        layer.full(index);
    }
};

/**
 * [close 关闭弹出层父窗口关闭]
 * @return {[type]} [description]
 */
juzhi.father_reload = function() {
    parent.location.reload();
};

/**
 * [close 关闭弹出层]
 * @return {[type]} [description]
 */
juzhi.close = function() {
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
};