<?php

	if(isset($_POST['a'])){
		require('DB.class.php');//引入数据库类
		$db=DB::getDB();
		$action=$_POST['a'];
		if($action=='put1'){
			//file_put_contents('data/listener.txt','1');
            $res=$db->query("UPDATE play SET listener='1' WHERE fromuid='{$_POST["fromuid"]}'");
			DB::unDB($res, $db);
			//echo 'put1';
		}else if($action=='get'){
			//echo file_get_contents('data/listener.txt');
			$res=$db->query("SELECT listener from play WHERE fromuid='{$_POST["fromuid"]}'");
			$row=$res->fetch_object();
			echo $row->listener;
			DB::unDB($res, $db);
		}else if($action=='put0'){
			//file_put_contents('data/listener.txt','0');
			//echo 'put0';
            $res=$db->query("UPDATE play SET listener='0' WHERE fromuid='{$_POST["fromuid"]}'");
			DB::unDB($res, $db);
		}
		
	}
	
?>