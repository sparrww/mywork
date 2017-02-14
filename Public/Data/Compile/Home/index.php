<?php defined('TPL_INCLUDE') OR exit('Access Denied'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>

<body>
<!-- 加载编辑器的容器 -->
<?php echo upload()?>

<a href="javascript:void(0);" id="1" onclick="upImage(this);">上传图片</a>
<a href="javascript:void(0);" id="2" onclick="upFiles(this);">上传文件</a>
<script>
    function imageArg(arr,id) {

    }
    function fileArg(arr,id) {

    }
</script>
</body>
</html>