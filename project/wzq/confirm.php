<?php
	//ajax修改确认信息
	if(isset($_POST)){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
		if($_POST['a']=='put'){

			if($_POST['d']=='1'){
				//不接受邀请则删除
				$db->query("DELETE FROM play WHERE touid='{$_POST["uid"]}' LIMIT 1");
				echo '1';
			}else if($_POST['d']=='2'){
				$db->query("UPDATE play SET confirm=2 WHERE touid='{$_POST["uid"]}'");
				$res=$db->query("SELECT fromuid FROM play WHERE touid='{$_POST["uid"]}' LIMIT 1");
				$row=$res->fetch_object();

                $res2=$db->query("SELECT username FROM user WHERE userid='$row->fromuid' LIMIT 1");
                $dataArr2=$res2->fetch_object();

                echo $row->fromuid.'|'.$dataArr2->username;//对方已接受邀请
			}
		}elseif($_POST['a']=='get'){
			
			//echo file_get_contents('data/confirm.txt');
		}elseif($_POST['a']=='read'){
			/*
			$dataStr=file_get_contents('data/invite.txt');
			$dataArr=json_decode($dataStr);
			echo $dataArr->touid;
			*/
		}
		DB::unDB($res, $db);
	}
	
?>