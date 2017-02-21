<?php
require __DIR__.'/../../workerman/vendor/GatewayClient/Gateway.php';
use GatewayClient\Gateway;
	//ajax处理邀请信息
	if(isset($_POST)){
		require('DB.class.php');//引入数据库类

		$fromuid=$_POST['fromuid'];
		$touid=$_POST['touid'];

		$db=DB::getDB();
		//判断邀请是否存在
		$res=$db->query("SELECT id FROM play WHERE (fromuid='$fromuid') OR (fromuid='$touid') OR (touid='$fromuid') OR (touid='$touid')");
		if($res->num_rows >0){
			echo '1';//1表示已存在邀请信息
			exit;
		}
		
		$db->query("INSERT INTO play (fromuid,touid) VALUES ('$fromuid','$touid')");
		if($db->affected_rows != 1){
			echo '2';//邀请失败
		}else{
            Gateway::$registerAddress = '127.0.0.1:1236';
            Gateway::sendToUid($touid, json_encode(['type'=>'invite']));
			echo '3';//邀请成功，等待确认

		}
		DB::unDB($res, $db);
	}

?>