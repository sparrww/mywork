<?php
//加载GatewayClient
require_once __DIR__.'/workerman/vendor/workerman/gateway-worker/src/Gateway.php';
// GatewayClient 3.0.0版本开始要使用命名空间
use GatewayClient\Gateway;
// 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
Gateway::$registerAddress = '127.0.0.1:1236';

// 假设用户已经登录，用户uid和群组id在session中
$uid      = $_SESSION['uid'];
$group_id = $_SESSION['group'];
file_put_contents(__DIR__.'/mylog.txt', $client_id."\r\n======================================\r\n", FILE_APPEND);
// client_id与uid绑定
Gateway::bindUid($client_id, $uid);
// 加入某个群组（可调用多次加入多个群组）
Gateway::joinGroup($client_id, $group_id);