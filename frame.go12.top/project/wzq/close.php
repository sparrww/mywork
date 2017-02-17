<?php
//查看邀请文件
if(isset($_GET['clent_id'])){
    require('DB.class.php');//引入数据库类
    $db=DB::getDB();

    $db->query("delete user where userid='{$_GET['clent_id']}'");
    $db->query("delete play where fromuid='{$_GET['clent_id']}' or touid='{$_GET['clent_id']}'");

    DB::unDB($res, $db);
}
?>