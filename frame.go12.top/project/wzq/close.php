<?php
//查看邀请文件
if(isset($_GET['client_id'])){
    require('DB.class.php');//引入数据库类
    $db=DB::getDB();

    $db->query("delete from user where userid='{$_GET['client_id']}'");
    $db->query("delete from play where fromuid='{$_GET['client_id']}' or touid='{$_GET['client_id']}'");

    DB::unDB($res, $db);
}
?>