<?php defined('TPL_INCLUDE') OR exit('Access Denied'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>管理平台</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />

    <link rel="stylesheet" type="text/css" href="/Module/<?php echo ucfirst($_GPC['act'])?>/View/css/reset.css">
    <link rel="stylesheet" type="text/css" href="/Module/<?php echo ucfirst($_GPC['act'])?>/View/css/supersized.css">
    <link rel="stylesheet" type="text/css" href="/Module/<?php echo ucfirst($_GPC['act'])?>/View/css/style.css">
    <script type="text/javascript" src="/Module/<?php echo ucfirst($_GPC['act'])?>/View/js/jquery-1.8.2.min.js" ></script>
    <script type="text/javascript" src="/Module/<?php echo ucfirst($_GPC['act'])?>/View/js/supersized.3.2.7.min.js" ></script>
    <script type="text/javascript" src="/Module/<?php echo ucfirst($_GPC['act'])?>/View/js/supersized-init.js" ></script>
    <script type="text/javascript" src="/Module/<?php echo ucfirst($_GPC['act'])?>/View/js/layer/layer.js" ></script>
</head>
<body>
<div class="page-container">
    <h1>后台管理</h1>
    <form action="" method="post" id="loginform">
        <input type="text" name="name" class="username" placeholder="请输入您的用户名！">
        <input type="password" name="passwd" class="password" placeholder="请输入您的用户密码！">
        <input type="Captcha" class="Captcha" name="Captcha" class="Captcha" placeholder="请输入验证码！" style="display:none;">
        <button type="button" class="submit_button">登录</button>
    </form>
</div>
<div style="margin-top:150px;"></div>
<div class="footerwrap">
    <div>©2016-2017</div>
</div>
</body>
<script>
    $(function() {
        $('.submit_button').bind('click',function(){
            login()
        })

        document.onkeydown = function(e){
            var ev = document.all ? window.event : e;
            if(ev.keyCode==13) {
                login()
            }
        }
    })
    function login(){
        if ($('.username').val() == '') {
            layer.msg('用户名不能为空！');
            $('.username').focus();
            return;
        } else if ($('.password').val() == '') {
            layer.msg('密码不能为空！');
            $('.password').focus();
            return;
        }

        $.ajax({
            type: 'post',
            url: "<?php echo create_url('login')?>",
            data: {
                "name": "<?php echo $_GPC['name'];?>",
                "username": $('.username').val(),
                "password": $('.password').val()
            },
            dataType: 'json',
            async: false,
            success: function (data) {
                if (data.type !='success') {
                    layer.msg(data.message);
                } else {
                    layer.msg('登录成功');
                    autoTime = setTimeout(function () {
                        clearTimeout(autoTime);
                        if(window !== window.top){
                            parent.location.reload();return
                        }else{
                            window.location.href = data.url
                        }
                    }, 800);

                }
            }
        })
    }
</script>
</html>