<?php
	//查看邀请文件
	if(isset($_POST)){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
		$uid=$_POST['uid'];//查看人的id
		$res=$db->query("SELECT fromuid,touid,confirm FROM play WHERE fromuid='$uid' OR touid='$uid' LIMIT 1");
		$dataArr=$res->fetch_object();
		if($dataArr->fromuid==$uid){//说明查看邀请的用户和发送邀请的用户是同一个
			//echo '1';
			if($dataArr->confirm==1){
				echo '1';//等待对方接受邀请
			}else if($dataArr->confirm==2){
                $res2=$db->query("SELECT username FROM user WHERE userid='$dataArr->touid' LIMIT 1");
                $dataArr2=$res->fetch_object();

				echo '2|'.$dataArr->touid.'|'.$dataArr->username;//对方已接受邀请
			}
		}elseif($dataArr->touid==$uid){
			echo '3';//说明查看邀请的用户被邀请了
		}else{
			echo '4';//说明没有邀请消息
		}
		DB::unDB($res, $db);
	}
?>