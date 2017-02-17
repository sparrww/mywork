<?php
//加载GatewayClient
session_start();
require_once __DIR__.'/workerman/vendor/GatewayClient/Gateway.php';
// GatewayClient 3.0.0版本开始要使用命名空间
use GatewayClient\Gateway;
// 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
Gateway::$registerAddress = '127.0.0.1:1236';

$client_id = $_POST['client_id'];


// 假设用户已经登录，用户uid和群组id在session中
if(empty($client_id)) return;

if(!Gateway::isOnline($client_id)) return;

// client_id与uid绑定
Gateway::bindUid($client_id, $client_id);

switch ($_POST['project']){
    case 'wzq':
        require(__DIR__.'/project/wzq/DB.class.php');//引入数据库类
        $db=DB::getDB();

        if(!empty($_POST['username'])){
            $db->query("update user set username='{$_POST['username']}' where  userid='{$client_id}'");
            DB::unDB($res, $db);
            return json_encode(['type'=>'success','username'=>$_POST['username']]);
        }else{
            $res=$db->query("SELECT username FROM user WHERE userid='{$client_id}' LIMIT 1");
            $row=$res->fetch_object();
            if(!$row->username){
                $db->query("INSERT INTO user (userid) VALUES ('$client_id')");
            }
            DB::unDB($res, $db);
            return json_encode(['type'=>'success','username'=>'']);
        }
        break;
}


//var_dump(Gateway::getClientIdByUid($uid));
//// 加入某个群组（可调用多次加入多个群组）
//if($group_id){
//    Gateway::joinGroup($client_id, $group_id);
//}
