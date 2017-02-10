<?php
function editer(){
    global $_W;
    $_W['host'] = 'http://demo.frontend.com/';

    $html = <<<ETO
<script id="container" name="content" type="text/plain">
        这里写你的初始化内容
    </script>
<!-- 配置文件 -->
<script type="text/javascript" src="{$_W['host']}Public/Common/Editer/ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="{$_W['host']}Public/Common/Editer/ueditor.all.js"></script>
<!-- 实例化编辑器 -->
<script type="text/javascript">
    var ue = UE.getEditor('container',{toolbars: [
    ['fullscreen', 'source', 'undo', 'redo'],
    ['bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', 'simpleupload', ,'insertimage']
]});
    
</script>
ETO;
    echo $html;

}

/**
 *  {php echo upload()}
    <a href="javascript:void(0);" id="1" onclick="upImage(this);">上传图片</a>
    <a href="javascript:void(0);" id="2" onclick="upFiles(this);">上传文件</a>
    <script>
    function imageArg(arr,id) {

    }
    function fileArg(arr,id) {

    }
    </script>
 */
function upload(){
    global $_W;
    $_W['host'] = 'http://demo.frontend.com/';

    $html = <<<ETO
<script src="//cdn.bootcss.com/jquery/3.1.1/jquery.min.js"></script>
<!-- 配置文件 -->
<script type="text/javascript" src="{$_W['host']}Public/Common/Editer/ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="{$_W['host']}Public/Common/Editer/ueditor.all.js"></script>
<script type="text/javascript">
var editor;
var _editor;
var uploadId;
$(function() {
     editor = UE.getEditor('myEditor', {
         initialFrameWidth: 800,
         initialFrameHeight: 300,
     });


    //重新实例化一个编辑器，防止在上面的editor编辑器中显示上传的图片或者文件
    _editor = UE.getEditor('upload_ue');
    _editor.ready(function () {
        //设置编辑器不可用
    
        //隐藏编辑器，因为不会用到这个编辑器实例，所以要隐藏
        _editor.hide();
        //侦听图片上传
        _editor.addListener('beforeInsertImage', function (t, arg) {
             imageArg(arg,uploadId);
        })
        //侦听文件上传，取上传文件列表中第一个上传的文件的路径
        _editor.addListener('afterUpfile', function (t, arg){
         console.log(t)
            fileArg(arg,uploadId);
        })
    });
});    
//弹出图片上传的对话框
function upImage(obj) {
    uploadId = obj.id
    var myImage = _editor.getDialog("insertimage");
    myImage.open();
}
//弹出文件上传的对话框
function upFiles(obj) {
    uploadId = obj.id
    var myFiles = _editor.getDialog("attachment");
    myFiles.open();
}


</script>
<div style="display: none;">
    <script type="text/plain" id="myEditor"></script>
    <script type="text/plain" id="upload_ue"></script> 
</div>
ETO;
    echo $html;
}